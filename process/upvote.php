<?php
require '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah sudah upvote
    $check = $pdo->prepare("SELECT * FROM upvotes WHERE question_id = ? AND user_id = ?");
    $check->execute([$question_id, $user_id]);
    
    if($check->rowCount() > 0) {
        // Jika sudah upvote, hapus (toggle)
        $stmt = $pdo->prepare("DELETE FROM upvotes WHERE question_id = ? AND user_id = ?");
        $stmt->execute([$question_id, $user_id]);
    } else {
        // Jika belum, tambahkan upvote
        $stmt = $pdo->prepare("INSERT INTO upvotes (question_id, user_id) VALUES (?, ?)");
        $stmt->execute([$question_id, $user_id]);
    }
    
    header('Location: ../answer.php?id=' . $question_id);
    exit;
}
?>
