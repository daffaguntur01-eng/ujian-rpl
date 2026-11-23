<?php
session_start();
include 'koneksi.php';

// Lindungi halaman: jika belum login, arahkan ke halaman login
if (!isset($_SESSION['nama']) && !isset($_SESSION['user_id'])) {
    header("Location: login_baru.php");
    exit;
}

$nama_user = $_SESSION['nama'] ?? 'Pengguna';
$role_user = $_SESSION['role'] ?? 'anggota';

$keyword = trim($_GET['cari'] ?? '');

if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    // Mencocokkan kolom umum pada tabel buku (judul, pengarang, penerbit, tahun, stok, dll.)
    $cols_res = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_buku");
    $where_parts = [];
    $params = [];
    $types = '';

    if ($cols_res) {
        while ($c = mysqli_fetch_assoc($cols_res)) {
            $field = $c['Field'];
            $where_parts[] = "`" . mysqli_real_escape_string($koneksi, $field) . "` LIKE ?";
            $params[] = $like;
            $types .= 's';
        }
    }

    if (!empty($where_parts)) {
        $sql = "SELECT * FROM tb_buku WHERE " . implode(" OR ", $where_parts);
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($koneksi, "SELECT * FROM tb_buku");
    }
} else {
    $result = mysqli_query($koneksi, "SELECT * FROM tb_buku");
}

$rows = [];
$columns = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (empty($columns)) {
            $columns = array_keys($row);
        }
        $rows[] = $row;
    }
}

// Jika tabel kosong, ambil daftar nama kolom dari struktur tabel
if (empty($columns)) {
    $cols_res = mysqli_query($koneksi, "SHOW COLUMNS FROM tb_buku");
    if ($cols_res) {
        while ($c = mysqli_fetch_assoc($cols_res)) {
            $columns[] = $c['Field'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabel Buku - Sistem Informasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background-color: #f4f7f6;
        }
        .card-main {
            border-radius: 1rem;
            border: none;
        }
        .table thead th {
            background-color: #0d6efd;
            color: #ffffff;
            white-space: nowrap;
        }
    </style>
</head>
<body class="py-4 px-3">

    <div class="container">
        <div class="card card-main shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Data Buku</h4>
                        <p class="text-muted small mb-0">
                            Login sebagai: <strong><?= htmlspecialchars($nama_user); ?></strong>
                            <span class="badge bg-secondary ms-1"><?= htmlspecialchars($role_user); ?></span>
                        </p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="logout.php" class="btn btn-outline-danger btn-sm px-3">Logout</a>
                    </div>
                </div>

                <form method="GET" action="" class="row g-2 mb-4">
                    <div class="col-sm-9 col-md-10">
                        <input
                            type="text"
                            name="cari"
                            class="form-control"
                            placeholder="Cari data buku..."
                            value="<?= htmlspecialchars($keyword); ?>"
                        >
                    </div>
                    <div class="col-sm-3 col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary fw-semibold">Cari</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 60px;">No</th>
                                <?php foreach ($columns as $col): ?>
                                    <th><?= htmlspecialchars(ucwords(str_replace('_', ' ', $col))); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($rows)): ?>
                                <?php $no = 1; foreach ($rows as $row): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <?php foreach ($columns as $col): ?>
                                            <td><?= htmlspecialchars((string)($row[$col] ?? '')); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= count($columns) + 1; ?>" class="text-center text-muted py-4">
                                        <?= $keyword !== '' ? 'Data buku yang dicari tidak ditemukan.' : 'Belum ada data di tabel buku.'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</body>
</html>