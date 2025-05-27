<?php
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Boletim - ACADMIX</title>
    <link rel="stylesheet" href="css/boletim.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Boletim</h1>
        <button id="logoutBtn"><i class="fas fa-sign-out-alt"></i> Sair</button>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="aluno.html"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="boletim.html" class="active"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="#"><i class="fas fa-tasks"></i> Tarefas</a></li>
                <li><a href="#"><i class="fas fa-calendar"></i> Horários</a></li>
                <li><a href="calendario.html"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <h2>Boletim Escolar</h2>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Disciplina</th>
                            <th>1º Bimestre</th>
                            <th>2º Bimestre</th>
                            <th>3º Bimestre</th>
                            <th>4º Bimestre</th>
                            <th>Média Final</th>
                            <th>Situação</th>
                        </tr>
                    </thead>
                    <tbody>
                          <tr class="subject-row">
        <td>Matemática</td>
        <td>8.5</td>
        <td>7.8</td>
        <td>9.0</td>
        <td>8.2</td>
        <td>8.4</td>
        <td>Aprovado</td>
    </tr>
    <tr class="details-row">
        <td colspan="7">
            <div class="details-content">
                <p>Prova 1: 8.0</p>
                <p>Prova 2: 9.0</p>
                <p>Atividade: 8.5</p>
            </div>
        </td>
    </tr>
                        <tr>
                            <td>Português</td>
                            <td>7.0</td>
                            <td>6.5</td>
                            <td>7.2</td>
                            <td>8.0</td>
                            <td>7.2</td>
                            <td>Aprovado</td>
                        </tr>
                        <tr>
                            <td>História</td>
                            <td>9.0</td>
                            <td>8.7</td>
                            <td>9.5</td>
                            <td>9.8</td>
                            <td>9.3</td>
                            <td>Aprovado</td>
                        </tr>
                        <tr>
                            <td>Geografia</td>
                            <td>8.0</td>
                            <td>7.5</td>
                            <td>8.7</td>
                            <td>8.1</td>
                            <td>8.1</td>
                            <td>Aprovado</td>
                        </tr>
                        <tr>
                            <td>Ciências</td>
                            <td>7.8</td>
                            <td>8.2</td>
                            <td>7.5</td>
                            <td>8.0</td>
                            <td>7.9</td>
                            <td>Aprovado</td>
                        </tr>
                        <tr>
                            <td>Inglês</td>
                            <td>9.5</td>
                            <td>9.0</td>
                            <td>9.2</td>
                            <td>9.4</td>
                            <td>9.3</td>
                            <td>Aprovado</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        const toggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const container = document.querySelector('.container');

        toggle.addEventListener('click', () => {
            container.classList.toggle('full-width');
        });
        const subjectRows = document.querySelectorAll('.subject-row');

subjectRows.forEach(row => {
    row.addEventListener('click', () => {
        const nextRow = row.nextElementSibling;
        if (nextRow.classList.contains('details-row')) {
            nextRow.style.display = 
                nextRow.style.display === 'table-row' ? 'none' : 'table-row';
        }
    });
});

    </script>
</body>
</html>
?>