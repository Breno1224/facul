<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'docente' || !isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}
include 'db.php';
$nome_professor_logado = $_SESSION['usuario_nome'];
$professor_id_logado = $_SESSION['usuario_id'];

// Define o identificador da página atual para a sidebar
$currentPageIdentifier = 'ver_comunicados_prof'; 

// Subquery para pegar as turmas do professor logado
$turmas_do_professor_ids = [];
$sql_minhas_turmas = "SELECT DISTINCT turma_id FROM professores_turmas_disciplinas WHERE professor_id = ?";
$stmt_minhas_turmas = mysqli_prepare($conn, $sql_minhas_turmas);
$turmas_ids_string = '0'; // Default to '0' to prevent SQL error if no classes

if ($stmt_minhas_turmas) {
    mysqli_stmt_bind_param($stmt_minhas_turmas, "i", $professor_id_logado);
    mysqli_stmt_execute($stmt_minhas_turmas);
    $result_minhas_turmas = mysqli_stmt_get_result($stmt_minhas_turmas);
    while ($row_turma = mysqli_fetch_assoc($result_minhas_turmas)) {
        $turmas_do_professor_ids[] = $row_turma['turma_id'];
    }
    mysqli_stmt_close($stmt_minhas_turmas);
    if (!empty($turmas_do_professor_ids)) {
        $turmas_ids_string = implode(',', array_map('intval', $turmas_do_professor_ids));
    }
}


$sql_comunicados_prof = "
    SELECT 
        c.titulo, c.conteudo, c.data_publicacao, 
        p_remetente.nome as nome_professor_remetente, 
        coord.nome as nome_coordenador_remetente,
        t.nome_turma,
        c.publico_alvo, c.professor_id AS comunicado_professor_id, c.coordenador_id AS comunicado_coordenador_id
    FROM comunicados c
    LEFT JOIN professores p_remetente ON c.professor_id = p_remetente.id
    LEFT JOIN coordenadores coord ON c.coordenador_id = coord.id
    LEFT JOIN turmas t ON c.turma_id = t.id 
    WHERE 
        (c.coordenador_id IS NOT NULL AND c.publico_alvo = 'TODOS_PROFESSORES') OR 
        (c.professor_id = ?) OR -- Comunicados enviados por este professor
        (c.publico_alvo = 'TURMA_ESPECIFICA' AND c.turma_id IN (" . $turmas_ids_string . ") AND c.professor_id != ?) -- Comunicados para as turmas do professor, não enviados por ele mesmo
        -- A última condição acima evita mostrar novamente os comunicados do próprio professor que já foram pegos pela condição c.professor_id = ?
        -- Se quiser ver TODOS os comunicados das suas turmas, incluindo os seus, simplifique para:
        -- (c.publico_alvo = 'TURMA_ESPECIFICA' AND c.turma_id IN (" . $turmas_ids_string . "))
    ORDER BY c.data_publicacao DESC";

$stmt_prof_com = mysqli_prepare($conn, $sql_comunicados_prof);
// Bind o ID do professor logado para a segunda e terceira condição
mysqli_stmt_bind_param($stmt_prof_com, "ii", $professor_id_logado, $professor_id_logado);
mysqli_stmt_execute($stmt_prof_com);
$result_comunicados_prof = mysqli_stmt_get_result($stmt_prof_com);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visualizar Comunicados - Professor ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style> 
        .main-content h2.page-title { text-align: center; font-size: 1.8rem; color: #2C1B17; margin-bottom: 2rem; border-bottom: 2px solid #D69D2A; padding-bottom: 0.5rem; display: inline-block;}
        .comunicado-item { background-color: #fff; border: 1px solid #e0e0e0; border-left: 5px solid #208A87; padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 0 5px 5px 0; box-shadow: 0 2px 5px rgba(0,0,0,0.07); }
        .comunicado-item.coord { border-left-color: #5D3A9A; background-color: #f4f0f8; }
        .comunicado-item.coord h3 { color: #4a2d7d; }
        .comunicado-item h3 { font-size: 1.3rem; color: #186D6A; margin-top: 0; margin-bottom: 0.5rem; }
        .comunicado-meta { font-size: 0.85rem; color: #777; margin-bottom: 1rem; }
        .comunicado-meta .author, .comunicado-meta .author-coord { font-weight: bold; }
        .comunicado-meta .author-coord { color: #5D3A9A; }
        .comunicado-conteudo { font-size: 1rem; line-height: 1.6; color: #333; white-space: pre-wrap; }
        .no-comunicados { text-align: center; padding: 20px; color: #777; font-size: 1.1rem; }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Quadro de Avisos</h1>
        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit" id="logoutBtnHeader"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>
    <div class="container">
        <nav class="sidebar" id="sidebar">
            <?php
            // Incluindo a sidebar padronizada do professor
            $sidebar_path = __DIR__ . '/includes/sidebar_professor.php';
            if (file_exists($sidebar_path)) {
                include $sidebar_path;
            } else {
                echo "<p style='padding:1rem; color:white;'>Erro: Arquivo da sidebar não encontrado.</p>";
            }
            ?>
        </nav>
        <main class="main-content">
            <div style="text-align: center;">
                <h2 class="page-title">Comunicados Importantes</h2>
            </div>
            <?php if(mysqli_num_rows($result_comunicados_prof) > 0): ?>
                <?php while($com = mysqli_fetch_assoc($result_comunicados_prof)): ?>
                    <?php
                    $remetente_display = ""; 
                    $classe_css_remetente = ""; 
                    
                    if (!empty($com['comunicado_coordenador_id'])) {
                        $remetente_display = "Coordenação (" . htmlspecialchars($com['nome_coordenador_remetente']) . ")";
                        $classe_css_remetente = "coord";
                    } elseif (!empty($com['comunicado_professor_id'])) {
                        if ($com['comunicado_professor_id'] == $professor_id_logado) {
                             $remetente_display = "Você";
                        } else {
                             $remetente_display = "Prof. " . htmlspecialchars($com['nome_professor_remetente']);
                        }
                    } else {
                        $remetente_display = "Sistema"; // Fallback
                    }

                    $publico_display = "";
                     if ($com['publico_alvo'] === 'TODOS_PROFESSORES') {
                        $publico_display = "Todos os Professores";
                    } elseif ($com['publico_alvo'] === 'TURMA_ESPECIFICA' && !empty($com['nome_turma'])) {
                        $publico_display = htmlspecialchars($com['nome_turma']);
                    } elseif ($com['publico_alvo'] === 'PROFESSOR_GERAL_ALUNOS') {
                         $publico_display = "Alunos (Geral do Remetente)";
                    } elseif ($com['publico_alvo'] === 'TODOS_ALUNOS') {
                        $publico_display = "Alunos (Geral da Escola)";
                    }
                    ?>
                    <article class="comunicado-item <?php echo $classe_css_remetente; ?>">
                        <h3><?php echo htmlspecialchars($com['titulo']); ?></h3>
                        <p class="comunicado-meta">
                            Publicado por: <span class="author <?php if($classe_css_remetente === 'coord') echo 'author-coord'; ?>"><?php echo $remetente_display; ?></span> |
                            Em: <?php echo date("d/m/Y H:i", strtotime($com['data_publicacao'])); ?>
                            <?php if(!empty($publico_display)): ?> | Para: <span class="target-turma"><?php echo $publico_display; ?></span><?php endif; ?>
                        </p>
                        <div class="comunicado-conteudo"><?php echo nl2br(htmlspecialchars($com['conteudo'])); ?></div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-comunicados">Nenhum comunicado para visualizar no momento.</p>
            <?php endif; ?>
            <?php 
            if(isset($stmt_prof_com)) mysqli_stmt_close($stmt_prof_com); 
            if($conn) mysqli_close($conn); 
            ?>
        </main>
    </div>
    <script>
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const container = document.querySelector('.container');

        if (menuToggle && sidebar && container) {
            menuToggle.addEventListener('click', function () {
                sidebar.classList.toggle('hidden');
                container.classList.toggle('full-width');
            });
        }
    </script>
</body>
</html>