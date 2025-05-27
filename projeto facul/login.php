<?php
session_start();
include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $senha = trim($_POST['password']);
    $role = $_POST['role'];

    if ($role === "aluno") {
        $sql = "SELECT * FROM alunos WHERE email = '$username' AND senha = '$senha'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $_SESSION['usuario'] = $username;
            $_SESSION['role'] = 'aluno';
            header("Location: aluno.php");
            exit();
        } else {
            echo "<script>alert('Usuário ou senha de aluno incorretos.'); window.location.href='index.html';</script>";
        }
    } elseif ($role === "docente") {
        $sql = "SELECT * FROM professores WHERE email = '$username' AND senha = '$senha'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) == 1) {
            $_SESSION['usuario'] = $username;
            $_SESSION['role'] = 'docente';
            header("Location: professor.php");
            exit();
        } else {
            echo "<script>alert('Usuário ou senha de docente incorretos.'); window.location.href='index.html';</script>";
        }
    }
}
?>
