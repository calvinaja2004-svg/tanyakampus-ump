<?php 
require 'config/database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajukan Pertanyaan - TanyaKampus</title>
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
        
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .form-card h2 {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 12px 40px;
            border-radius: 50px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.4);
        }
        
        .btn-back {
            background: #6b7280;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
        }
        
        .character-count {
            font-size: 0.85rem;
            color: #9ca3af;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="container form-container">
        <div class="form-card">
            <h2><i class="fas fa-question-circle"></i> Ajukan Pertanyaan Baru</h2>
            
            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Pertanyaan berhasil diposting!
                </div>
            <?php endif; ?>
            
            <form action="process/submit_question.php" method="POST">
                <div class="mb-4">
                    <label class="form-label">Judul Pertanyaan <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" 
                           placeholder="Contoh: Bagaimana cara daftar KRS online?" 
                           required maxlength="255" id="judulInput">
                    <small class="text-muted">Buat judul yang jelas dan spesifik</small>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="">Pilih Kategori (Opsional)</option>
                        <option value="Akademik">Akademik</option>
                        <option value="Administrasi">Administrasi</option>
                        <option value="Kemahasiswaan">Kemahasiswaan</option>
                        <option value="Fasilitas">Fasilitas</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Detail Pertanyaan <span class="text-danger">*</span></label>
                    <textarea name="isi" class="form-control" rows="8" 
                              placeholder="Jelaskan pertanyaan Anda dengan detail..." 
                              required id="isiInput"></textarea>
                    <div class="character-count">
                        <span id="charCount">0</span> karakter
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-submit">
                        <i class="fas fa-paper-plane"></i> Posting Pertanyaan
                    </button>
                    <a href="index.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Character counter
        const isiInput = document.getElementById('isiInput');
        const charCount = document.getElementById('charCount');
        
        isiInput.addEventListener('input', function() {
            charCount.textContent = this.value.length;
        });
    </script>
</body>
</html>