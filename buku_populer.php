<?php
// Halaman ini dimuat oleh index.php. Jika dibuka langsung, arahkan ke index.php.
if (!defined('APP_PERPUS')) {
    header('Location: index.php?halaman=buku_populer');
    exit;
}
?>
<?php $data = jalankan($koneksi, "SELECT * FROM v_bukupopuler ORDER BY Jumlah_Peminjaman DESC"); ?>
<div class="page-heading">
    <div>
        <p class="eyebrow">VIEW DATABASE</p>
        <h1>Buku Populer</h1>
        <p>Daftar buku yang telah dipinjam minimal dua kali.</p>
    </div><span class="mini-tag">V_BukuPopuler</span>
</div>
<div class="panel">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Jumlah Peminjaman</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1;
                if (mysqli_num_rows($data) > 0):
                    while ($r = mysqli_fetch_assoc($data)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><b><?= aman($r['Judul']) ?></b></td>
                            <td><?= aman($r['Penulis']) ?></td>
                            <td><span class="count-pill"><?= aman($r['Jumlah_Peminjaman']) ?> kali</span></td>
                        </tr><?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" class="empty">Belum ada buku yang memenuhi kriteria populer.</td>
                    </tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="footnote">Data diambil langsung dari View <code>v_bukupopuler</code>.</p>
</div>