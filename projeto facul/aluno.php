<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['role'] != 'aluno') {
    header("Location: index.html");
    exit();
}
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Home do Aluno - ACADMIX</title>
    <link rel="stylesheet" href="css/aluno.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Área do Aluno</h1>
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="#"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="boletim.html"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="#"><i class="fas fa-tasks"></i> Tarefas</a></li>
                <li><a href="calendario.html"><i class="fas fa-calendar"></i> calendario</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
                
            </ul>
        </nav>

        <main class="main-content">
            <h2>Bem-vindo(a), aluno!</h2>
            <p>Você está logado em sua área exclusiva do sistema ACADMIX.</p>

            <section class="card">
                <h3>Destaques de hoje</h3>
                <ul>
                    <li>📚 Aula de Matemática às 10h</li>
                    <li>📝 Entregar tarefa de História até às 18h</li>
                    <li>📢 Aviso: reunião de classe amanhã às 13h</li>
                </ul>
            </section>
        </main>
    </div>

    <script>
        // Botão de logout
        document.getElementById('logoutBtn').addEventListener('click', function () {
            alert('Você saiu da conta.');
            window.location.href = 'index.html';
        });

        // Mostrar/ocultar menu lateral e ajustar conteúdo
        const sidebar = document.getElementById('sidebar');
        const container = document.querySelector('.container');

        document.getElementById('menu-toggle').addEventListener('click', function () {
            sidebar.classList.toggle('hidden');
            container.classList.toggle('full-width');
        });
    </script>

</body>
</html>
