

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Home do Professor - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Área do Professor</h1>
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="#" class="active"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="lancar-notas.php"><i class="fas fa-pen"></i> Lançar Notas</a></li>
                <li><a href="#"><i class="fas fa-clipboard-list"></i> Frequência</a></li>
                <li><a href="#"><i class="fas fa-bullhorn"></i> Comunicados</a></li>
                <li><a href="#"><i class="fas fa-users"></i> Turmas</a></li>
                <li><a href="#"><i class="fas fa-book"></i> Disciplinas</a></li>
                <li><a href="#"><i class="fas fa-file-alt"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Bem-vindo(a), Professor(a)!</h2>
            <p>Você está logado em sua área exclusiva do sistema ACADMIX.</p>

            <section class="card">
                <h3>Atividades Recentes</h3>
                <ul>
                    <li>✅  Notas de Matemática lançadas para Turma 1A</li>
                    <li>📢  Aviso: reunião pedagógica amanhã às 14h</li>
                    <li>📝  Frequência pendente para Turma 2B</li>
                </ul>
            </section>
        </main>
    </div>

    <script src="js/professor.js"></script>

</body>

</html>
