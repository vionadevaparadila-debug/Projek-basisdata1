<?php
// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=anggota_aktif');
    exit;
}
?>
<?php $data = jalankan($koneksi, "SELECT * FROM v_anggotaaktif ORDER BY Jumlah_Peminjaman DESC"); ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">VIEW DATABASE</p>
        <h1>Anggota Aktif</h1>
        <p>Anggota yang pernah meminjam dan tidak memiliki peminjaman berstatus terlambat.</p>
    </div><span class="mini-tag">V_AnggotaAktif</span>
</div>
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>ID Anggota</th>
                    <th>Nama Anggota</th>
                    <th>Jumlah Peminjaman</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                if (mysqli_num_rows($data) > 0):
                    while ($r = mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= aman($r['ID_Anggota']) ?></td>
                            <td><b><?= aman($r['Nama_Anggota']) ?></b></td>
                            <td><span class="count-pill"><?= aman($r['Jumlah_Peminjaman']) ?> kali</span></td>
                        </tr><?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="empty">Belum ada anggota yang memenuhi kriteria aktif.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="footnote">Data diambil langsung dari View <code>v_anggotaaktif</code>.</p>
</div>