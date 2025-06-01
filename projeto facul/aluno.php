<?php
session_start();
// Verifica se o usuário é um aluno logado
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'aluno') {
    header("Location: index.html");
    exit();
}
// include 'db.php'; // Descomente se for buscar notícias do banco no futuro

$nome_aluno = $_SESSION['usuario_nome']; // Usado no título da página ou em outros locais se necessário

// --- Lógica para buscar notícias do banco (Exemplo Futuro) ---
// $noticias = [];
// $sql_noticias = "SELECT titulo, resumo, link_externo, imagem_url, data_publicacao, categoria
//                  FROM noticias_academicas
//                  ORDER BY data_publicacao DESC LIMIT 10"; // Pega as 10 mais recentes
// $result_noticias = mysqli_query($conn, $sql_noticias);
// while ($row = mysqli_fetch_assoc($result_noticias)) {
//     $noticias[] = $row;
// }
// --- Fim da Lógica Futura ---

// Para este exemplo, usaremos dados estáticos (simulando notícias):
$noticias_static = [
    [
        "titulo" => "Inscrições para o ENEM 2025 Abertas!",
        "resumo" => "O período de inscrição para o Exame Nacional do Ensino Médio (ENEM) de 2025 já começou. Não perca o prazo e garanta sua participação no maior vestibular do país. Saiba mais sobre as datas, taxas e como se preparar.",
        "link_externo" => "https://www.gov.br/inep/pt-br/areas-de-atuacao/avaliacao-e-exames-educacionais/enem", // Link de exemplo
        "imagem_url" => "img/noticias/enem_2025.jpg", // Crie a pasta img/noticias/ e coloque uma imagem
        "data_publicacao" => "2025-05-28",
        "categoria" => "ENEM"
    ],
    [
        "titulo" => "Dicas Essenciais para Organizar sua Rotina de Estudos",
        "resumo" => "Manter uma rotina de estudos organizada é fundamental para o sucesso acadêmico. Confira dicas práticas sobre como criar um cronograma eficiente, definir metas realistas e utilizar ferramentas que podem otimizar seu aprendizado.",
        "link_externo" => "#", // Poderia ser um link para um artigo interno ou externo
        "imagem_url" => "img/noticias/rotina_estudos.jpg",
        "data_publicacao" => "2025-05-25",
        "categoria" => "Dicas de Estudo"
    ],
    [
        "titulo" => "Novos Materiais de Matemática Adicionados!",
        "resumo" => "Professores adicionaram novas videoaulas e listas de exercícios de Matemática na seção de Materiais Didáticos. Acesse agora para complementar seus estudos sobre Funções e Geometria Espacial.",
        "link_externo" => "materiais.php", // Link para a página de materiais
        "imagem_url" => "img/noticias/novos_materiais.jpg",
        "data_publicacao" => "2025-05-22",
        "categoria" => "Materiais Didáticos"
    ],
    [
        "titulo" => "Feira de Profissões Online: Descubra sua Carreira",
        "resumo" => "Participe da Feira de Profissões Online na próxima semana! Uma ótima oportunidade para conhecer diversas áreas de atuação, conversar com profissionais e tirar dúvidas sobre o mercado de trabalho.",
        "link_externo" => "#",
        "imagem_url" => "img/noticias/feira_profissoes.jpg",
        "data_publicacao" => "2025-05-19",
        "categoria" => "Eventos"
    ]
];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Portal do Aluno - ACADMIX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/aluno.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Estilos específicos para a página de notícias, complementando aluno.css */
        .main-content h2.page-title { /* Estilo para o título principal da página de notícias */
            text-align: center;
            font-size: 1.8rem;
            color: #2C1B17; /* Cor escura do texto, conforme seu tema */
            margin-bottom: 2rem;
            border-bottom: 2px solid #D69D2A; /* Detalhe com cor de destaque */
            padding-bottom: 0.5rem;
            display: inline-block; /* Para a borda ficar só no texto */
        }
        /* NOVO ESTILO PARA A MENSAGEM DE BOAS-VINDAS */
        .welcome-message-aluno {
            text-align: center;
            font-size: 1.5rem; /* Tamanho um pouco menor que o título principal da seção */
            color: #333;     /* Cor um pouco mais suave */
            margin-bottom: 0.5rem; /* Menos espaço abaixo, pois o título da seção vem logo em seguida */
            font-weight: 500; /* Peso da fonte normal ou semi-bold */
        }
        .news-feed {
            display: grid;
            gap: 1.5rem; /* Espaçamento entre os cards de notícia */
        }
        .news-item {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            overflow: hidden; /* Para a imagem não vazar do border-radius */
            display: flex;
            flex-direction: column;
             transition: transform 0.2s ease-in-out;
        }
        .news-item:hover {
            transform: translateY(-5px);
        }
        .news-image-container {
            width: 100%;
            max-height: 200px; /* Altura máxima para a imagem */
            overflow: hidden;
        }
        .news-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Garante que a imagem cubra o espaço sem distorcer */
            display: block;
        }
        .news-content {
            padding: 1rem 1.5rem 1.5rem 1.5rem;
            flex-grow: 1; /* Faz o conteúdo ocupar o espaço restante */
            display: flex;
            flex-direction: column;
        }
        .news-title {
            font-size: 1.3rem;
            color: #208A87; /* Cor ciano do tema */
            margin-bottom: 0.5rem;
        }
        .news-meta {
            font-size: 0.8rem;
            color: #777;
            margin-bottom: 0.75rem;
        }
        .news-meta .news-date {
            font-weight: bold;
        }
        .news-meta .news-category {
            background-color: #f0f0f0;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            color: #555;
        }
        .news-summary {
            font-size: 0.95rem;
            color: #444;
            line-height: 1.6;
            margin-bottom: 1rem;
            flex-grow: 1;
        }
        .btn-news-readmore {
            display: inline-block;
            padding: 0.5rem 1rem;
            background-color: #D69D2A; /* Cor mostarda do tema */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9rem;
            text-align: center;
            align-self: flex-start; /* Alinha o botão à esquerda */
            transition: background-color 0.3s;
        }
        .btn-news-readmore:hover {
            background-color: #C58624; /* Tom mais escuro */
        }
        .btn-news-readmore i {
            margin-left: 5px;
        }
        .no-news {
            text-align: center;
            padding: 30px;
            color: #777;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Portal do Aluno</h1>
        <form action="logout.php" method="post" style="display: inline;">
            <button type="submit" id="logoutBtnHeader"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="aluno.php" class="active"><i class="fas fa-home"></i> Início & Notícias</a></li>
                <li><a href="boletim.php"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="comunicados_aluno.php"><i class="fas fa-bell"></i> Comunicados</a></li>
                <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                <li><a href="materiais.php"><i class="fas fa-book-open"></i> Materiais Didáticos</a></li>
                <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li> </ul>
            
        </nav>

        <main class="main-content">
            
            <div class="welcome-message-aluno">
                <h3>Bem-vindo(a), <?php echo htmlspecialchars($nome_aluno); ?>!</h3>
            </div>

            <div style="text-align: center;"> 
                 <h2 class="page-title">Fique por Dentro!</h2>
            </div>

            <div class="news-feed">
                <?php if (empty($noticias_static)): ?>
                    <p class="no-news">Nenhuma notícia ou atualização no momento.</p>
                <?php else: ?>
                    <?php foreach ($noticias_static as $noticia): ?>
                        <article class="news-item">
                            <?php if (!empty($noticia['imagem_url']) && file_exists($noticia['imagem_url'])): ?>
                            <div class="news-image-container">
                                <img src="<?php echo htmlspecialchars($noticia['imagem_url']); ?>" alt="Imagem para <?php echo htmlspecialchars($noticia['titulo']); ?>" class="news-image">
                            </div>
                            <?php endif; ?>
                            <div class="news-content">
                                <h3 class="news-title"><?php echo htmlspecialchars($noticia['titulo']); ?></h3>
                                <p class="news-meta">
                                    <span class="news-date"><i class="fas fa-calendar-alt"></i> <?php echo date("d/m/Y", strtotime($noticia['data_publicacao'])); ?></span>
                                    <?php if(!empty($noticia['categoria'])): ?>
                                        | <span class="news-category"><?php echo htmlspecialchars($noticia['categoria']); ?></span>
                                    <?php endif; ?>
                                </p>
                                <p class="news-summary"><?php echo htmlspecialchars($noticia['resumo']); ?></p>
                                <?php if (!empty($noticia['link_externo']) && $noticia['link_externo'] !== '#'): ?>
                                <a href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="btn-news-readmore" target="_blank">
                                    Saiba Mais <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php elseif ($noticia['link_externo'] === 'materiais.php'): ?>
                                 <a href="<?php echo htmlspecialchars($noticia['link_externo']); ?>" class="btn-news-readmore">
                                    Ver Materiais <i class="fas fa-arrow-right"></i>
                                 </a>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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