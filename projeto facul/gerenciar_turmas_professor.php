<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'docente') {
    header("Location: index.html");
    exit();
}
include 'db.php';
$nome_professor = $_SESSION['usuario_nome'];
$professor_id = $_SESSION['usuario_id'];

// Define o identificador da página atual para a sidebar
$currentPageIdentifier = 'minhas_turmas';

// 1. Buscar as turmas associadas a este professor
$turmas_professor = [];
$sql_turmas = "SELECT DISTINCT t.id, t.nome_turma 
               FROM turmas t
               JOIN professores_turmas_disciplinas ptd ON t.id = ptd.turma_id
               WHERE ptd.professor_id = ?
               ORDER BY t.nome_turma";
$stmt_turmas = mysqli_prepare($conn, $sql_turmas);
if ($stmt_turmas) {
    mysqli_stmt_bind_param($stmt_turmas, "i", $professor_id);
    mysqli_stmt_execute($stmt_turmas);
    $result_turmas = mysqli_stmt_get_result($stmt_turmas);
    while ($row = mysqli_fetch_assoc($result_turmas)) {
        $turmas_professor[] = $row;
    }
    mysqli_stmt_close($stmt_turmas);
} else {
    error_log("Erro ao buscar turmas do professor: " . mysqli_error($conn));
}

// 2. Verificar se uma turma foi selecionada para listar os alunos
$alunos_da_turma = [];
$turma_selecionada_id = null;
$nome_turma_selecionada = "";
$professor_tem_acesso_turma = false; // Inicializa a flag

if (isset($_GET['turma_id']) && !empty($_GET['turma_id'])) {
    $turma_selecionada_id = intval($_GET['turma_id']);

    foreach ($turmas_professor as $turma_p) {
        if ($turma_p['id'] == $turma_selecionada_id) {
            $professor_tem_acesso_turma = true;
            $nome_turma_selecionada = $turma_p['nome_turma'];
            break;
        }
    }

    if ($professor_tem_acesso_turma) {
        $sql_alunos = "SELECT id, nome, email, foto_url 
                       FROM alunos 
                       WHERE turma_id = ? 
                       ORDER BY nome";
        $stmt_alunos = mysqli_prepare($conn, $sql_alunos);
        if ($stmt_alunos) {
            mysqli_stmt_bind_param($stmt_alunos, "i", $turma_selecionada_id);
            mysqli_stmt_execute($stmt_alunos);
            $result_alunos = mysqli_stmt_get_result($stmt_alunos);
            while ($row = mysqli_fetch_assoc($result_alunos)) {
                $alunos_da_turma[] = $row;
            }
            mysqli_stmt_close($stmt_alunos);
        } else {
            error_log("Erro ao buscar alunos da turma: " . mysqli_error($conn));
        }
    } else {
      $turma_selecionada_id = null; // Reseta se não tem acesso ou turma_id inválido
      if (isset($_GET['turma_id']) && !empty($_GET['turma_id'])) { // Só mostra erro se tentou selecionar uma turma inválida
          // Você pode querer setar uma session message aqui para exibir um erro mais proeminente
          // Ex: $_SESSION['turma_error_message'] = "Você não tem acesso a esta turma ou ela é inválida.";
      }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Turmas - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .dashboard-section { background-color: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); margin-bottom: 2rem; }
        .dashboard-section h3 { font-size: 1.4rem; color: #2C1B17; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #D69D2A; }
        .turma-select-form label { font-weight: bold; margin-right: 10px; }
        .turma-select-form select { padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; margin-right: 10px; min-width: 200px; }
        .turma-select-form button { padding: 0.5rem 1rem; background-color: #208A87; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .turma-select-form button:hover { background-color: #186D6A; }
        .student-list-container { margin-top: 2rem; }
        .student-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
        .student-card {
            background-color: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1rem; display: flex; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .student-photo {
            width: 70px; height: 70px; border-radius: 50%; object-fit: cover; margin-right: 1rem; border: 2px solid #ddd;
        }
        .student-info h4 { margin: 0 0 0.3rem 0; font-size: 1.1rem; color: #333; }
        .student-info p { margin: 0 0 0.5rem 0; font-size: 0.85rem; color: #666; }
        .student-info .btn-profile {
            font-size: 0.8rem; padding: 0.3rem 0.7rem; text-decoration: none; background-color: #D69D2A; color: white; border-radius: 4px; transition: background-color 0.2s;
        }
        .student-info .btn-profile:hover { background-color: #C58624; }
        .no-data-message { padding: 1rem; text-align: center; color: #777; background-color: #f8f9fa; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Gerenciar Turmas (Prof. <?php echo htmlspecialchars($nome_professor); ?>)</h1>
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
            <section class="dashboard-section">
                <h3>Selecione uma Turma</h3>
                <?php if (!empty($turmas_professor)): ?>
                    <form method="GET" action="gerenciar_turmas_professor.php" class="turma-select-form">
                        <label for="turma_id">Turma:</label>
                        <select name="turma_id" id="turma_id" onchange="this.form.submit()"> <option value="">-- Selecione uma Turma --</option>
                            <?php foreach ($turmas_professor as $turma): ?>
                                <option value="<?php echo $turma['id']; ?>" <?php echo ($turma_selecionada_id == $turma['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($turma['nome_turma']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        </form>
                <?php else: ?>
                    <p class="no-data-message">Você não está associado a nenhuma turma no momento ou nenhuma turma foi cadastrada.</p>
                <?php endif; ?>
            </section>

            <?php if ($turma_selecionada_id && $professor_tem_acesso_turma): ?>
                <section class="dashboard-section student-list-container">
                    <h3>Alunos da Turma: <?php echo htmlspecialchars($nome_turma_selecionada); ?></h3>
                    <?php if (!empty($alunos_da_turma)): ?>
                        <div class="student-grid">
                            <?php foreach ($alunos_da_turma as $aluno): ?>
                                <div class="student-card">
                                    <img src="<?php echo htmlspecialchars(!empty($aluno['foto_url']) ? $aluno['foto_url'] : 'img/alunos/default_avatar.png'); ?>" 
                                         alt="Foto de <?php echo htmlspecialchars($aluno['nome']); ?>" 
                                         class="student-photo"
                                         onerror="this.onerror=null; this.src='img/alunos/default_avatar.png';">
                                    <div class="student-info">
                                        <h4><?php echo htmlspecialchars($aluno['nome']); ?></h4>
                                        <?php if(!empty($aluno['email'])): ?>
                                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($aluno['email']); ?></p>
                                        <?php endif; ?>
                                        <a href="perfil_aluno_detalhado.php?aluno_id=<?php echo $aluno['id']; ?>" class="btn-profile">
                                            <i class="fas fa-user-circle"></i> Ver Perfil
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data-message">Nenhum aluno encontrado para esta turma.</p>
                    <?php endif; ?>
                </section>
            <?php elseif (isset($_GET['turma_id']) && !$professor_tem_acesso_turma && !empty($_GET['turma_id'])): ?>
                 <section class="dashboard-section student-list-container">
                    <h3>Alunos da Turma</h3>
                     <p class="no-data-message">Você não tem permissão para visualizar esta turma ou a turma selecionada é inválida.</p>
                 </section>
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