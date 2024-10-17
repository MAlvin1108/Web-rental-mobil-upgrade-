<?php
if (isset($_GET["action"])) {
    $now = date("Y-m-d H:i:s"); // Mengubah format waktu menjadi format yang benar
    $sql = "UPDATE transaksi";
    if ($_GET["action"] == "ambil") {
        $sql .= " SET tgl_ambil=?";
    } elseif ($_GET["action"] == "kembali") {
        // Menggunakan prepared statement untuk menghindari SQL injection
        $query = $connection->prepare("SELECT * FROM transaksi JOIN detail_transaksi USING(id_transaksi) WHERE id_transaksi=?");
        $query->bind_param("i", $_GET['key']);
        $query->execute();
        $result = $query->get_result();
        $r = $result->fetch_assoc();
        $sql .= " SET tgl_kembali=?, status='1'";
        // Persiapkan query SQL dengan prepared statement
        $sql_update_mobil = "UPDATE mobil SET status = '1' WHERE id_mobil = ?";
        // Siapkan statement
        $stmt = $connection->prepare($sql_update_mobil);
        if ($stmt) {
            // Bind parameter ke placeholder dalam statement
            $stmt->bind_param("i", $r["id_mobil"]);
            // Eksekusi statement
            if ($stmt->execute()) {
                echo "Status mobil berhasil diperbarui.";
            } else {
                echo "Error dalam mengeksekusi pernyataan SQL: " . $stmt->error;
            }
            // Tutup statement
            $stmt->close();
        } else {
            echo "Error dalam menyiapkan pernyataan SQL: " . $connection->error;
        }
    }
    $sql .= " WHERE id_transaksi=?";
    // Persiapkan query SQL dengan prepared statement
    $stmt = $connection->prepare($sql);
    if ($stmt) {
        if ($_GET["action"] == "ambil" || $_GET["action"] == "kembali") {
            $stmt->bind_param("si", $now, $_GET['key']);
        } else {
            $stmt->bind_param("i", $_GET['key']);
        }
        // Eksekusi statement
        if ($stmt->execute()) {
            echo alert("Berhasil", "?page=lap_perperiode");
        } else {
            echo "Error dalam mengeksekusi pernyataan SQL: " . $stmt->error;
        }
        // Tutup statement
        $stmt->close();
    } else {
        echo "Error dalam menyiapkan pernyataan SQL: " . $connection->error;
    }
}
?>
<form class="form-inline hidden-print" action="<?=$_SERVER["REQUEST_URI"]?>" method="post">
	<label>Periode</label>
	<input type="text" class="form-control" name="start">
	<label>s/d</label>
	<input type="text" class="form-control" name="stop">
	<button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
</form>
<br>
<?php if ($_POST): ?>
	<div class="panel panel-info">
		<div class="panel-heading"><h3 class="text-center">LAPORAN PENYEWAAN PERPERIODE</h3><br><h4 class="text-center"><?=$_POST["start"]." s/d ".$_POST["stop"]?></h4></div>
		<div class="panel-body">
				<table class="table table-condensed">
						<thead>
								<tr>
										<th>No</th>
										<th>Nama Pelanggan</th>
										<th>Nama Mobil</th>
										<th>Nomor Mobil</th>
										<th>Tanggal Sewa</th>
										<th>Tanggal Ambil</th>
										<th>Tanggal Kembali</th>
										<th>Lama Sewa</th>
										<th>Total Harga</th>
										<th class="hidden-print"></th>
								</tr>
						</thead>
						<tbody>
								<?php $no = 1; ?>
								<?php if ($query = $connection->query("SELECT * FROM transaksi t JOIN mobil m USING(id_mobil) JOIN pelanggan p ON t.id_pelanggan=p.id_pelanggan WHERE t.tgl_sewa BETWEEN '$_POST[start]' AND '$_POST[stop]'")): ?>
										<?php while($row = $query->fetch_assoc()): ?>
										<tr>
												<td><?=$no++?></td>
												<td><?=$row['nama']?></td>
												<td><?=$row['nama_mobil']?></td>
												<td><?=$row['no_mobil']?></td>
												<td><?=date("d-m-Y H:i:s", strtotime($row['tgl_sewa']))?></td>
												<td><?=($row['tgl_ambil']) ? date("d-m-Y H:i:s", strtotime($row['tgl_ambil'])) : "<b>Belum Diambil</b>" ?></td>
												<td><?=($row['tgl_kembali']) ? date("d-m-Y H:i:s", strtotime($row['tgl_kembali'])) : "<b>Belum Dikembalikan</b>" ?></td>
												<td><?=$row['lama']?> Hari</td>
												<td>Rp.<?=number_format($row['total_harga'])?>,-</td>
												<td class="hidden-print">
														<div class="btn-group">
															<?php //if (($row["konfirmasi"] == 1) AND ($row["tgl_ambil"] == NULL) AND ($row["tgl_kembali"] == NULL)): ?>
																<!-- <a href="?page=lap_perperiode&action=ambil&key=<?//=$row['id_transaksi']?>" class="btn btn-success btn-xs">Ambil</a> -->
															<?php //endif; ?>
															<?php if ($row["konfirmasi"] AND $row["tgl_kembali"] == NULL): ?>
																<a href="?page=lap_perperiode&action=kembali&key=<?=$row['id_transaksi']?>" class="btn btn-primary btn-xs">Dikembalikan</a>
															<?php endif; ?>
														</div>
												</td>
										</tr>
										<?php endwhile ?>
								<?php endif ?>
						</tbody>
				</table>
		</div>
    <div class="panel-footer hidden-print">
        <a onClick="window.print();return false" class="btn btn-primary"><i class="glyphicon glyphicon-print"></i></a>
    </div>
	</div>
<?php endif; ?>
