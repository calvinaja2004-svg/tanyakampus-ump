<?php
require '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = htmlspecialchars($_POST['judul']);
    $isi = htmlspecialchars($_POST['isi']);
    $kategori = htmlspecialchars($_POST['kategori']) ?? null;
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, judul, isi, kategori) VALUES (?, ?, ?, ?)");
    
    try {
        $stmt->execute([$user_id, $judul, $isi, $kategori]);
        header('Location: ../index.php?success=1');
    } catch(PDOException $e) {
        header('Location: ../post_question.php?error=Terjadi kesalahan');
    }
    exit;
}
?>