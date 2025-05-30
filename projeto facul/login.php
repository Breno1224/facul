<?php
session_start();
include("db.php"); // Sua conexão com o banco

// Definindo a que página redirecionar em caso de sucesso
$redirect_page_aluno = "aluno.php";      // <- Verifique se é este o nome da sua página de aluno
$redirect_page_docente = "professor.php"; // <- Verifique se é este o nome da sua página de professor

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']); // Deve ser o email
    $senha_fornecida = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($username) || empty($senha_fornecida) || empty($role)) {
        echo "<script>alert('Por favor, preencha todos os campos.'); window.location.href='index.html';</script>";
        exit();
    }

    $sql = "";
    if ($role === "aluno") {
        $sql = "SELECT id, nome, email, senha, turma_id FROM alunos WHERE email = ?";
    } elseif ($role === "docente") {
        $sql = "SELECT id, nome, email, senha FROM professores WHERE email = ?";
    } else {
        echo "<script>alert('Papel inválido selecionado.'); window.location.href='index.html';</script>";
        exit();
    }

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // ATENÇÃO: Verificação de senha em texto plano (NÃO SEGURO PARA PRODUÇÃO)
            // Em produção, use password_verify($senha_fornecida, $user['senha'])
            if ($senha_fornecida === $user['senha']) { // << MUDAR PARA password_verify em produção
                $_SESSION['usuario_id'] = $user['id'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_email'] = $user['email'];
                $_SESSION['role'] = $role;

                if ($role === "aluno") {
                    $_SESSION['turma_id'] = $user['turma_id']; // Útil para alunos
                    header("Location: " . $redirect_page_aluno);
                    exit();
                } elseif ($role === "docente") {
                    header("Location: " . $redirect_page_docente);
                    exit();
                }
            } else {
                // Senha incorreta
                $error_msg = "Usuário ou senha incorretos.";
            }
        } else {
            // Usuário não encontrado
            $error_msg = "Usuário ou senha incorretos.";
        }
        mysqli_stmt_close($stmt);
    } else {
        // Erro na preparação da query
        $error_msg = "Erro no sistema de login. Tente novamente mais tarde.";
        // Logar o erro: error_log(mysqli_error($conn));
    }

    echo "<script>alert('".$error_msg."'); window.location.href='index.html';</script>";
    exit();

} else {
    // Se não for POST, redireciona para o index
    header("Location: index.html");
    exit();
}
mysqli_close($conn);
?>