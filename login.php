<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['nama']) || isset($_SESSION['user_id'])) {
    header("Location: tabel_buku.php");
    exit;
}

$error = '';

if (isset($_POST['login'])) {
    $input_nama = preg_replace('/^[\s\p{Z}\p{C}]+|[\s\p{Z}\p{C}]+$/u', '', $_POST['nama'] ?? '');
    $input_pass = $_POST['pass'] ?? '';

    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id_user, nama, email, pass, role, kelas FROM tb_user"
    );
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $user_ditemukan = null;
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $nama_db = preg_replace('/^[\s\p{Z}\p{C}]+|[\s\p{Z}\p{C}]+$/u', '', $row['nama'] ?? '');
            if (mb_strtolower($nama_db, 'UTF-8') === mb_strtolower($input_nama, 'UTF-8')) {
                $user_ditemukan = $row;
                break;
            }
        }
    }
    mysqli_stmt_close($stmt);

    if ($user_ditemukan !== null) {
        $pass_db = (string)($user_ditemukan['pass'] ?? '');
        if ($input_pass === $pass_db || password_verify($input_pass, $pass_db)) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user_ditemukan['id_user'];
            $_SESSION['nama']    = $user_ditemukan['nama'];
            $_SESSION['email']   = $user_ditemukan['email'] ?? '';
            $_SESSION['role']    = strtolower($user_ditemukan['role'] ?? 'anggota');
            $_SESSION['kelas']   = $user_ditemukan['kelas'] ?? '';

            header("Location: tabel_buku.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Nama tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Baru - Sistem Informasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #f4f7f6;
        }
        .login-container {
            max-width: 1050px;
            width: 100%;
            background: #ffffff;
            border-radius: 1.25rem;
            overflow: hidden;
        }
        .left-banner {
            padding: 0;
            overflow: hidden;
            background-color: #eaf2ff;
        }
        .left-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center p-3">

    <div class="row login-container shadow-lg border-0 g-0">
        <div class="col-lg-6 left-banner d-none d-lg-block">
            <img src="sipn.png" alt="Banner Login" onerror="this.style.display='none';">
        </div>

        <div class="col-lg-6 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
            <div class="text-center mb-4">
                <h4 class="fw-bold text-dark">Hai, selamat datang kembali</h4>
                <p class="text-muted small">Belum punya akun? <a href="registasi.php" class="text-decoration-none">Daftar akun</a></p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger py-2 text-center small" role="alert">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" autocomplete="off">
                <div class="mb-3">
                    <label for="nama" class="form-label text-muted small fw-semibold">Nama</label>
                    <input type="text" name="nama" id="nama" class="form-control py-2" required placeholder="Masukkan nama">
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <label for="pass" class="form-label text-muted small fw-semibold">Password</label>
                        <a href="#" class="small text-decoration-none text-primary">Lupa kata sandi?</a>
                    </div>
                    <input type="password" name="pass" id="pass" class="form-control py-2" required placeholder="Masukkan password">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label text-muted small" for="remember">Ingat perangkat ini</label>
                </div>

                <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">Masuk</button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted" style="font-size: 0.75rem;">
                    Dengan melanjutkan, kamu menerima <a href="#" class="text-decoration-none">Syarat Penggunaan</a> dan <a href="#" class="text-decoration-none">Kebijakan Privasi</a> kami.
                </small>
            </div>
        </div>
    </div>

</body>
</html>