<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'aluno' || !isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}
include 'db.php';
$nome_aluno = $_SESSION['usuario_nome'];
$aluno_id = $_SESSION['usuario_id'];
$turma_id_aluno = isset($_SESSION['turma_id']) ? intval($_SESSION['turma_id']) : 0;
$currentPageIdentifier = 'comunicados_aluno'; // Para a sidebar

// Query para buscar comunicados relevantes para o aluno
$sql_comunicados = "
    SELECT 
        c.titulo, 
        c.conteudo, 
        c.data_publicacao, 
        c.publico_alvo,
        c.turma_id AS comunicado_turma_id, -- Renomeado para evitar conflito se t.turma_id for usado
        p.nome as nome_professor, 
        coord.nome as nome_coordenador,
        t.nome_turma 
    FROM comunicados c
    LEFT JOIN professores p ON c.professor_id = p.id
    LEFT JOIN coordenadores coord ON c.coordenador_id = coord.id
    LEFT JOIN turmas t ON c.turma_id = t.id 
    WHERE 
        -- 1. Comunicados da Coordenação para TODOS os alunos
        (c.coordenador_id IS NOT NULL AND c.publico_alvo = 'TODOS_ALUNOS') 
        OR
        -- 2. Comunicados da Coordenação para a TURMA ESPECÍFICA do aluno
        (c.coordenador_id IS NOT NULL AND c.publico_alvo = 'TURMA_ESPECIFICA' AND c.turma_id = ?)
        OR
        -- 3. Comunicados de Professores para a TURMA ESPECÍFICA do aluno
        (c.professor_id IS NOT NULL AND c.publico_alvo = 'TURMA_ESPECIFICA' AND c.turma_id = ?)
        -- Para PROFESSOR_GERAL_ALUNOS (do professor para alunos de suas turmas):
        -- Seria necessário um JOIN com professores_turmas_disciplinas para verificar se o professor do comunicado
        -- leciona para a turma do aluno. Ex:
        -- OR (
        --    c.professor_id IS NOT NULL AND c.publico_alvo = 'PROFESSOR_GERAL_ALUNOS' AND
        --    EXISTS (SELECT 1 FROM professores_turmas_disciplinas ptd WHERE ptd.professor_id = c.professor_id AND ptd.turma_id = ?)
        -- )
        -- Por enquanto, a query acima é mais simples e cobre os casos mais diretos.
    ORDER BY c.data_publicacao DESC";

$stmt = mysqli_prepare($conn, $sql_comunicados);
// Bind $turma_id_aluno para cada placeholder '?' na query
// Se você adicionar a lógica para PROFESSOR_GERAL_ALUNOS, precisará de mais um binding.
mysqli_stmt_bind_param($stmt, "ii", $turma_id_aluno, $turma_id_aluno); 
mysqli_stmt_execute($stmt);
$result_comunicados = mysqli_stmt_get_result($stmt);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Comunicados - ACADMIX</title>
    <link rel="stylesheet" href="css/aluno.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content h2.page-title { text-align: center; font-size: 1.8rem; color: #2C1B17; margin-bottom: 2rem; border-bottom: 2px solid #D69D2A; padding-bottom: 0.5rem; display: inline-block;}
        .comunicado-item {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-left: 5px solid #208A87; /* Cor padrão para professor */
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: 0 5px 5px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.07);
        }
        /* Estilo DIFERENCIADO para comunicados da coordenação */
        .comunicado-item.coord-comunicado {
            border-left-color: #5D3A9A; /* Roxo para coordenação */
            background-color: #f7f4fA; /* Um tom de fundo levemente roxo/lilás */
        }
        .comunicado-item.coord-comunicado h3 {
            color: #4a2d7d; /* Tom mais escuro de roxo para o título */
        }
        .comunicado-item.coord-comunicado .comunicado-meta .author { /* Para o nome da coordenação */
            color: #5D3A9A; 
        }

        .comunicado-item h3 {
            font-size: 1.3rem;
            color: #186D6A; /* Cor padrão para título de professor */
            margin-top: 0;
            margin-bottom: 0.5rem;
        }
        .comunicado-meta {
            font-size: 0.85rem;
            color: #777;
            margin-bottom: 1rem;
        }
        .comunicado-meta .author, .comunicado-meta .target-turma {
            font-weight: bold;
        }
        .comunicado-conteudo {
            font-size: 1rem;
            line-height: 1.6;
            color: #333;
            white-space: pre-wrap; /* Mantém quebras de linha e espaços do textarea */
        }
        .no-comunicados {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Comunicados</h1>
        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit" id="logoutBtnHeader"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <?php
            $sidebar_path = __DIR__ . '/includes/sidebar_aluno.php'; // Usando o include da sidebar do aluno
            if (file_exists($sidebar_path)) {
                include $sidebar_path;
            } else {
                // Fallback para a sidebar antiga se o include não existir
                echo '<ul>
                        <li><a href="aluno.php"><i class="fas fa-home"></i> Início & Notícias</a></li>
                        <li><a href="boletim.php"><i class="fas fa-book"></i> Boletim</a></li>
                        <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                        <li><a href="materiais.php"><i class="fas fa-book-open"></i> Materiais Didáticos</a></li>
                        <li><a href="comunicados_aluno.php" class="active"><i class="fas fa-bell"></i> Comunicados</a></li>
                        <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
                      </ul>';
            }
            ?>
        </nav>

        <main class="main-content">
            <div style="text-align: center;">
                <h2 class="page-title">Quadro de Avisos</h2>
            </div>

            <?php if(mysqli_num_rows($result_comunicados) > 0): ?>
                <?php while($comunicado = mysqli_fetch_assoc($result_comunicados)): ?>
                    <?php
                    $remetente_display = "";
                    $classe_extra_css = ""; // Classe CSS para diferenciar
                    $publico_display = "";

                    if (!empty($comunicado['coordenador_id'])) {
                        $remetente_display = "Coordenação (" . htmlspecialchars($comunicado['nome_coordenador']) . ")";
                        $classe_extra_css = "coord-comunicado"; // Aplica estilo de coordenação
                    } elseif (!empty($comunicado['professor_id'])) {
                        $remetente_display = "Prof. " . htmlspecialchars($comunicado['nome_professor']);
                    }

                    if ($comunicado['publico_alvo'] === 'TODOS_ALUNOS') {
                        $publico_display = "Alunos (Geral)";
                    } elseif ($comunicado['publico_alvo'] === 'TURMA_ESPECIFICA' && !empty($comunicado['nome_turma'])) {
                        $publico_display = htmlspecialchars($comunicado['nome_turma']);
                    } elseif ($comunicado['publico_alvo'] === 'PROFESSOR_GERAL_ALUNOS') {
                        $publico_display = "Alunos do Professor (Geral)";
                    }
                    ?>
                    <article class="comunicado-item <?php echo $classe_extra_css; ?>">
                        <h3><?php echo htmlspecialchars($comunicado['titulo']); ?></h3>
                        <p class="comunicado-meta">
                            Publicado por: <span class="author"><?php echo $remetente_display; ?></span> | 
                            Em: <?php echo date("d/m/Y H:i", strtotime($comunicado['data_publicacao'])); ?>
                            <?php if(!empty($publico_display)): ?>
                                | Para: <span class="target-turma"><?php echo $publico_display; ?></span>
                            <?php endif; ?>
                        </p>
                        <div class="comunicado-conteudo">
                            <?php echo nl2br(htmlspecialchars($comunicado['conteudo'])); ?>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-comunicados">Nenhum comunicado disponível para você no momento.</p>
            <?php endif; ?>
            <?php mysqli_stmt_close($stmt); mysqli_close($conn); ?>
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