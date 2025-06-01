<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'docente') {
    header("Location: index.html");
    exit();
}
include 'db.php';
$nome_professor = $_SESSION['usuario_nome'];
$professor_id = $_SESSION['usuario_id'];
$currentPageIdentifier = 'disciplinas'; // Para a sidebar

// Buscar as associações de turmas e disciplinas para este professor
$sql_associacoes = "
    SELECT 
        t.id as turma_id, 
        t.nome_turma, 
        d.id as disciplina_id, 
        d.nome_disciplina
        -- , d.ementa -- Descomente se você adicionou a coluna ementa
    FROM turmas t
    JOIN professores_turmas_disciplinas ptd ON t.id = ptd.turma_id
    JOIN disciplinas d ON d.id = ptd.disciplina_id
    WHERE ptd.professor_id = ?
    ORDER BY t.nome_turma, d.nome_disciplina";

$stmt_assoc = mysqli_prepare($conn, $sql_associacoes);
$disciplinas_por_turma = [];

if ($stmt_assoc) {
    mysqli_stmt_bind_param($stmt_assoc, "i", $professor_id);
    mysqli_stmt_execute($stmt_assoc);
    $result_assoc = mysqli_stmt_get_result($stmt_assoc);
    while ($row = mysqli_fetch_assoc($result_assoc)) {
        $disciplinas_por_turma[$row['nome_turma']][] = $row;
    }
    mysqli_stmt_close($stmt_assoc);
} else {
    error_log("Erro ao buscar disciplinas e turmas do professor: " . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Minhas Disciplinas - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .main-content h2.page-title {
            text-align: center; font-size: 1.8rem; color: #2C1B17; margin-bottom: 2rem;
            border-bottom: 2px solid #D69D2A; padding-bottom: 0.5rem; display: inline-block;
        }
        .turma-section {
            background-color: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            margin-bottom: 2rem;
        }
        .turma-section h3 { /* Título da Turma */
            font-size: 1.6rem;
            color: #186D6A; /* Ciano escuro da paleta */
            margin-top: 0;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }
        .disciplina-card {
            background-color: #f9f9f9;
            border: 1px solid #e0e0e0;
            border-left: 5px solid #208A87; /* Ciano da paleta */
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 5px 5px 0;
        }
        .disciplina-card h4 { /* Nome da Disciplina */
            font-size: 1.25rem;
            color: #2C1B17;
            margin-top: 0;
            margin-bottom: 0.75rem;
        }
        .disciplina-ementa {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 1rem;
            padding-left: 1rem;
            border-left: 2px solid #D69D2A; /* Mostarda da paleta */
        }
        .disciplina-ementa p { margin: 0.5rem 0; }
        .disciplina-actions a {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 5px; /* Para mobile */
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            text-decoration: none;
            color: white;
            border-radius: 4px;
            transition: opacity 0.2s;
        }
        .disciplina-actions a:hover { opacity: 0.85; }
        .action-notas { background-color: #28a745; } /* Verde */
        .action-frequencia { background-color: #ffc107; color: #333 !important; } /* Amarelo */
        .action-materiais { background-color: #17a2b8; } /* Azul Info */
        .action-relatorios { background-color: #6f42c1; } /* Roxo */
        .no-data-message {
            padding: 1rem; text-align: center; color: #777; background-color: #f8f9fa; border-radius: 4px;
        }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Minhas Disciplinas (Prof. <?php echo htmlspecialchars($nome_professor); ?>)</h1>
        <form action="logout.php" method="post" style="display: inline;"><button type="submit"><i class="fas fa-sign-out-alt"></i> Sair</button></form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <?php include __DIR__ . '/includes/sidebar_professor.php'; ?>
        </nav>

        <main class="main-content">
            <div style="text-align: center;">
                <h2 class="page-title">Minhas Disciplinas e Turmas</h2>
            </div>

            <?php if (empty($disciplinas_por_turma)): ?>
                <p class="no-data-message">Você não está associado a nenhuma disciplina ou turma no momento.</p>
            <?php else: ?>
                <?php foreach ($disciplinas_por_turma as $nome_turma_key => $disciplinas_da_turma_array): ?>
                    <section class="turma-section">
                        <h3><i class="fas fa-users-class"></i> Turma: <?php echo htmlspecialchars($nome_turma_key); ?></h3>
                        <?php foreach ($disciplinas_da_turma_array as $disciplina_info): ?>
                            <div class="disciplina-card">
                                <h4><i class="fas fa-book-reader"></i> <?php echo htmlspecialchars($disciplina_info['nome_disciplina']); ?></h4>
                                
                                <?php if (!empty($disciplina_info['ementa'])): // Descomente se adicionou a coluna ementa ?>
                                <?php else: ?>
                                <?php endif; ?>

                                <div class="disciplina-actions">
                                    <a href="lancar-notas.php?turma_id=<?php echo $disciplina_info['turma_id']; ?>&disciplina_id=<?php echo $disciplina_info['disciplina_id']; ?>" class="action-notas" title="Lançar Notas para esta Turma/Disciplina">
                                        <i class="fas fa-edit"></i> Lançar Notas
                                    </a>
                                    <a href="frequencia_professor.php?turma_id=<?php echo $disciplina_info['turma_id']; ?>" class="action-frequencia" title="Registrar Frequência para esta Turma">
                                        <i class="fas fa-user-check"></i> Frequência
                                    </a>
                                    <a href="gerenciar_materiais.php?turma_id=<?php echo $disciplina_info['turma_id']; ?>&disciplina_id=<?php echo $disciplina_info['disciplina_id']; ?>" class="action-materiais" title="Enviar Materiais para esta Turma/Disciplina">
                                        <i class="fas fa-folder-open"></i> Materiais
                                    </a>
                                    <a href="enviar_relatorio_professor.php?turma_id=<?php echo $disciplina_info['turma_id']; ?>&disciplina_id=<?php echo $disciplina_info['disciplina_id']; ?>" class="action-relatorios" title="Ver/Enviar Relatórios para esta Turma/Disciplina">
                                        <i class="fas fa-file-alt"></i> Relatórios
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </section>
                <?php endforeach; ?>
            <?php endif; ?>
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
<?php if($conn) mysqli_close($conn); ?>