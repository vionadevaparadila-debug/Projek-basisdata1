<?php
// =====================================================
// index.php
// Gabungan: index (router + dashboard) + header + navbar + footer
// =====================================================
define('APP_PERPUS', true);
include 'koneksi.php';

function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function jalankan($koneksi, $sql) {
    return mysqli_query($koneksi, $sql);
}

function bersihkanHasilCall($koneksi) {
    while (mysqli_more_results($koneksi)) {
        mysqli_next_result($koneksi);
        $hasil = mysqli_store_result($koneksi);
        if ($hasil) {
            mysqli_free_result($hasil);
        }
    }
}

function pesan($teks, $jenis = "sukses") {
    $_SESSION['pesan'] = $teks;
    $_SESSION['jenis_pesan'] = $jenis;
}

function aktif($nama, $halaman) {
    return $nama === $halaman ? 'aktif' : '';
}

session_start();

// Daftar halaman yang diizinkan => file yang dimuat
$halaman_valid = [
    'dashboard'     => null,               // dashboard ada di index.php ini
    'buku'          => 'buku.php',
    'anggota'       => 'anggota.php',
    'peminjaman'    => 'peminjaman.php',
    'laporan'       => 'laporan.php',
    'buku_populer'  => 'buku_populer.php',
    'anggota_aktif' => 'anggota_aktif.php',
];

$halaman = isset($_GET['halaman']) ? $_GET['halaman'] : 'dashboard';
$halaman_ditemukan = array_key_exists($halaman, $halaman_valid);

$pesan_tampil = isset($_SESSION['pesan']) ? $_SESSION['pesan'] : '';
$jenis_pesan = isset($_SESSION['jenis_pesan']) ? $_SESSION['jenis_pesan'] : 'sukses';
unset($_SESSION['pesan'], $_SESSION['jenis_pesan']);

// ===== KONTEN HALAMAN =====
// Konten ditampung dulu (output buffering) supaya proses form di tiap
// halaman bisa melakukan redirect sebelum ada HTML yang terkirim.
ob_start();
if (!$halaman_ditemukan) {
    echo '<div class="panel"><h2>Halaman tidak ditemukan</h2><a class="text-link" href="index.php">Kembali ke Dashboard</a></div>';
} elseif ($halaman === 'dashboard') {
?>
                <?php
                $total_buku = mysqli_fetch_assoc(jalankan($koneksi, "SELECT COUNT(*) AS total FROM buku"))['total'];
                $total_stok = mysqli_fetch_assoc(jalankan($koneksi, "SELECT COALESCE(SUM(Jumlah_Stok),0) AS total FROM buku"))['total'];
                $total_anggota = mysqli_fetch_assoc(jalankan($koneksi, "SELECT COUNT(*) AS total FROM anggota"))['total'];
                $total_pinjam = mysqli_fetch_assoc(jalankan($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE Status_Peminjaman='Dipinjam'"))['total'];
                $total_terlambat = mysqli_fetch_assoc(jalankan($koneksi, "SELECT COUNT(*) AS total FROM peminjaman WHERE Status_Peminjaman='Terlambat'"))['total'];
                $terbaru = jalankan($koneksi, "SELECT p.*, b.Judul, a.Nama_Anggota FROM peminjaman p JOIN buku b ON p.ID_Buku=b.ID_Buku JOIN anggota a ON p.ID_Anggota=a.ID_Anggota ORDER BY p.Tanggal_Peminjaman DESC LIMIT 5");
                ?>
                <div class="page-heading"><div><p class="eyebrow">RINGKASAN</p><h1>Dashboard</h1><p>Selamat datang di Sistem Manajemen Perpustakaan Literasi Jaya.</p></div><span class="date-chip"><?= date('d-m-Y') ?></span></div>
                <div class="stats">
                    <div class="stat-card"><div class="stat-icon green">▤</div><div><span>Total Judul Buku</span><strong><?= aman($total_buku) ?></strong><small>Jenis koleksi buku</small></div></div>
                    <div class="stat-card"><div class="stat-icon gold">▥</div><div><span>Total Stok Buku</span><strong><?= aman($total_stok) ?></strong><small>Eksemplar tersedia</small></div></div>
                    <div class="stat-card"><div class="stat-icon blue">♙</div><div><span>Total Anggota</span><strong><?= aman($total_anggota) ?></strong><small>Anggota terdaftar</small></div></div>
                    <div class="stat-card"><div class="stat-icon purple">⇄</div><div><span>Sedang Dipinjam</span><strong><?= aman($total_pinjam) ?></strong><small>Transaksi aktif</small></div></div>
                </div>
                <div class="notice-row"><div class="notice"><b><?= aman($total_terlambat) ?></b><span>Peminjaman terlambat</span></div><div class="notice"><b><?= aman($total_stok) ?></b><span>Jumlah stok buku saat ini</span></div></div>
                <div class="panel">
                    <div class="panel-heading"><div><h2>Peminjaman Terbaru</h2><p>Daftar transaksi peminjaman terakhir</p></div><a class="text-link" href="index.php?halaman=peminjaman">Lihat semua →</a></div>
                    <div class="table-wrap"><table><thead><tr><th>ID Peminjaman</th><th>Judul Buku</th><th>Nama Anggota</th><th>Tanggal Pinjam</th><th>Jatuh Tempo</th><th>Status</th></tr></thead><tbody>
                    <?php if (mysqli_num_rows($terbaru) > 0): while ($row = mysqli_fetch_assoc($terbaru)): ?>
                        <tr><td><?= aman($row['ID_Peminjaman']) ?></td><td><?= aman($row['Judul']) ?></td><td><?= aman($row['Nama_Anggota']) ?></td><td><?= aman($row['Tanggal_Peminjaman']) ?></td><td><?= aman($row['Tanggal_Jatuh_Tempo']) ?></td><td><span class="status <?= strtolower($row['Status_Peminjaman']) ?>"><?= aman($row['Status_Peminjaman']) ?></span></td></tr>
                    <?php endwhile; else: ?><tr><td colspan="6" class="empty">Belum ada data peminjaman.</td></tr><?php endif; ?>
                    </tbody></table></div>
                </div>

<?php
} else {
    include $halaman_valid[$halaman];
}
$konten = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="id">
<!-- ================= HEADER ================= -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literasi Jaya - Sistem Perpustakaan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="layout">

    <!-- ================= NAVBAR ================= -->
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-icon">LJ</div>
            <div><strong>Literasi Jaya</strong><small>Manajemen Perpustakaan</small></div>
        </div>
        <div class="menu-label">MENU UTAMA</div>
        <a class="<?= aktif('dashboard', $halaman) ?>" href="index.php"><span>▦</span> Dashboard</a>
        <a class="<?= aktif('buku', $halaman) ?>" href="index.php?halaman=buku"><span>▤</span> Data Buku</a>
        <a class="<?= aktif('anggota', $halaman) ?>" href="index.php?halaman=anggota"><span>♙</span> Data Anggota</a>
        <a class="<?= aktif('peminjaman', $halaman) ?>" href="index.php?halaman=peminjaman"><span>⇄</span> Peminjaman</a>
        <div class="menu-label">LAPORAN & VIEW</div>
        <a class="<?= aktif('laporan', $halaman) ?>" href="index.php?halaman=laporan"><span>▧</span> Laporan</a>
        <a class="<?= aktif('buku_populer', $halaman) ?>" href="index.php?halaman=buku_populer"><span>★</span> Buku Populer</a>
        <a class="<?= aktif('anggota_aktif', $halaman) ?>" href="index.php?halaman=anggota_aktif"><span>♧</span> Anggota Aktif</a>
        <div class="sidebar-bottom">Sistem Informasi Perpustakaan<br><b>Tahun 2026</b></div>
    </aside>

    <main class="main">
        <header class="topbar">
            <div><span class="top-title">Sistem Manajemen Perpustakaan</span><small>Perpustakaan Literasi Jaya · Bangkalan</small></div>
            <div class="admin"><span class="admin-avatar">A</span><span>Administrator<small>Admin Perpustakaan</small></span></div>
        </header>

        <section class="content">
            <?php if ($pesan_tampil !== ''): ?>
                <div class="alert <?= aman($jenis_pesan) ?>"><?= aman($pesan_tampil) ?></div>
            <?php endif; ?>

            <?= $konten ?>

            <!-- ================= FOOTER ================= -->
            <footer class="footer">© 2026 Perpustakaan Literasi Jaya · Sistem Manajemen Perpustakaan</footer>
        </section>
    </main>
</div>
</body>
</html>
