<?php
// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=peminjaman');
    exit;
}

// ===== PROSES FORM (pinjam & kembalikan) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    if ($aksi === 'pinjam_buku') {
        $id_buku = $_POST['ID_Buku'];
        $id_anggota = $_POST['ID_Anggota'];
        $stmt = mysqli_prepare($koneksi, "CALL SP_PinjamBuku(?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $id_buku, $id_anggota);
        if (mysqli_stmt_execute($stmt)) {
            pesan('Peminjaman berhasil diproses menggunakan SP_PinjamBuku.');
        } else {
            pesan('Peminjaman gagal: ' . mysqli_stmt_error($stmt), 'gagal');
        }
        mysqli_stmt_close($stmt);
        bersihkanHasilCall($koneksi);
        header("Location: index.php?halaman=peminjaman");
        exit;
    }

    if ($aksi === 'kembalikan_buku') {
        $id_pinjam = $_POST['ID_Peminjaman'];
        $stmt = mysqli_prepare($koneksi, "CALL SP_KembalikanBuku(?)");
        mysqli_stmt_bind_param($stmt, "s", $id_pinjam);
        mysqli_stmt_execute($stmt);
        $stmt->bind_result($col,$col2);
        $result = $stmt->fetch();
        if ($col2 == 1) {
            pesan('Pengembalian berhasil diproses menggunakan SP_KembalikanBuku.');
        } else {
            pesan($col, 'gagal');
        }
        mysqli_stmt_close($stmt);
        bersihkanHasilCall($koneksi);
        header("Location: index.php?halaman=peminjaman");
        exit;
    }
}
?>
<?php
$daftar_buku = jalankan($koneksi, "SELECT ID_Buku, Judul, Jumlah_Stok FROM buku WHERE Jumlah_Stok > 0 ORDER BY Judul");
$daftar_anggota = jalankan($koneksi, "SELECT ID_Anggota, Nama_Anggota FROM anggota ORDER BY Nama_Anggota");
$daftar_pinjam = jalankan($koneksi, "SELECT p.*, b.Judul, a.Nama_Anggota FROM peminjaman p JOIN buku b ON p.ID_Buku=b.ID_Buku JOIN anggota a ON p.ID_Anggota=a.ID_Anggota ORDER BY p.Tanggal_Peminjaman DESC, p.ID_Peminjaman DESC");
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">TRANSAKSI</p>
        <h1>Peminjaman & Pengembalian</h1>
        <p>Proses transaksi menggunakan Stored Procedure database.</p>
    </div>
</div>
<div class="two-column transaction-columns">
    <div class="panel form-panel">
        <div class="panel-heading">
            <div>
                <h2>Pinjam Buku</h2>
                <p>Proses menggunakan SP_PinjamBuku</p>
            </div><span class="mini-tag">PROCEDURE</span>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="pinjam_buku">
            <label>Pilih Buku</label><select name="ID_Buku" required>
                <option value="">-- Pilih Buku --</option><?php while ($b = mysqli_fetch_assoc($daftar_buku)): ?>
                    <option value="<?= aman($b['ID_Buku']) ?>"><?= aman($b['ID_Buku']) ?> - <?= aman($b['Judul']) ?> (Stok:
                        <?= aman($b['Jumlah_Stok']) ?>)</option><?php endwhile; ?>
            </select>
            <label>Pilih Anggota</label><select name="ID_Anggota" required>
                <option value="">-- Pilih Anggota --</option><?php while ($a = mysqli_fetch_assoc($daftar_anggota)): ?>
                    <option value="<?= aman($a['ID_Anggota']) ?>"><?= aman($a['ID_Anggota']) ?> -
                        <?= aman($a['Nama_Anggota']) ?></option><?php endwhile; ?>
            </select>
            <div class="info-box">Tanggal peminjaman otomatis menggunakan tanggal saat ini. Jatuh tempo ditetapkan 7
                hari setelah peminjaman.</div>
            <button class="btn-primary" type="submit">Proses Peminjaman</button>
        </form>
    </div>
    <div class="panel form-panel">
        <div class="panel-heading">
            <div>
                <h2>Pengembalian Buku</h2>
                <p>Proses menggunakan SP_KembalikanBuku</p>
            </div><span class="mini-tag">PROCEDURE</span>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="kembalikan_buku">
            <label>Pilih Peminjaman Aktif</label><select name="ID_Peminjaman" required>
                <option value="">-- Pilih ID Peminjaman --</option><?php
                $aktif_pinjam = jalankan($koneksi, "SELECT ID_Peminjaman, ID_Buku, Status_Peminjaman FROM peminjaman WHERE Status_Peminjaman IN ('Dipinjam','Terlambat') ORDER BY ID_Peminjaman DESC");
                while ($p = mysqli_fetch_assoc($aktif_pinjam)): ?>
                    <option value="<?= aman($p['ID_Peminjaman']) ?>"><?= aman($p['ID_Peminjaman']) ?> -
                        <?= aman($p['ID_Buku']) ?> (<?= aman($p['Status_Peminjaman']) ?>)</option><?php endwhile; ?>
            </select>
            <div class="info-box">Stok buku akan bertambah satu setelah pengembalian berhasil.</div>
            <button class="btn-primary btn-green" type="submit">Proses Pengembalian</button>
        </form>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div>
            <h2>Riwayat Peminjaman</h2>
            <p>Seluruh transaksi peminjaman buku</p>
        </div>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID Peminjaman</th>
                    <th>Buku</th>
                    <th>Anggota</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($daftar_pinjam) > 0):
                    while ($p = mysqli_fetch_assoc($daftar_pinjam)): ?>
                        <tr>
                            <td><?= aman($p['ID_Peminjaman']) ?></td>
                            <td><?= aman($p['Judul']) ?></td>
                            <td><?= aman($p['Nama_Anggota']) ?></td>
                            <td><?= aman($p['Tanggal_Peminjaman']) ?></td>
                            <td><?= aman($p['Tanggal_Jatuh_Tempo']) ?></td>
                            <td><?= aman($p['Tanggal_Pengembalian'] ?? '-') ?></td>
                            <td><span
                                    class="status <?= strtolower($p['Status_Peminjaman']) ?>"><?= aman($p['Status_Peminjaman']) ?></span>
                            </td>
                        </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="7" class="empty">Belum ada transaksi.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>