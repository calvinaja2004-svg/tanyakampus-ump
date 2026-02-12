<?php 
require 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$question_id = $_GET['id'] ?? 0;

// AMBIL DETAIL PERTANYAAN
$stmt = $pdo->prepare("
    SELECT q.*, u.nama, u.email, u.role
    FROM questions q
    JOIN users u ON q.user_id = u.id
    WHERE q.id = ?
");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if(!$question) {
    header('Location: index.php');
    exit;
}

// CEK APAKAH USER PEMBUAT PERTANYAAN
$is_owner = ($question['user_id'] == $_SESSION['user_id']);

// AMBIL DATA UPVOTE TERPISAH
$stmt = $pdo->prepare("
    SELECT 
    (SELECT COUNT(*) FROM upvotes WHERE question_id = ?) as total_upvote,
    (SELECT COUNT(*) FROM upvotes WHERE question_id = ? AND user_id = ?) as user_upvoted
");
$stmt->execute([$question_id, $question_id, $_SESSION['user_id']]);
$upvote_data = $stmt->fetch();

// GABUNGKAN DATA
$question['total_upvote'] = $upvote_data['total_upvote'] ?? 0;
$question['user_upvoted'] = $upvote_data['user_upvoted'] ?? 0;

// AMBIL JAWABAN
$stmt = $pdo->prepare("
    SELECT a.*, u.nama, u.email, u.role
    FROM answers a
    JOIN users u ON a.user_id = u.id
    WHERE a.question_id = ?
    ORDER BY a.created_at DESC
");
$stmt->execute([$question_id]);
$answers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($question['judul']) ?> - TanyaKampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --secondary: #8b5cf6;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        
        .content-container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .question-detail {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .question-detail h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 20px;
        }
        
        .question-meta {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 15px 0;
            border-top: 2px solid #f3f4f6;
            border-bottom: 2px solid #f3f4f6;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .question-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #4b5563;
            margin: 30px 0;
        }
        
        .upvote-btn {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 50px;
            padding: 8px 20px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .upvote-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .upvote-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
        }
        
        /* Action Buttons (Edit & Delete) */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f3f4f6;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 130, 246, 0.4);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
        }
        
        .answer-section {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .answer-section h3 {
            font-weight: 700;
            margin-bottom: 25px;
        }
        
        .answer-card {
            background: #f9fafb;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .answer-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .answer-content {
            line-height: 1.7;
            color: #374151;
        }
        
        .answer-form {
            background: #f0fdf4;
            border-radius: 15px;
            padding: 30px;
            border: 2px dashed #86efac;
        }
        
        .btn-submit-answer {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            display: inline-block;
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
    <div class="container content-container">
        <!-- Back Button -->
        <a href="index.php" class="btn-back mb-4">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
        
        <!-- Question Detail -->
        <div class="question-detail">
            <?php if(isset($_GET['success']) && $_GET['success'] == 'edit'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> Pertanyaan berhasil diperbarui!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($question['kategori']): ?>
                <span class="badge bg-secondary mb-3"><?= htmlspecialchars($question['kategori']) ?></span>
            <?php endif; ?>
            
            <h1><?= htmlspecialchars($question['judul']) ?></h1>
            
            <div class="question-meta">
                <div class="user-avatar">
                    <?= strtoupper(substr($question['nama'], 0, 1)) ?>
                </div>
                <div>
                    <strong><?= htmlspecialchars($question['nama']) ?></strong>
                    <span class="role-badge <?= $question['role'] ?? 'mahasiswa' ?> ms-1">
                        <?= ucfirst($question['role'] ?? 'mahasiswa') ?>
                    </span><br>
                    <small class="text-muted">
                        <i class="far fa-clock"></i> 
                        <?= date('d M Y, H:i', strtotime($question['created_at'])) ?>
                    </small>
                </div>
                <div class="ms-auto">
                    <form action="process/upvote.php" method="POST" style="display: inline;">
                        <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                        <button type="submit" class="upvote-btn <?= $question['user_upvoted'] ? 'active' : '' ?>">
                            <i class="fas fa-arrow-up"></i> 
                            <?= $question['total_upvote'] ?> Upvote
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="question-content">
                <?= nl2br(htmlspecialchars($question['isi'])) ?>
            </div>
            
            <!-- Action Buttons (Hanya muncul jika user adalah pembuat pertanyaan) -->
            <?php if($is_owner): ?>
                <div class="action-buttons">
                    <a href="edit_question.php?id=<?= $question['id'] ?>" class="btn-edit">
                        <i class="fas fa-edit"></i> Edit Pertanyaan
                    </a>
                    <button type="button" class="btn-delete" onclick="confirmDelete(<?= $question['id'] ?>)">
                        <i class="fas fa-trash-alt"></i> Hapus Pertanyaan
                    </button>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Answers Section -->
        <div class="answer-section">
            <h3><i class="fas fa-comments"></i> <?= count($answers) ?> Jawaban</h3>
            
            <?php if(count($answers) > 0): ?>
                <?php foreach($answers as $ans): ?>
                    <div class="answer-card">
                        <div class="answer-header">
                            <div class="user-avatar">
                                <?= strtoupper(substr($ans['nama'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($ans['nama']) ?></strong>
                                <span class="role-badge <?= $ans['role'] ?? 'mahasiswa' ?> ms-1">
                                    <?= ucfirst($ans['role'] ?? 'mahasiswa') ?>
                                </span><br>
                                <small class="text-muted">
                                    <i class="far fa-clock"></i> 
                                    <?= date('d M Y, H:i', strtotime($ans['created_at'])) ?>
                                </small>
                            </div>
                        </div>
                        <div class="answer-content">
                            <?= nl2br(htmlspecialchars($ans['jawaban'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-4">
                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                    Belum ada jawaban. Jadilah yang pertama menjawab!
                </p>
            <?php endif; ?>
            
            <!-- Answer Form -->
            <div class="answer-form mt-4">
                <h5 class="mb-3"><i class="fas fa-pen"></i> Tulis Jawaban Anda</h5>
                <form action="process/submit_answer.php" method="POST">
                    <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                    <textarea name="jawaban" class="form-control mb-3" rows="5" 
                              placeholder="Bagikan jawaban atau solusi Anda..." required></textarea>
                    <button type="submit" class="btn-submit-answer">
                        <i class="fas fa-paper-plane"></i> Kirim Jawaban
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(questionId) {
            if(confirm('⚠️ Apakah Anda yakin ingin menghapus pertanyaan ini?\n\nSemua jawaban dan upvote akan ikut terhapus!')) {
                // Buat form dan submit
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'process/delete_question.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question_id';
                input.value = questionId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>