<?php
session_start();
// Verifica se o usuário é um docente logado
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'docente') {
    header("Location: index.html");
    exit();
}
include 'db.php'; // Conexão com o banco

$nome_professor = $_SESSION['usuario_nome'];
$professor_id = $_SESSION['usuario_id']; // Guardamos o ID do professor

// Define o identificador da página atual para a sidebar
$currentPageIdentifier = 'lancar_notas';

// Buscar turmas e disciplinas do banco
// Idealmente, buscar apenas as turmas/disciplinas que ESTE professor leciona.
// Por agora, a query busca todas, como no seu código original.
// Para buscar apenas as do professor:
// $sql_turmas_prof = "SELECT DISTINCT t.id, t.nome_turma FROM turmas t JOIN professores_turmas_disciplinas ptd ON t.id = ptd.turma_id WHERE ptd.professor_id = ? ORDER BY t.nome_turma";
// $sql_disciplinas_prof = "SELECT DISTINCT d.id, d.nome_disciplina FROM disciplinas d JOIN professores_turmas_disciplinas ptd ON d.id = ptd.disciplina_id WHERE ptd.professor_id = ? ORDER BY d.nome_disciplina";
// E então usar prepared statements com $professor_id.

$turmas_result_query = mysqli_query($conn, "SELECT id, nome_turma FROM turmas ORDER BY nome_turma");
$disciplinas_result_query = mysqli_query($conn, "SELECT id, nome_disciplina FROM disciplinas ORDER BY nome_disciplina");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lançar Notas - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Estilos adicionais se necessário (mantidos do seu código) */
        .form-section label, .form-section select, .form-section input, .form-section button {
            margin-bottom: 10px;
            display: block;
            width: calc(100% - 20px); 
        }
        .form-section select, .form-section input[type="text"], .form-section input[type="number"] {
            padding: 8px;
        }
        .form-section button { /* Estilo para o botão "Carregar Alunos" */
            padding: 10px 15px;
            background-color: #D69D2A;
            color: white;
            border: none;
            cursor: pointer;
            width: auto; /* Para não ocupar 100% */
        }
         #alunosSection button[type="submit"] { /* Estilo para o botão "Lançar Notas" */
            padding: 10px 15px;
            background-color: #208A87; /* Cor primária do tema */
            color: white;
            border: none;
            cursor: pointer;
            width: auto;
            margin-top: 1rem;
        }
        #alunosSection table { margin-top: 15px; }
        #alunosSection th, #alunosSection td { text-align: left; padding: 8px; border: 1px solid #ddd;}
        .hidden { display: none; }
        #statusMessage { margin-top: 15px; padding: 10px; border-radius: 4px; }
        .status-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;}
        .status-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;}
    </style>
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Lançar Notas (Prof. <?php echo htmlspecialchars($nome_professor); ?>)</h1>
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
            <h2>Lançamento de Notas</h2>
            <div id="statusMessage" class="hidden"></div>

            <div class="form-section">
                <label for="turmaSelect">Turma:</label>
                <select id="turmaSelect" name="turma_id">
                    <option value="">Selecione uma Turma</option>
                    <?php while ($turma = mysqli_fetch_assoc($turmas_result_query)): ?>
                        <option value="<?php echo $turma['id']; ?>"><?php echo htmlspecialchars($turma['nome_turma']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="disciplinaSelect">Disciplina:</label>
                <select id="disciplinaSelect" name="disciplina_id">
                    <option value="">Selecione uma Disciplina</option>
                     <?php while ($disciplina = mysqli_fetch_assoc($disciplinas_result_query)): ?>
                        <option value="<?php echo $disciplina['id']; ?>"><?php echo htmlspecialchars($disciplina['nome_disciplina']); ?></option>
                    <?php endwhile; ?>
                </select>

                <label for="avaliacaoInput">Avaliação:</label>
                <input type="text" id="avaliacaoInput" name="avaliacao" placeholder="Ex: Prova 1, Trabalho Bimestral">

                <label for="bimestreSelect">Bimestre:</label>
                <select id="bimestreSelect" name="bimestre">
                    <option value="">Selecione o Bimestre</option>
                    <option value="1">1º Bimestre</option>
                    <option value="2">2º Bimestre</option>
                    <option value="3">3º Bimestre</option>
                    <option value="4">4º Bimestre</option>
                </select>

                <button type="button" onclick="carregarAlunos()">Carregar Alunos</button>
            </div>

            <div id="alunosSection" class="hidden">
                <h3>Inserir Notas</h3>
                <form id="notasForm">
                    <input type="hidden" name="turma_id_form" id="turma_id_form">
                    <input type="hidden" name="disciplina_id_form" id="disciplina_id_form">
                    <input type="hidden" name="avaliacao_form" id="avaliacao_form">
                    <input type="hidden" name="bimestre_form" id="bimestre_form">

                    <table>
                        <thead>
                            <tr>
                                <th>Aluno (ID)</th>
                                <th>Nome</th>
                                <th>Nota (0.00 - 10.00)</th>
                            </tr>
                        </thead>
                        <tbody id="alunosTableBody">
                            </tbody>
                    </table>
                    <button type="submit">Lançar Notas</button>
                </form>
            </div>
        </main>
    </div>

    <script src="js/lancar-notas.js"></script>
    <script>
        // Script do menu lateral
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden'); 
            document.querySelector('.container').classList.toggle('full-width'); 
        });
    </script>
</body>
</html>
<?php if($conn) mysqli_close($conn); ?>