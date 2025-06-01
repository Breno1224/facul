<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'docente') {
    header("Location: index.html");
    exit();
}
include 'db.php';
$nome_professor = $_SESSION['usuario_nome'];
$professor_id = $_SESSION['usuario_id'];
$currentPageIdentifier = 'frequencia';

// (PHP para buscar turmas do professor - código da resposta anterior)
$turmas_professor = [];
$sql_turmas = "SELECT DISTINCT t.id, t.nome_turma FROM turmas t JOIN professores_turmas_disciplinas ptd ON t.id = ptd.turma_id WHERE ptd.professor_id = ? ORDER BY t.nome_turma";
$stmt_turmas_fetch = mysqli_prepare($conn, $sql_turmas);
if ($stmt_turmas_fetch) {
    mysqli_stmt_bind_param($stmt_turmas_fetch, "i", $professor_id);
    mysqli_stmt_execute($stmt_turmas_fetch);
    $result_turmas = mysqli_stmt_get_result($stmt_turmas_fetch);
    while ($row = mysqli_fetch_assoc($result_turmas)) {
        $turmas_professor[] = $row;
    }
    mysqli_stmt_close($stmt_turmas_fetch);
}

$turma_selecionada_id = isset($_GET['turma_id']) ? intval($_GET['turma_id']) : null;
$data_aula_selecionada = isset($_GET['data_aula']) ? $_GET['data_aula'] : date('Y-m-d');
$nome_turma_selecionada = "";
$alunos_com_frequencia = [];
$professor_tem_acesso_turma = false; // Inicializa a flag

if ($turma_selecionada_id && $data_aula_selecionada) {
    foreach ($turmas_professor as $turma_p) {
        if ($turma_p['id'] == $turma_selecionada_id) {
            $professor_tem_acesso_turma = true;
            $nome_turma_selecionada = $turma_p['nome_turma'];
            break;
        }
    }
    
    if ($professor_tem_acesso_turma) {
        $sql_alunos_frequencia = "
            SELECT a.id as aluno_id, a.nome as aluno_nome, a.foto_url, f.status, f.observacao
            FROM alunos a
            LEFT JOIN frequencia f ON a.id = f.aluno_id AND f.turma_id = ? AND f.data_aula = ?
            WHERE a.turma_id = ?
            ORDER BY a.nome";
        $stmt_alunos = mysqli_prepare($conn, $sql_alunos_frequencia);
        if ($stmt_alunos) {
            mysqli_stmt_bind_param($stmt_alunos, "isi", $turma_selecionada_id, $data_aula_selecionada, $turma_selecionada_id);
            mysqli_stmt_execute($stmt_alunos);
            $result_alunos_frequencia = mysqli_stmt_get_result($stmt_alunos);
            while ($row = mysqli_fetch_assoc($result_alunos_frequencia)) {
                if (is_null($row['status'])) {
                    $row['status'] = 'P'; 
                }
                $alunos_com_frequencia[] = $row;
            }
            mysqli_stmt_close($stmt_alunos);
        }
    } else {
        $turma_selecionada_id = null; 
         $_SESSION['frequencia_status_message'] = "Você não tem acesso à turma selecionada ou ela não existe.";
         $_SESSION['frequencia_status_type'] = "status-error";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Registro de Frequência - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* (Estilos anteriores da frequência_professor.php) */
        .dashboard-section { background-color: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.07); margin-bottom: 2rem; }
        .dashboard-section h3 { font-size: 1.4rem; color: #2C1B17; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #D69D2A; }
        .form-inline label { margin-right: 0.5rem; font-weight:bold; }
        .form-inline select, .form-inline input[type="date"] { padding: 0.5rem; border-radius: 4px; border: 1px solid #ccc; margin-right: 1rem; }
        .form-inline button { padding: 0.5rem 1rem; background-color: #186D6A; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .form-inline button:hover { background-color: #104b49; }
        
        .chamada-table { width: 100%; border-collapse: collapse; margin-top: 1.5rem; }
        .chamada-table th, .chamada-table td { border: 1px solid #ddd; padding: 0.75rem; text-align: left; vertical-align: middle;}
        .chamada-table th { background-color: #f8f9fa; }
        .chamada-table .aluno-nome-clickable { cursor: pointer; color: #186D6A; font-weight: 500; }
        .chamada-table .aluno-nome-clickable:hover { text-decoration: underline; }

        /* Estilos para os botões de status lado a lado */
        .status-buttons { display: flex; gap: 5px; }
        .status-buttons input[type="radio"] { display: none; } /* Esconde o radio button real */
        .status-buttons label {
            padding: 6px 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: bold;
            transition: background-color 0.2s, color 0.2s;
            min-width: 35px; /* Para P, F, A terem largura similar */
            text-align: center;
        }
        .status-buttons input[type="radio"]:checked + label {
            color: white;
            border-color: transparent;
        }
        .status-buttons .status-P input[type="radio"]:checked + label { background-color: #28a745; } /* Verde Presente */
        .status-buttons .status-F input[type="radio"]:checked + label { background-color: #dc3545; } /* Vermelho Falta */
        .status-buttons .status-A input[type="radio"]:checked + label { background-color: #ffc107; color: #333 !important; } /* Amarelo Atraso */
        .status-buttons .status-FJ input[type="radio"]:checked + label { background-color: #17a2b8; } /* Azul Info Falta Just. */
        
        .status-buttons .status-P label:hover { background-color: #d4edda; }
        .status-buttons .status-F label:hover { background-color: #f8d7da; }
        .status-buttons .status-A label:hover { background-color: #fff3cd; }
        .status-buttons .status-FJ label:hover { background-color: #d1ecf1; }

        .chamada-table input[type="text"].observacao-input { width: 95%; padding: 0.3rem; font-size:0.85rem; border: 1px solid #eee; border-radius: 3px;}
        .btn-salvar-chamada { display: block; width: auto; padding: 0.75rem 1.5rem; background-color: #208A87; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: 1.5rem; }
        .btn-salvar-chamada:hover { background-color: #186D6A; }
        .status-message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .status-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        /* Estilos do Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fefefe; margin: 10% auto; padding: 25px; border: 1px solid #888; width: 80%; max-width: 500px; border-radius: 8px; position: relative; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-close-button { color: #aaa; float: right; font-size: 28px; font-weight: bold; }
        .modal-close-button:hover, .modal-close-button:focus { color: black; text-decoration: none; cursor: pointer; }
        #modalAlunoNome { margin-top: 0; color: #186D6A; }
        #modalAlunoStats p { font-size: 1.1rem; line-height: 1.6; }
        #modalAlunoStats .highlight { font-weight: bold; color: #208A87; }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Registro de Frequência (Prof. <?php echo htmlspecialchars($nome_professor); ?>)</h1>
        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit" id="logoutBtnHeader"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <?php include __DIR__ . '/includes/sidebar_professor.php'; ?>
        </nav>

        <main class="main-content">
            <section class="dashboard-section">
                <h3>Selecionar Turma e Data</h3>
                <form method="GET" action="frequencia_professor.php" class="form-inline">
                     <label for="turma_id">Turma:</label>
                    <select name="turma_id" id="turma_id_select" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($turmas_professor as $turma): ?>
                            <option value="<?php echo $turma['id']; ?>" <?php echo ($turma_selecionada_id == $turma['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($turma['nome_turma']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label for="data_aula">Data:</label>
                    <input type="date" name="data_aula" id="data_aula_select" value="<?php echo htmlspecialchars($data_aula_selecionada); ?>" required>
                    <button type="submit"><i class="fas fa-list-alt"></i> Carregar</button>
                </form>
            </section>

            <?php if(isset($_SESSION['frequencia_status_message'])): ?>
                <div class="status-message <?php echo $_SESSION['frequencia_status_type']; ?>">
                    <?php echo $_SESSION['frequencia_status_message']; ?>
                </div>
                <?php unset($_SESSION['frequencia_status_message']); unset($_SESSION['frequencia_status_type']); ?>
            <?php endif; ?>

            <?php if ($turma_selecionada_id && $data_aula_selecionada && $professor_tem_acesso_turma): ?>
            <section class="dashboard-section">
                <h3>Chamada para: <?php echo htmlspecialchars($nome_turma_selecionada); ?> - Data: <?php echo date("d/m/Y", strtotime($data_aula_selecionada)); ?></h3>
                <?php if (!empty($alunos_com_frequencia)): ?>
                <form action="salvar_frequencia.php" method="POST">
                    <input type="hidden" name="turma_id" value="<?php echo $turma_selecionada_id; ?>">
                    <input type="hidden" name="data_aula" value="<?php echo $data_aula_selecionada; ?>">
                    <table class="chamada-table">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th width="35%">Status (P, F, A, FJ)</th>
                                <th width="35%">Observação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alunos_com_frequencia as $aluno): ?>
                            <tr>
                                <td>
                                    <span class="aluno-nome-clickable" data-aluno-id="<?php echo $aluno['aluno_id']; ?>" data-aluno-nome="<?php echo htmlspecialchars($aluno['aluno_nome']); ?>" data-turma-id="<?php echo $turma_selecionada_id; ?>">
                                        <?php echo htmlspecialchars($aluno['aluno_nome']); ?>
                                    </span>
                                </td>
                                <td class="status-buttons">
                                    <?php $aluno_id_input = $aluno['aluno_id']; ?>
                                    <div class="status-P">
                                        <input type="radio" id="p_<?php echo $aluno_id_input; ?>" name="frequencia[<?php echo $aluno_id_input; ?>][status]" value="P" <?php echo ($aluno['status'] == 'P') ? 'checked' : ''; ?>>
                                        <label for="p_<?php echo $aluno_id_input; ?>">P</label>
                                    </div>
                                    <div class="status-F">
                                        <input type="radio" id="f_<?php echo $aluno_id_input; ?>" name="frequencia[<?php echo $aluno_id_input; ?>][status]" value="F" <?php echo ($aluno['status'] == 'F') ? 'checked' : ''; ?>>
                                        <label for="f_<?php echo $aluno_id_input; ?>">F</label>
                                    </div>
                                    <div class="status-A">
                                        <input type="radio" id="a_<?php echo $aluno_id_input; ?>" name="frequencia[<?php echo $aluno_id_input; ?>][status]" value="A" <?php echo ($aluno['status'] == 'A') ? 'checked' : ''; ?>>
                                        <label for="a_<?php echo $aluno_id_input; ?>">A</label>
                                    </div>
                                    <div class="status-FJ">
                                        <input type="radio" id="fj_<?php echo $aluno_id_input; ?>" name="frequencia[<?php echo $aluno_id_input; ?>][status]" value="FJ" <?php echo ($aluno['status'] == 'FJ') ? 'checked' : ''; ?>>
                                        <label for="fj_<?php echo $aluno_id_input; ?>">FJ</label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="frequencia[<?php echo $aluno_id_input; ?>][observacao]" class="observacao-input" value="<?php echo htmlspecialchars($aluno['observacao'] ?? ''); ?>">
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn-salvar-chamada"><i class="fas fa-save"></i> Salvar Chamada</button>
                </form>
                <?php else: ?>
                    <p>Nenhum aluno encontrado nesta turma.</p>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>
    </div>

    <div id="frequenciaModal" class="modal">
        <div class="modal-content">
            <span class="modal-close-button" onclick="document.getElementById('frequenciaModal').style.display='none'">&times;</span>
            <h3 id="modalAlunoNome"></h3>
            <div id="modalAlunoStats">
                <p>Carregando estatísticas...</p>
            </div>
        </div>
    </div>

    <script>
        // Script do menu lateral
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const container = document.querySelector('.container');
        if (menuToggle && sidebar && container) {
            menuToggle.addEventListener('click', function () {
                sidebar.classList.toggle('hidden');
                container.classList.toggle('full-width');
            });
        }

        // Script para o Modal de Frequência do Aluno
        const modal = document.getElementById('frequenciaModal');
        const modalAlunoNome = document.getElementById('modalAlunoNome');
        const modalAlunoStats = document.getElementById('modalAlunoStats');
        
        document.querySelectorAll('.aluno-nome-clickable').forEach(item => {
            item.addEventListener('click', function() {
                const alunoId = this.dataset.alunoId;
                const alunoNome = this.dataset.alunoNome;
                const turmaId = this.dataset.turmaId;

                modalAlunoNome.textContent = 'Estatísticas de Frequência: ' + alunoNome;
                modalAlunoStats.innerHTML = '<p>Carregando...</p>';
                modal.style.display = 'block';

                fetch(`buscar_estatisticas_aluno.php?aluno_id=${alunoId}&turma_id=${turmaId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            modalAlunoStats.innerHTML = `<p style="color:red;">Erro: ${data.error}</p>`;
                        } else {
                            modalAlunoStats.innerHTML = `
                                <p>Total de Aulas Registradas na Turma: <span class="highlight">${data.totalAulas}</span></p>
                                <p>Presenças (P + A): <span class="highlight">${data.presencas}</span></p>
                                <p>Faltas (F + FJ): <span class="highlight">${data.faltas}</span></p>
                                <p>Porcentagem de Presença: <span class="highlight">${data.porcentagemPresenca.toFixed(2)}%</span></p>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao buscar estatísticas:', error);
                        modalAlunoStats.innerHTML = '<p style="color:red;">Não foi possível carregar as estatísticas.</p>';
                    });
            });
        });

        // Fechar o modal ao clicar fora dele
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
<?php if($conn) mysqli_close($conn); ?>