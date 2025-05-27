
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lançar Notas - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Lançar Notas</h1>
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="professor.html"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="#" class="active"><i class="fas fa-pen"></i> Lançar Notas</a></li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> Frequência</a></li>
                <li><a href="#"><i class="fas fa-bullhorn"></i> Comunicados</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Turmas</a></li>
                <li><a href="#"><i class="fas fa-book"></i> Disciplinas</a></li>
                <li><a href="#"><i class="fas fa-file-alt"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Lançamento de Notas</h2>

            <div class="form-section">
                <label>Turma:</label>
                <select id="turmaSelect"></select>

                <label>Disciplina:</label>
                <select id="disciplinaSelect"></select>

                <label>Avaliação:</label>
                <input type="text" id="avaliacaoInput" placeholder="Ex: Prova 1">

                <button onclick="carregarAlunos()">Carregar Alunos</button>
            </div>

            <div id="alunosSection" class="hidden">
                <h3>Inserir Notas</h3>
                <form id="notasForm">
                    <table>
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>Nota</th>
                            </tr>
                        </thead>
                        <tbody id="alunosTable">
                            <!-- Lista de alunos via JS -->
                        </tbody>
                    </table>
                    <button type="submit">Lançar Notas</button>
                </form>
            </div>
        </main>
    </div>

    <script src="js/lancar-notas.js"></script>

</body>
</html>
