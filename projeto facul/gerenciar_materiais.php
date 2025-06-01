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
$currentPageIdentifier = 'enviar_materiais';

// Buscar disciplinas e turmas para os selects
$disciplinas_result_query = mysqli_query($conn, "SELECT id, nome_disciplina FROM disciplinas ORDER BY nome_disciplina");
$turmas_result_query = mysqli_query($conn, "SELECT id, nome_turma FROM turmas ORDER BY nome_turma");

// Buscar materiais já enviados por este professor (para listagem)
$materiais_enviados_sql = "
    SELECT m.id, m.titulo, m.tipo_material, d.nome_disciplina, t.nome_turma, m.data_upload
    FROM materiais_didaticos m
    LEFT JOIN disciplinas d ON m.disciplina_id = d.id
    LEFT JOIN turmas t ON m.turma_id = t.id
    WHERE m.professor_id = ?
    ORDER BY m.data_upload DESC";
$stmt_materiais = mysqli_prepare($conn, $materiais_enviados_sql);
mysqli_stmt_bind_param($stmt_materiais, "i", $professor_id);
mysqli_stmt_execute($stmt_materiais);
$materiais_enviados_result = mysqli_stmt_get_result($stmt_materiais);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Materiais Didáticos - ACADMIX</title>
    <link rel="stylesheet" href="css/professor.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Estilos mantidos do seu código original */
        .form-section, .list-section { margin-bottom: 2rem; padding: 1.5rem; background-color: #fff; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .form-section label { display: block; margin-top: 1rem; margin-bottom: 0.5rem; font-weight: bold; }
        .form-section input[type="text"],
        .form-section input[type="url"],
        .form-section input[type="file"],
        .form-section textarea,
        .form-section select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-section textarea { min-height: 100px; }
        .form-section .radio-group label { font-weight: normal; margin-right: 15px; }
        .form-section .radio-group input[type="radio"] { margin-right: 5px; vertical-align: middle;}
        .form-section button[type="submit"] { /* Botão "Enviar Material" */
            background-color: #208A87; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; margin-top: 1.5rem;
        }
        .form-section button[type="submit"]:hover { background-color: #186D6A; }
        .status-message { padding: 1rem; margin-bottom: 1rem; border-radius: 4px; }
        .status-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .list-section table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .list-section th, .list-section td { padding: 0.75rem; border: 1px solid #ddd; text-align: left; }
        .list-section th { background-color: #f2f2f2; }
        .btn-delete { color: red; text-decoration: none; } /* Se você adicionar botões de exclusão no futuro */
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Gerenciar Materiais (Prof. <?php echo htmlspecialchars($nome_professor); ?>)</h1>
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
            <h2>Enviar Novo Material Didático</h2>

            <?php if(isset($_SESSION['status_message'])): // Usar a mesma chave de sessão que salvar_material.php usa ?>
                <div class="status-message <?php echo $_SESSION['status_type']; ?>">
                    <?php echo $_SESSION['status_message']; ?>
                </div>
                <?php unset($_SESSION['status_message']); unset($_SESSION['status_type']); ?>
            <?php endif; ?>

            <section class="form-section">
                <form action="salvar_material.php" method="POST" enctype="multipart/form-data">
                    <label for="titulo">Título do Material:</label>
                    <input type="text" id="titulo" name="titulo" required>

                    <label for="descricao">Descrição:</label>
                    <textarea id="descricao" name="descricao"></textarea>

                    <label for="disciplina_id">Disciplina:</label>
                    <select id="disciplina_id" name="disciplina_id" required>
                        <option value="">Selecione a Disciplina</option>
                        <?php while($disciplina = mysqli_fetch_assoc($disciplinas_result_query)): ?>
                            <option value="<?php echo $disciplina['id']; ?>"><?php echo htmlspecialchars($disciplina['nome_disciplina']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label for="turma_id">Turma (Opcional - deixe em branco para global da disciplina):</label>
                    <select id="turma_id" name="turma_id">
                        <option value="">Todas as Turmas / Global</option>
                        <?php mysqli_data_seek($turmas_result_query, 0); // Resetar ponteiro do resultado ?>
                        <?php while($turma = mysqli_fetch_assoc($turmas_result_query)): ?>
                            <option value="<?php echo $turma['id']; ?>"><?php echo htmlspecialchars($turma['nome_turma']); ?></option>
                        <?php endwhile; ?>
                    </select>

                    <label>Tipo de Envio:</label>
                    <div class="radio-group">
                        <input type="radio" id="tipo_arquivo" name="tipo_envio" value="arquivo" checked onchange="toggleEnvioFields()"> <label for="tipo_arquivo">Arquivo</label>
                        <input type="radio" id="tipo_link" name="tipo_envio" value="link" onchange="toggleEnvioFields()"> <label for="tipo_link">Link Externo</label>
                    </div>

                    <div id="campo_arquivo">
                        <label for="arquivo_material">Selecione o Arquivo:</label>
                        <input type="file" id="arquivo_material" name="arquivo_material">
                    </div>

                    <div id="campo_link" style="display:none;">
                        <label for="link_material">URL do Material (Ex: link do YouTube, Google Drive, artigo):</label>
                        <input type="url" id="link_material" name="link_material" placeholder="https://www.example.com/material">
                    </div>
                    
                    <label for="tipo_material">Tipo do Material (Ex: PDF, Vídeo, Apresentação):</label>
                    <input type="text" id="tipo_material" name="tipo_material" required placeholder="Descreva o tipo do material">

                    <button type="submit">Enviar Material</button>
                </form>
            </section>

            <section class="list-section">
                <h2>Materiais Enviados</h2>
                <?php if(mysqli_num_rows($materiais_enviados_result) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Disciplina</th>
                            <th>Turma</th>
                            <th>Tipo</th>
                            <th>Data Envio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($material = mysqli_fetch_assoc($materiais_enviados_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($material['titulo']); ?></td>
                            <td><?php echo htmlspecialchars($material['nome_disciplina'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($material['nome_turma'] ?? 'Global'); ?></td>
                            <td><?php echo htmlspecialchars($material['tipo_material']); ?></td>
                            <td><?php echo date("d/m/Y H:i", strtotime($material['data_upload'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>Nenhum material enviado por você ainda.</p>
                <?php endif; ?>
                <?php if(isset($stmt_materiais)) mysqli_stmt_close($stmt_materiais); // Fechar o statement aqui ?>
            </section>
        </main>
    </div>

    <script>
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.container').classList.toggle('full-width');
        });

        function toggleEnvioFields() {
            if (document.getElementById('tipo_arquivo').checked) {
                document.getElementById('campo_arquivo').style.display = 'block';
                document.getElementById('arquivo_material').required = true; // Torna obrigatório se selecionado
                document.getElementById('campo_link').style.display = 'none';
                document.getElementById('link_material').required = false;
            } else { // tipo_link selecionado
                document.getElementById('campo_arquivo').style.display = 'none';
                document.getElementById('arquivo_material').required = false;
                document.getElementById('campo_link').style.display = 'block';
                document.getElementById('link_material').required = true; // Torna obrigatório se selecionado
            }
        }
        // Chamar a função para garantir o estado inicial correto ao carregar a página
        toggleEnvioFields();
    </script>
</body>
</html>
<?php if($conn) mysqli_close($conn); ?>