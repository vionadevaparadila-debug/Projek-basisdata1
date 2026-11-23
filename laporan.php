<?php
// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=laporan');
    exit;
}
?>
<?php
$belum = jalankan($koneksi, "SELECT b.Judul AS Judul_Buku, a.Nama_Anggota, p.Tanggal_Peminjaman, p.Tanggal_Jatuh_Tempo FROM peminjaman p JOIN buku b ON p.ID_Buku=b.ID_Buku JOIN anggota a ON p.ID_Anggota=a.ID_Anggota WHERE p.Status_Peminjaman='Dipinjam' ORDER BY p.Tanggal_Jatuh_Tempo");
$terlambat = jalankan($koneksi, "SELECT * FROM v_peminjamanterlambat ORDER BY Tanggal_Jatuh_Tempo");
?>
<div class="page-heading">
    <div>
        <p class="eyebrow">LAPORAN</p>
        <h1>Laporan Perpustakaan</h1>
        <p>Informasi peminjaman yang belum kembali dan terlambat.</p>
    </div>
</div>
<div class="panel">
    <div class="panel-heading">
        <div>
            <h2>Buku Belum Dikembalikan</h2>
            <p>Data peminjaman dengan status Dipinjam</p>
        </div><span class="mini-tag">SP_LaporanBukuBelumKembali</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Judul Buku</th>
                    <th>Nama Anggota</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($belum) > 0):
                    while ($r = mysqli_fetch_assoc($belum)): ?>
                        <tr>
                            <td><?= aman($r['Judul_Buku']) ?></td>
                            <td><?= aman($r['Nama_Anggota']) ?></td>
                            <td><?= aman($r['Tanggal_Peminjaman']) ?></td>
                            <td><?= aman($r['Tanggal_Jatuh_Tempo']) ?></td>
                        </tr><?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="empty">Tidak ada peminjaman yang masih berstatus Dipinjam.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="footnote">Untuk menjalankan Stored Procedure laporan secara langsung di phpMyAdmin, gunakan:
        <code>CALL SP_LaporanBukuBelumKembali();</code></p>
</div>
<div class="panel">
    <div class="panel-heading">
        <div>
            <h2>Peminjaman Terlambat</h2>
            <p>Data diambil dari View V_PeminjamanTerlambat</p>
        </div><span class="mini-tag">VIEW</span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Judul Buku</th>
                    <th>Nama Anggota</th>
                    <th>Tanggal Pinjam</th>
                    <th>Jatuh Tempo</th>
                    <th>Tanggal Kembali</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($terlambat) > 0):
                    while ($r = mysqli_fetch_assoc($terlambat)): ?>
                        <tr>
                            <td><?= aman($r['Judul_Buku']) ?></td>
                            <td><?= aman($r['Nama_Anggota']) ?></td>
                            <td><?= aman($r['Tanggal_Peminjaman']) ?></td>
                            <td><?= aman($r['Tanggal_Jatuh_Tempo']) ?></td>
                            <td><?= aman($r['Tanggal_Pengembalian'] ?? '-') ?></td>
                        </tr><?php endwhile; else: ?>
                    <tr>
                        <td colspan="5" class="empty">Tidak ada data peminjaman terlambat.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>