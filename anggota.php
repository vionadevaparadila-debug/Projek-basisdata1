<?php

// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=anggota');
    exit;
}

// ===== PROSES FORM (tambah, edit, hapus) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'tambah_anggota' || $aksi === 'edit_anggota') {
        $id = trim($_POST['ID_Anggota']);
        $nama = trim($_POST['Nama_Anggota']);
        $alamat = trim($_POST['Alamat']);
        $telepon = trim($_POST['No_Telepon']);
        $email = trim($_POST['Email']);
        $tanggal = $_POST['Tanggal_Daftar'];

        if ($aksi === 'tambah_anggota') {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO anggota (ID_Anggota, Nama_Anggota, Alamat, No_Telepon, Email, Tanggal_Daftar) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $id, $nama, $alamat, $telepon, $email, $tanggal);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE anggota SET Nama_Anggota=?, Alamat=?, No_Telepon=?, Email=?, Tanggal_Daftar=? WHERE ID_Anggota=?");
            mysqli_stmt_bind_param($stmt, "ssssss", $nama, $alamat, $telepon, $email, $tanggal, $id);
        }

        if (mysqli_stmt_execute($stmt)) {
            pesan($aksi === 'tambah_anggota' ? 'Data anggota berhasil ditambahkan.' : 'Data anggota berhasil diperbarui.');
        } else {
            pesan('Data anggota gagal disimpan: ' . mysqli_stmt_error($stmt), 'gagal');
        }
        mysqli_stmt_close($stmt);
        header("Location: index.php?halaman=anggota");
        exit;
    }

    if ($aksi === 'hapus_anggota') {
        $id = $_POST['ID_Anggota'];
        $stmt = mysqli_prepare($koneksi, "DELETE FROM anggota WHERE ID_Anggota=?");
        mysqli_stmt_bind_param($stmt, "s", $id);
        if (mysqli_stmt_execute($stmt)) {
            pesan('Data anggota berhasil dihapus.');
        } else {
            pesan('Anggota tidak dapat dihapus karena masih memiliki riwayat peminjaman.', 'gagal');
        }
        mysqli_stmt_close($stmt);
        header("Location: index.php?halaman=anggota");
        exit;
    }
}

$edit_anggota = null;
if ($halaman === 'anggota' && isset($_GET['edit'])) {
    $id_edit = mysqli_real_escape_string($koneksi, $_GET['edit']);
    $q = jalankan($koneksi, "SELECT * FROM anggota WHERE ID_Anggota='$id_edit'");
    $edit_anggota = mysqli_fetch_assoc($q);
}
?>
<?php
$cari = isset($_GET['cari']) ? trim($_GET['cari']) : '';
$cari_sql = mysqli_real_escape_string($koneksi, $cari);
$data_anggota = jalankan($koneksi, "SELECT * FROM anggota WHERE Nama_Anggota LIKE '%$cari_sql%' OR ID_Anggota LIKE '%$cari_sql%' ORDER BY ID_Anggota");
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">MASTER DATA</p>
        <h1>Data Anggota</h1>
        <p>Kelola informasi anggota perpustakaan.</p>
    </div>
</div>
<div class="two-column">
    <div class="panel form-panel">
        <div class="panel-heading">
            <div>
                <h2><?= $edit_anggota ? 'Edit Anggota' : 'Tambah Anggota' ?></h2>
                <p>Lengkapi informasi anggota</p>
            </div>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="<?= $edit_anggota ? 'edit_anggota' : 'tambah_anggota' ?>">
            <label>ID Anggota</label><input name="ID_Anggota" maxlength="10" required
                value="<?= aman($edit_anggota['ID_Anggota'] ?? '') ?>" <?= $edit_anggota ? 'readonly' : '' ?>>
            <label>Nama Anggota</label><input name="Nama_Anggota" required
                value="<?= aman($edit_anggota['Nama_Anggota'] ?? '') ?>">
            <label>Alamat</label><textarea name="Alamat" required><?= aman($edit_anggota['Alamat'] ?? '') ?></textarea>
            <label>Nomor Telepon</label><input name="No_Telepon" required
                value="<?= aman($edit_anggota['No_Telepon'] ?? '') ?>">
            <label>Email</label><input type="email" name="Email" value="<?= aman($edit_anggota['Email'] ?? '') ?>">
            <label>Tanggal Daftar</label><input type="date" name="Tanggal_Daftar" required
                value="<?= aman($edit_anggota['Tanggal_Daftar'] ?? date('Y-m-d')) ?>">
            <button class="btn-primary"
                type="submit"><?= $edit_anggota ? 'Simpan Perubahan' : 'Tambah Anggota' ?></button>
            <?php if ($edit_anggota): ?><a class="btn-light full-btn" href="index.php?halaman=anggota">Batal
                    Edit</a><?php endif; ?>
        </form>
    </div>
    <div class="panel">
        <div class="panel-heading">
            <div>
                <h2>Daftar Anggota</h2>
                <p>Anggota yang terdaftar</p>
            </div>
        </div>
        <form class="search-form" method="GET"><input type="hidden" name="halaman" value="anggota"><input name="cari"
                placeholder="Cari nama atau ID anggota..." value="<?= aman($cari) ?>"><button
                class="btn-secondary">Cari</button></form>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($data_anggota) > 0):
                        while ($row = mysqli_fetch_assoc($data_anggota)): ?>
                            <tr>
                                <td><?= aman($row['ID_Anggota']) ?></td>
                                <td><b><?= aman($row['Nama_Anggota']) ?></b><small
                                        class="sub-cell"><?= aman($row['Alamat']) ?></small></td>
                                <td><?= aman($row['No_Telepon']) ?></td>
                                <td><?= aman($row['Email']) ?></td>
                                <td><?= aman($row['Tanggal_Daftar']) ?></td>
                                <td class="actions"><a class="action-edit"
                                        href="index.php?halaman=anggota&edit=<?= urlencode($row['ID_Anggota']) ?>">Edit</a>
                                    <form method="POST" onsubmit="return confirm('Yakin ingin menghapus anggota ini?')"><input
                                            type="hidden" name="aksi" value="hapus_anggota"><input type="hidden"
                                            name="ID_Anggota" value="<?= aman($row['ID_Anggota']) ?>"><button
                                            class="action-delete">Hapus</button></form>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="6" class="empty">Data anggota tidak ditemukan.</td>
                        </tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>