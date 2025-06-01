<?php
session_start();
// Verifica se o usuário é um aluno logado
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'aluno') {
    header("Location: index.html");
    exit();
}
include 'db.php'; 

$nome_aluno = $_SESSION['usuario_nome'];
$aluno_id = $_SESSION['usuario_id'];
$turma_id_aluno = isset($_SESSION['turma_id']) ? intval($_SESSION['turma_id']) : 0;

$materiais_por_disciplina = [];

// SQL para buscar materiais:
// - Materiais específicos para a turma do aluno (m.turma_id = ?)
// - Materiais globais para uma disciplina (m.turma_id IS NULL)
$sql_materiais = "
    SELECT 
        m.titulo, 
        m.descricao, 
        m.arquivo_path_ou_link, 
        m.tipo_material, 
        d.nome_disciplina,
        CASE 
            WHEN m.arquivo_path_ou_link LIKE 'http%' OR m.arquivo_path_ou_link LIKE 'https%' THEN 0 -- É um link
            ELSE 1 -- É um arquivo para download
        END as is_download,
        CASE
            WHEN LOWER(m.tipo_material) LIKE '%pdf%' THEN 'fas fa-file-pdf'
            WHEN LOWER(m.tipo_material) LIKE '%vídeo%' OR LOWER(m.tipo_material) LIKE '%video%' THEN 'fas fa-video'
            WHEN LOWER(m.tipo_material) LIKE '%apresentação%' OR LOWER(m.tipo_material) LIKE '%powerpoint%' OR LOWER(m.tipo_material) LIKE '%slide%' THEN 'fas fa-file-powerpoint'
            WHEN LOWER(m.tipo_material) LIKE '%documento%' OR LOWER(m.tipo_material) LIKE '%word%' THEN 'fas fa-file-word'
            WHEN LOWER(m.tipo_material) LIKE '%planilha%' OR LOWER(m.tipo_material) LIKE '%excel%' THEN 'fas fa-file-excel'
            WHEN LOWER(m.tipo_material) LIKE '%link%' OR LOWER(m.tipo_material) LIKE '%artigo online%' THEN 'fas fa-link'
            ELSE 'fas fa-file'
        END as icon_class
    FROM materiais_didaticos m
    JOIN disciplinas d ON m.disciplina_id = d.id
    WHERE (m.turma_id = ? OR m.turma_id IS NULL) -- Materiais da turma do aluno ou globais para a disciplina
    ORDER BY d.nome_disciplina, m.data_upload DESC";

$stmt = mysqli_prepare($conn, $sql_materiais);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $turma_id_aluno);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $materiais_por_disciplina[$row['nome_disciplina']][] = $row;
    }
    mysqli_stmt_close($stmt);
} else {
    // Lidar com erro na preparação da query, se necessário
    error_log("Erro ao preparar a query de materiais: " . mysqli_error($conn));
}
mysqli_close($conn);

// O restante do arquivo HTML continua igual, mas o loop usará $materiais_por_disciplina
// Substitua $materiais_por_disciplina_static por $materiais_por_disciplina no loop PHP
// e ajuste os campos conforme a query (ex: $material['icon_class'], $material['is_download'])
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Materiais Didáticos - ACADMIX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aluno.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* (Seus estilos existentes da página materiais.php) */
        .main-content h2 { margin-bottom: 1.5rem; text-align: center; color: #2C1B17; }
        .disciplina-materiais { margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 1px solid #eee; }
        .disciplina-materiais:last-child { border-bottom: none; }
        .disciplina-materiais h3 { font-size: 1.4rem; color: #186D6A; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #D69D2A; display: inline-block; }
        .material-item { background-color: #fff; border: 1px solid #e0e0e0; padding: 15px; margin-bottom: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        .material-item h4 { font-size: 1.2rem; color: #2C1B17; margin-bottom: 0.5rem; }
        .material-item h4 i { margin-right: 8px; color: #208A87; } /* Cor do ícone */
        .material-item p { font-size: 0.95rem; color: #555; margin-bottom: 1rem; line-height: 1.6; }
        .btn-material { display: inline-block; padding: 8px 15px; background-color: #208A87; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background-color 0.3s; }
        .btn-material:hover { background-color: #186D6A; }
        .btn-material i { margin-right: 5px; } /* Ícone dentro do botão (se houver) */
        .no-materials { text-align: center; padding: 20px; color: #777; font-size: 1.1rem; }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Materiais Didáticos</h1>
        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit" id="logoutBtnHeader"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
             <ul>
                <li><a href="aluno.php"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="boletim.php"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                <li><a href="materiais.php" class="active"><i class="fas fa-book-open"></i> Materiais Didáticos</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li> 
                <li><a href="comunicados_aluno.php"><i class="fas fa-bell"></i> Comunicados</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Materiais Didáticos Disponíveis</h2>

            <?php if (empty($materiais_por_disciplina)): ?>
                <p class="no-materials">Nenhum material didático disponível no momento.</p>
            <?php else: ?>
                <?php foreach ($materiais_por_disciplina as $disciplina => $materiais): ?>
                    <section class="disciplina-materiais">
                        <h3><?php echo htmlspecialchars($disciplina); ?></h3>
                        <?php if (empty($materiais)): ?>
                            <p>Nenhum material para esta disciplina.</p>
                        <?php else: ?>
                            <?php foreach ($materiais as $material): ?>
                                <div class="material-item">
                                    <h4><i class="<?php echo htmlspecialchars($material['icon_class']); ?>"></i> <?php echo htmlspecialchars($material['titulo']); ?></h4>
                                    <p><?php echo nl2br(htmlspecialchars($material['descricao'])); // nl2br para quebras de linha na descrição ?></p>
                                    <a href="<?php echo htmlspecialchars($material['arquivo_path_ou_link']); ?>" 
                                       class="btn-material" 
                                       target="_blank" <?php if ($material['is_download']): echo ' download '; endif; ?>>
                                        <?php echo htmlspecialchars($material['is_download'] ? 'Baixar' : 'Acessar'); ?> <?php echo htmlspecialchars($material['tipo_material']); ?>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    <script>
        // (Seu script do menu lateral existente)
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