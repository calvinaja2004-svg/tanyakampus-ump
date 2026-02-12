<?php
require '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $judul = htmlspecialchars(trim($_POST['judul']));
    $isi = htmlspecialchars(trim($_POST['isi']));
    $kategori = htmlspecialchars(trim($_POST['kategori'])) ?? null;
    $user_id = $_SESSION['user_id'];
    
    // Validasi input
    if(empty($judul) || empty($isi)) {
        header('Location: ../edit_question.php?id=' . $question_id . '&error=Judul dan isi pertanyaan tidak boleh kosong');
        exit;
    }
    
    // Cek apakah pertanyaan milik user ini
    $check = $pdo->prepare("SELECT * FROM questions WHERE id = ? AND user_id = ?");
    $check->execute([$question_id, $user_id]);
    
    if($check->rowCount() == 0) {
        header('Location: ../index.php?error=Anda tidak memiliki akses untuk mengedit pertanyaan ini');
        exit;
    }
    
    // Update pertanyaan
    $stmt = $pdo->prepare("UPDATE questions SET judul = ?, isi = ?, kategori = ? WHERE id = ? AND user_id = ?");
    
    try {
        $stmt->execute([$judul, $isi, $kategori, $question_id, $user_id]);
        header('Location: ../answer.php?id=' . $question_id . '&success=edit');
    } catch(PDOException $e) {
        header('Location: ../edit_question.php?id=' . $question_id . '&error=Terjadi kesalahan sistem');
    }
    exit;
}

// Kalau bukan POST request, redirect
header('Location: ../index.php');
exit;
?>
