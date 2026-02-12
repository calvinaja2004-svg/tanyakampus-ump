<?php
require '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah pertanyaan milik user ini
    $check = $pdo->prepare("SELECT * FROM questions WHERE id = ? AND user_id = ?");
    $check->execute([$question_id, $user_id]);
    
    if($check->rowCount() == 0) {
        header('Location: ../index.php?error=Anda tidak memiliki akses untuk menghapus pertanyaan ini');
        exit;
    }
    
    // Hapus pertanyaan (ON DELETE CASCADE akan otomatis hapus jawaban dan upvote)
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND user_id = ?");
    
    try {
        $stmt->execute([$question_id, $user_id]);
        header('Location: ../index.php?success=delete');
    } catch(PDOException $e) {
        header('Location: ../answer.php?id=' . $question_id . '&error=Terjadi kesalahan sistem');
    }
    exit;
}

// Kalau bukan POST request, redirect
header('Location: ../index.php');
exit;
?>
