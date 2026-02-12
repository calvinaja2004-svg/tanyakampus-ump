<?php
require '../config/database.php';

// PROSES REGISTER
if(isset($_GET['action']) && $_GET['action'] == 'register') {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $nim = htmlspecialchars($_POST['nim']) ?? null;
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = htmlspecialchars($_POST['role']); // Tambah ini
    
    // Cek email sudah ada atau belum
    $check = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if($check->rowCount() > 0) {
        header('Location: ../register.php?error=Email sudah terdaftar!');
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO users (nama, email, nim, password, role) VALUES (?, ?, ?, ?, ?)");
    
    try {
        $stmt->execute([$nama, $email, $nim, $password, $role]);
        header('Location: ../login.php?success=1');
    } catch(PDOException $e) {
        header('Location: ../register.php?error=Terjadi kesalahan sistem');
    }
    exit;
}

// PROSES LOGIN - Tidak perlu perubahan
if(isset($_GET['action']) && $_GET['action'] == 'login') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role']; // Tambah ini
        header('Location: ../index.php');
    } else {
        header('Location: ../login.php?error=Email atau password salah!');
    }
    exit;
}
?>
