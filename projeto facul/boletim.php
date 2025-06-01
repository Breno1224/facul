<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'aluno') {
    header("Location: index.html");
    exit();
}
include 'db.php'; // Conexão com o banco

$aluno_id = $_SESSION['usuario_id'];
$nome_aluno = $_SESSION['usuario_nome'];

// Buscar notas do aluno, agrupadas por disciplina e bimestre
$sql_notas = "
    SELECT 
        d.nome_disciplina, 
        n.avaliacao, 
        n.nota, 
        n.bimestre,
        n.data_lancamento
    FROM notas n
    JOIN disciplinas d ON n.disciplina_id = d.id
    WHERE n.aluno_id = ?
    ORDER BY d.nome_disciplina, n.bimestre, n.data_lancamento, n.avaliacao";

$stmt_notas = mysqli_prepare($conn, $sql_notas);
mysqli_stmt_bind_param($stmt_notas, "i", $aluno_id);
mysqli_stmt_execute($stmt_notas);
$resultado_notas = mysqli_stmt_get_result($stmt_notas);

$boletim_data = [];
while ($nota_row = mysqli_fetch_assoc($resultado_notas)) {
    $boletim_data[$nota_row['nome_disciplina']][$nota_row['bimestre']][] = [
        'avaliacao' => $nota_row['avaliacao'],
        'nota' => $nota_row['nota']
    ];
}
mysqli_stmt_close($stmt_notas);
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Boletim - ACADMIX</title>
    <link rel="stylesheet" href="css/boletim.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .details-row { display: none; }
        .details-content { padding: 10px; background-color: #f9f9f9; }
        .details-content ul { list-style-type: none; padding-left: 15px; }
        .details-content li { margin-bottom: 5px; }
        .subject-row:hover { cursor: pointer; background-color: #f1f1f1; }
        .no-grades td { text-align: center; padding: 20px; color: #777; }
        /* Cores para situação (exemplo) */
        .situacao-aprovado { color: green; font-weight: bold; }
        .situacao-reprovado { color: red; font-weight: bold; }
        .situacao-cursando { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <button id="menu-toggle" class="menu-btn"><i class="fas fa-bars"></i></button>
        <h1>ACADMIX - Boletim de <?php echo htmlspecialchars($nome_aluno); ?></h1>
        <form action="logout.php" method="post" style="display: inline;">
             <button type="submit"><i class="fas fa-sign-out-alt"></i> Sair</button>
        </form>
    </header>

    <div class="container">
        <nav class="sidebar" id="sidebar">
            <ul>
                <li><a href="aluno.php"><i class="fas fa-home"></i> Início</a></li>
                <li><a href="boletim.php" class="active"><i class="fas fa-book"></i> Boletim</a></li>
                <li><a href="calendario.php"><i class="fas fa-calendar-alt"></i> Calendário</a></li>
                 <li><a href="#"><i class="fas fa-user"></i> Perfil</a></li>
               <li><a href="#"><i class="fas fa-tasks"></i> Tarefas</a></li>
             <li><a href="materiais.php" class="active"><i class="fas fa-book-open"></i> Materiais Didáticos</a></li>
             <li><a href="comunicados_aluno.php"><i class="fas fa-bell"></i> Comunicados</a></li>
            
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
                        <?php if (empty($boletim_data)): ?>
                            <tr class="no-grades">
                                <td colspan="7">Nenhuma nota lançada no sistema até o momento.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($boletim_data as $disciplina => $bimestres): ?>
                                <tr class="subject-row">
                                    <td><?php echo htmlspecialchars($disciplina); ?></td>
                                    <?php
                                    $soma_medias_bimestrais = 0;
                                    $bimestres_com_media = 0;
                                    $media_final_disciplina = 0;

                                    for ($b = 1; $b <= 4; $b++):
                                        $notas_bimestre_atual = isset($bimestres[$b]) ? $bimestres[$b] : [];
                                        $soma_notas_bimestre = 0;
                                        $qtd_notas_bimestre = count($notas_bimestre_atual);
                                        
                                        if ($qtd_notas_bimestre > 0) {
                                            foreach ($notas_bimestre_atual as $avaliacao_info) {
                                                $soma_notas_bimestre += floatval($avaliacao_info['nota']);
                                            }
                                            $media_bimestre = $soma_notas_bimestre / $qtd_notas_bimestre;
                                            echo '<td>' . number_format($media_bimestre, 2, ',', '.') . '</td>';
                                            $soma_medias_bimestrais += $media_bimestre;
                                            $bimestres_com_media++;
                                        } else {
                                            echo '<td>-</td>'; // Sem nota para este bimestre
                                        }
                                    endfor;

                                    // Cálculo simples de Média Final e Situação (você pode refinar esta lógica)
                                    if ($bimestres_com_media > 0) {
                                        $media_final_disciplina = $soma_medias_bimestrais / $bimestres_com_media; // Ou $soma_medias_bimestrais / 4 se todos bimestres contam
                                        echo '<td>' . number_format($media_final_disciplina, 2, ',', '.') . '</td>';
                                        if ($media_final_disciplina >= 6.0) { // Exemplo de média para aprovação
                                            echo '<td class="situacao-aprovado">Aprovado</td>';
                                        } else {
                                            echo '<td class="situacao-reprovado">Reprovado</td>';
                                        }
                                    } else {
                                        echo '<td>-</td>'; // Média Final
                                        echo '<td class="situacao-cursando">Cursando</td>'; // Situação
                                    }
                                    ?>
                                </tr>
                                <tr class="details-row">
                                    <td colspan="7">
                                        <div class="details-content">
                                            <h4>Detalhes de <?php echo htmlspecialchars($disciplina); ?>:</h4>
                                            <?php for ($b = 1; $b <= 4; $b++): ?>
                                                <?php if (isset($bimestres[$b]) && count($bimestres[$b]) > 0): ?>
                                                    <p><strong><?php echo $b; ?>º Bimestre:</strong></p>
                                                    <ul>
                                                        <?php foreach ($bimestres[$b] as $avaliacao_info): ?>
                                                            <li><?php echo htmlspecialchars($avaliacao_info['avaliacao']); ?>: <?php echo number_format(floatval($avaliacao_info['nota']), 2, ',', '.'); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <?php if (empty($bimestres)): ?>
                                                <p>Nenhuma avaliação detalhada para esta disciplina.</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        // Script do menu lateral (se o seu aluno.js não for global)
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.querySelector('.container').classList.toggle('full-width');
        });

        // Script para mostrar/ocultar detalhes da disciplina
        const subjectRows = document.querySelectorAll('.subject-row');
        subjectRows.forEach(row => {
            row.addEventListener('click', () => {
                const nextRow = row.nextElementSibling;
                if (nextRow && nextRow.classList.contains('details-row')) {
                    nextRow.style.display = nextRow.style.display === 'table-row' ? 'none' : 'table-row';
                }
            });
        });
    </script>
</body>
</html>