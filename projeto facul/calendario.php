<?php
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Calendário - ACADMIX</title>
    <link rel="stylesheet" href="css/calendario.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Calendário</h1>
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="aluno.html"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="#"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="#"><i class="fas fa-tasks"></i> Tarefas</a></li>
                <li><a href="#"><i class="fas fa-calendar"></i> Horários</a></li>
                <li><a href="calendario.html" class="active"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Calendário Acadêmico</h2>

            <div class="calendar-controls">
                <button onclick="changeMonth(-1)">&#8592;</button>
                <h3 id="monthYear"></h3>
                <button onclick="changeMonth(1)">&#8594;</button>
            </div>

            <div class="calendar-grid" id="calendar-days">
                <!-- Dias renderizados via JS -->
            </div>

            <section class="event-form">
                <h3>Adicionar Evento</h3>
                <input type="text" id="event-title" placeholder="Ex: Prova de Matemática">
                <button onclick="addEvent()">Adicionar</button>
            </section>

            <section class="event-list">
                <h3>Eventos do Dia</h3>
                <ul id="event-list"></ul>
            </section>
        </main>
    </div>

    <script src="js/calendario.js"></script>
</body>
</html>
?>