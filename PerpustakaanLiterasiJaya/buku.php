<?php
// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=buku');
    exit;
}

// ===== PROSES FORM (tambah, edit, hapus) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'tambah_buku' || $aksi === 'edit_buku') {
        $id = trim($_POST['ID_Buku']);
        $judul = trim($_POST['Judul']);
        $penulis = trim($_POST['Penulis']);
        $penerbit = trim($_POST['Penerbit']);
        $tahun = ($_POST['Tahun_Terbit'] === '') ? null : (int) $_POST['Tahun_Terbit'];
        $isbn = trim($_POST['ISBN']);
        $stok = (int) $_POST['Jumlah_Stok'];
        $kategori = trim($_POST['Kategori']);

        if ($aksi === 'tambah_buku') {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO buku (ID_Buku, Judul, Penulis, Penerbit, Tahun_Terbit, ISBN, Jumlah_Stok, Kategori) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssisis", $id, $judul, $penulis, $penerbit, $tahun, $isbn, $stok, $kategori);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE buku SET Judul=?, Penulis=?, Penerbit=?, Tahun_Terbit=?, ISBN=?, Jumlah_Stok=?, Kategori=? WHERE ID_Buku=?");
            mysqli_stmt_bind_param($stmt, "sssisiss", $judul, $penulis, $penerbit, $tahun, $isbn, $stok, $kategori, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            pesan($aksi === 'tambah_buku' ? 'Data buku berhasil ditambahkan.' : 'Data buku berhasil diperbarui.');
        } else {
            pesan('Data buku gagal disimpan: ' . mysqli_stmt_error($stmt), 'gagal');
        }
        mysqli_stmt_close($stmt);
        header("Location: index.php?halaman=buku");
        exit;
    }

    if ($aksi === 'hapus_buku') {
        $id = $_POST['ID_Buku'];
        $stmt = mysqli_prepare($koneksi, "DELETE FROM buku WHERE ID_Buku=?");
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (mysqli_stmt_execute($stmt)) {
            pesan('Data buku berhasil dihapus.');
        } else {
            pesan('Buku tidak dapat dihapus karena masih memiliki riwayat peminjaman atau terjadi kesalahan.', 'gagal');
        }
        mysqli_stmt_close($stmt);
        header("Location: index.php?halaman=buku");
        exit;
    }
}

$edit_buku = null;
if ($halaman === 'buku' && isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $q = jalankan($koneksi, "SELECT * FROM buku WHERE ID_Buku='$id_edit'");
    $edit_buku = mysqli_fetch_assoc($q);
}
?>
<?php
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$cari_sql = mysqli_real_escape_string($koneksi, $cari);
$data_buku = jalankan($koneksi, "SELECT * FROM buku WHERE Judul LIKE '%$cari_sql%' OR ID_Buku LIKE '%$cari_sql%' OR Kategori LIKE '%$cari_sql%' ORDER BY ID_Buku");
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Data Buku</h1>
        <p>Kelola koleksi buku perpustakaan.</p>
    </div>
</div>
<div class="two-column">
    <div class="panel form-panel">
        <div class="panel-heading">
            <div>
                <h2><?= $edit_buku ? 'Edit Buku' : 'Tambah Buku' ?></h2>
                <p>Lengkapi informasi buku</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $edit_buku ? 'edit_buku' : 'tambah_buku' ?>">
            <label>ID Buku</label><input name="ID_Buku" maxlength="10" required
                value="<?= aman($edit_buku['ID_Buku'] ?? '') ?>" <?= $edit_buku ? 'readonly' : '' ?>>
            <label>Judul Buku</label><input name="Judul" required value="<?= aman($edit_buku['Judul'] ?? '') ?>">
            <label>Penulis</label><input name="Penulis" required value="<?= aman($edit_buku['Penulis'] ?? '') ?>">
            <label>Penerbit</label><input name="Penerbit" required value="<?= aman($edit_buku['Penerbit'] ?? '') ?>">
            <div class="form-grid">
                <div><label>Tahun Terbit</label><input type="number" name="Tahun_Terbit"
                        value="<?= aman($edit_buku['Tahun_Terbit'] ?? '') ?>"></div>
                <div><label>Jumlah Stok</label><input type="number" name="Jumlah_Stok" min="0" required
                        value="<?= aman($edit_buku['Jumlah_Stok'] ?? '0') ?>"></div>
            </div>
            <label>ISBN</label><input name="ISBN" value="<?= aman($edit_buku['ISBN'] ?? '') ?>">
            <label>Kategori</label><input name="Kategori" required value="<?= aman($edit_buku['Kategori'] ?? '') ?>">
            <button class="btn-primary" type="submit"><?= $edit_buku ? 'Simpan Perubahan' : 'Tambah Buku' ?></button>
            <?php if ($edit_buku): ?><a class="btn-light full-btn" href="index.php?halaman=buku">Batal
                    Edit</a><?php endif; ?>
        </form>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h2>Daftar Buku</h2>
                <p>Data koleksi yang tersimpan</p>
            </div>
        </div>
        <form class="search-form" method="GET"><input type="hidden" name="halaman" value="buku"><input name="cari"
                placeholder="Cari judul, ID, kategori..." value="<?= aman($cari) ?>"><button
                class="btn-secondary">Cari</button></form>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Stok</th>
                        <th>Kategori</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($data_buku) > 0):
                        while ($row = mysqli_fetch_assoc($data_buku)): ?>
                            <tr>
                                <td><?= aman($row['ID_Buku']) ?></td>
                                <td><b><?= aman($row['Judul']) ?></b><small
                                        class="sub-cell"><?= aman($row['Penerbit']) ?></small></td>
                                <td><?= aman($row['Penulis']) ?></td>
                                <td><?= aman($row['Jumlah_Stok']) ?></td>
                                <td><?= aman($row['Kategori']) ?></td>
                                <td class="actions"><a class="action-edit"
                                        href="index.php?halaman=buku&edit=<?= urlencode($row['ID_Buku']) ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus buku ini?')"><input
                                            type="hidden" name="aksi" value="hapus_buku"><input type="hidden" name="ID_Buku"
                                            value="<?= aman($row['ID_Buku']) ?>"><button class="action-delete">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="empty">Data buku tidak ditemukan.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>