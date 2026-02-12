<?php require 'config/database.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - TanyaKampus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-card {
            background: white;
            border-radius: 25px;
            padding: 50px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px; /* Diperbesar sedikit */
            width: 100%;
        }
        
        .auth-card h2 {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .auth-subtitle {
            text-align: center;
            color: #6b7280;
            margin-bottom: 30px;
        }
        
        .form-control, .form-select {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            border-radius: 50px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo i {
            font-size: 4rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .role-option {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option:hover {
            border-color: #6366f1;
            background-color: #f8fafc;
        }
        
        .role-option.selected {
            border-color: #6366f1;
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
        }
        
        .role-icon {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        
        .student-icon {
            color: #3b82f6;
        }
        
        .lecturer-icon {
            color: #8b5cf6;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="logo">
            <i class="fas fa-graduation-cap"></i>
        </div>
        <h2>Daftar TanyaKampus</h2>
        <p class="auth-subtitle">Bergabung dengan komunitas akademik</p>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= $_GET['error'] ?>
            </div>
        <?php endif; ?>
        
        <form action="process/auth.php?action=register" method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user"></i> Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope"></i> Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@kampus.ac.id" required>
            </div>
            
            <!-- Pilihan Role -->
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user-tag"></i> Pilih Peran Anda</label>
                <input type="hidden" name="role" id="selectedRole" required>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="role-option text-center" onclick="selectRole('mahasiswa')" id="mahasiswaOption">
                            <div class="role-icon student-icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                            <h5 class="mb-1">Mahasiswa</h5>
                            <p class="text-muted small mb-0">Bertanya & berdiskusi</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="role-option text-center" onclick="selectRole('dosen')" id="dosenOption">
                            <div class="role-icon lecturer-icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                            <h5 class="mb-1">Dosen</h5>
                            <p class="text-muted small mb-0">Membimbing & menjawab</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-id-card"></i> NIM/NIP</label>
                <input type="text" name="nim" class="form-control" 
                       placeholder="Masukkan NIM (mahasiswa) atau NIP (dosen)" required>
                <small class="text-muted">*Wajib diisi untuk verifikasi</small>
            </div>
            
            <div class="mb-4">
                <label class="form-label"><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" class="form-control" 
                       placeholder="Minimal 6 karakter" required minlength="6">
            </div>
            
            <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <p class="text-center mt-4">
            Sudah punya akun? <a href="login.php" style="color: #6366f1; font-weight: 600;">Login di sini</a>
        </p>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            
            // Reset semua opsi
            document.getElementById('mahasiswaOption').classList.remove('selected');
            document.getElementById('dosenOption').classList.remove('selected');
            
            // Aktifkan opsi yang dipilih
            document.getElementById(role + 'Option').classList.add('selected');
            
            // Ubah placeholder NIM/NIP
            const nimField = document.querySelector('input[name="nim"]');
            if (role === 'mahasiswa') {
                nimField.placeholder = "Masukkan NIM (contoh: 2020123456)";
            } else {
                nimField.placeholder = "Masukkan NIP (contoh: 198012345678)";
            }
            
            // Aktifkan tombol submit
            document.getElementById('submitBtn').disabled = false;
        }
        
        // Pilih mahasiswa secara default
        document.addEventListener('DOMContentLoaded', function() {
            selectRole('mahasiswa');
        });
    </script>
</body>
</html>