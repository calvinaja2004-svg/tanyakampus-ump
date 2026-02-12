<?php 
require 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Ambil statistik platform dengan data per role
$stats = $pdo->query("
    SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'mahasiswa') as total_mahasiswa,
    (SELECT COUNT(*) FROM users WHERE role = 'dosen') as total_dosen,
    (SELECT COUNT(*) FROM questions) as total_questions,
    (SELECT COUNT(*) FROM answers) as total_answers
")->fetch();

// Ambil semua pertanyaan dengan error handling
try {
    $stmt = $pdo->query("
        SELECT q.*, u.nama, u.email, u.role,
        (SELECT COUNT(*) FROM answers WHERE question_id = q.id) as total_jawaban,
        (SELECT COUNT(*) FROM upvotes WHERE question_id = q.id) as total_upvote
        FROM questions q
        JOIN users u ON q.user_id = u.id
        ORDER BY q.created_at DESC
    ");
    $questions = $stmt->fetchAll();
} catch(PDOException $e) {
    $questions = []; // Set empty array jika error
    error_log("Error fetching questions: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TanyaKampus UMP - Platform Tanya Jawab Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
            --ump-green: #0d9488;
            --ump-dark: #064e3b;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand {
            font-weight: 800;
            font-size: 1.6rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .main-container {
            margin-top: 100px;
            margin-bottom: 50px;
        }
        
        /* Stats Section */
        .stats-section {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--ump-green));
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .stat-card {
            text-align: center;
            padding: 25px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border-radius: 15px;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
        }
        
        .stat-card i {
            font-size: 3rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
            display: block;
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        /* Quick Links UMP Section */
        .ump-links-section {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 15px 40px rgba(99, 102, 241, 0.15);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(99, 102, 241, 0.1);
        }
        
        .ump-links-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .ump-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .ump-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #0d9488, #064e3b);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.8rem;
            font-weight: 800;
            box-shadow: 0 5px 15px rgba(13, 148, 136, 0.3);
        }
        
        .ump-header h3 {
            margin: 0;
            font-weight: 700;
            color: #1f2937;
            font-size: 1.5rem;
        }
        
        .ump-header p {
            margin: 0;
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .quick-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .link-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
            text-decoration: none;
            display: block;
            border: 1px solid #e2e8f0;
        }
        
        .link-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
            border-left-width: 6px;
            border-left-color: #0d9488;
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }
        
        .link-card-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .link-card:hover .link-card-icon {
            background: linear-gradient(135deg, #0d9488, #064e3b);
            transform: scale(1.05);
        }
        
        .link-card-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .link-card-desc {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
            line-height: 1.5;
        }
        
        .link-card-arrow {
            color: var(--primary);
            font-size: 1.2rem;
            float: right;
            margin-top: -25px;
            transition: all 0.3s ease;
        }
        
        .link-card:hover .link-card-arrow {
            color: #0d9488;
            transform: translateX(5px);
        }
        
        /* Welcome Card */
        .welcome-card {
            background: white;
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        
        .welcome-card h2 {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        
        .btn-ask {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 14px 35px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(99, 102, 241, 0.4);
            font-size: 1.05rem;
        }
        
        .btn-ask:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.6);
            color: white;
        }
        
        /* Question Cards */
        .section-title {
            color: white;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .question-card {
            background: white;
            border-radius: 18px;
            padding: 30px;
            margin-bottom: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
            transition: all 0.3s;
            border-left: 5px solid var(--primary);
        }
        
        .question-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.18);
        }
        
        .question-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .question-title a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s;
        }
        
        .question-title a:hover {
            color: var(--primary);
        }
        
        .question-content {
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        .question-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            font-size: 0.95rem;
        }
        
        .badge {
            padding: 6px 14px;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .badge-answers {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }
        
        .badge-upvotes {
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #15803d;
        }
        
        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #e5e7eb;
            margin-bottom: 25px;
        }
        
        .role-badge {
            background: linear-gradient(135deg, #f3e8ff, #e9d5ff);
            color: #7c3aed;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .role-badge.mahasiswa {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e40af;
        }
        
        .role-badge.dosen {
            background: linear-gradient(135deg, #fce7f3, #fbcfe8);
            color: #be185d;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-graduation-cap"></i>
                <span>TanyaKampus UMP</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <span class="nav-link">
                            <i class="fas fa-user-circle"></i> 
                            Halo, <strong><?= htmlspecialchars($_SESSION['nama']) ?></strong>
                            <span class="role-badge <?= $_SESSION['role'] ?? 'mahasiswa' ?> ms-1">
                                <?= ucfirst($_SESSION['role'] ?? 'mahasiswa') ?>
                            </span>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger fw-bold" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container main-container">
        
        <!-- Platform Statistics -->
        <div class="stats-section">
            <h3 class="text-center mb-2" style="font-weight: 700; color: #1f2937;">
                <i class="fas fa-chart-line" style="color: var(--primary);"></i> 
                Statistik Platform TanyaKampus UMP
            </h3>
            <p class="text-center text-muted mb-0">Data pengguna aktif dan aktivitas platform</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <span class="stat-number"><?= $stats['total_users'] ?? 0 ?></span>
                    <div class="stat-label">Total Pengguna</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-user-graduate"></i>
                    <span class="stat-number"><?= $stats['total_mahasiswa'] ?? 0 ?></span>
                    <div class="stat-label">Mahasiswa</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="stat-number"><?= $stats['total_dosen'] ?? 0 ?></span>
                    <div class="stat-label">Dosen</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-question-circle"></i>
                    <span class="stat-number"><?= $stats['total_questions'] ?? 0 ?></span>
                    <div class="stat-label">Pertanyaan</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-comments"></i>
                    <span class="stat-number"><?= $stats['total_answers'] ?? 0 ?></span>
                    <div class="stat-label">Jawaban</div>
                </div>
            </div>
        </div>

        <!-- Quick Links UMP -->
        <div class="ump-links-section">
            <div class="ump-header">
                <div class="ump-logo">
                    <i class="fas fa-university"></i>
                </div>
                <div>
                    <h3><span style="color: #0d9488;">UMP</span> Quick Links</h3>
                    <p>Akses cepat ke layanan kampus Universitas Muhammadiyah Pontianak</p>
                </div>
            </div>
            
            <div class="quick-links-grid">
                <a href="https://krs.unmuhpnk.ac.id/v4/" target="_blank" class="link-card">
                    <div class="link-card-icon">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="link-card-title">KRS Online</div>
                    <p class="link-card-desc">Sistem Kartu Rencana Studi - Daftar Mata Kuliah</p>
                    <i class="fas fa-arrow-right link-card-arrow"></i>
                </a>
                
                <a href="https://library.unmuhpnk.ac.id/" target="_blank" class="link-card">
                    <div class="link-card-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="link-card-title">Perpustakaan</div>
                    <p class="link-card-desc">Digital Library & Katalog Buku UMP</p>
                    <i class="fas fa-arrow-right link-card-arrow"></i>
                </a>
                
                <a href="https://unmuhpnk.ac.id/" target="_blank" class="link-card">
                    <div class="link-card-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div class="link-card-title">Website Resmi UMP</div>
                    <p class="link-card-desc">Informasi & Pengumuman Kampus</p>
                    <i class="fas fa-arrow-right link-card-arrow"></i>
                </a>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-card">
            <?php if(isset($_GET['success'])): ?>
                <?php if($_GET['success'] == 'delete'): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i> Pertanyaan berhasil dihapus!
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-fire"></i> Selamat Datang di TanyaKampus UMP!</h2>
                    <p class="text-muted mb-0" style="font-size: 1.05rem;">
                        Platform tanya jawab khusus mahasiswa Universitas Muhammadiyah Pontianak. 
                        <?php if($_SESSION['role'] == 'dosen'): ?>
                            Sebagai dosen, Anda dapat membantu menjawab pertanyaan mahasiswa.
                        <?php else: ?>
                            Tanya apa saja seputar kampus, kuliah, atau akademik!
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="post_question.php" class="btn btn-ask">
                        <i class="fas fa-plus-circle"></i> Ajukan Pertanyaan
                    </a>
                </div>
            </div>
        </div>

        <!-- Questions List -->
        <div class="row">
            <div class="col-12">
                <h4 class="section-title">
                    <i class="fas fa-comments"></i> 
                    Pertanyaan Terbaru
                </h4>
                
                <?php if(is_array($questions) && count($questions) > 0): ?>
                    <?php foreach($questions as $q): ?>
                        <div class="question-card">
                            <div class="question-title">
                                <a href="answer.php?id=<?= $q['id'] ?>">
                                    <?= htmlspecialchars($q['judul']) ?>
                                </a>
                            </div>
                            <div class="question-content">
                                <?= nl2br(htmlspecialchars(substr($q['isi'], 0, 200))) ?>
                                <?= strlen($q['isi']) > 200 ? '...' : '' ?>
                            </div>
                            <div class="question-meta">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar">
                                        <?= strtoupper(substr($q['nama'], 0, 1)) ?>
                                    </div>
                                    <span>
                                        <strong><?= htmlspecialchars($q['nama']) ?></strong>
                                        <span class="role-badge <?= $q['role'] ?? 'mahasiswa' ?> ms-1">
                                            <?= ucfirst($q['role'] ?? 'mahasiswa') ?>
                                        </span>
                                    </span>
                                </div>
                                <span>
                                    <i class="far fa-clock"></i> 
                                    <?= date('d M Y, H:i', strtotime($q['created_at'])) ?>
                                </span>
                                <span class="badge badge-answers">
                                    <i class="fas fa-comment"></i> <?= $q['total_jawaban'] ?? 0 ?> Jawaban
                                </span>
                                <span class="badge badge-upvotes">
                                    <i class="fas fa-arrow-up"></i> <?= $q['total_upvote'] ?? 0 ?> Upvote
                                </span>
                                <?php if($q['kategori']): ?>
                                    <span class="badge" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;">
                                        <i class="fas fa-tag"></i> <?= htmlspecialchars($q['kategori']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h4>Belum Ada Pertanyaan</h4>
                        <p class="text-muted">Jadilah yang pertama bertanya dan mulai diskusi!</p>
                        <a href="post_question.php" class="btn btn-ask mt-3">
                            <i class="fas fa-plus-circle"></i> Buat Pertanyaan Pertama
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>