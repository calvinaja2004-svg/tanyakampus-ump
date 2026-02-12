<?php
require '../config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_id = (int)$_POST['question_id'];
    $jawaban = htmlspecialchars($_POST['jawaban']);
    $user_id = $_SESSION['user_id'];
    
    $stmt = $pdo->prepare("INSERT INTO answers (question_id, user_id, jawaban) VALUES (?, ?, ?)");
    
    try {
        $stmt->execute([$question_id, $user_id, $jawaban]);
        header('Location: ../answer.php?id=' . $question_id . '&success=1');
    } catch(PDOException $e) {
        header('Location: ../answer.php?id=' . $question_id . '&error=1');
    }
    exit;
}
?>
