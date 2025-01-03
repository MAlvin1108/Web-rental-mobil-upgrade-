<?php
date_default_timezone_set('Asia/Jakarta');
$now = date("Y-m-d H:i:s");
/**
 * Database connection setup
 */
$connection = new Mysqli("localhost", "root", "rapi", "mobil");

if ($connection->connect_error) {
    die("Koneksi database gagal: " . $connection->connect_error);
}

/**
 * Page initialize
 */
if (isset($_GET["page"])) {
  $_PAGE = $_GET["page"];
  $_ADMINPAGE = $_GET["page"];
} else {
  $_PAGE = "home";
  $_ADMINPAGE = "home";
}
/**
 * Page setup
 * @param page
 * @return page filename
 */
function page($page) {
  return "pelanggan/" . $page . ".php";
}
/**
 * Page setup
 * @param page
 * @return page filename
 */
function adminPage($page) {
  return "page/" . $page . ".php";
}

/**
 * Alert notification
 * @param message, redirection
 * @return alert notify
 */
function alert($msg, $to = null) {
  $to = ($to) ? $to : $_SERVER["PHP_SELF"];
  return "<script>alert('{$msg}');window.location='{$to}';</script>";
}

// Update otomatis
$query = $connection->query("SELECT a.id_mobil, a.id_transaksi, (DATEDIFF(NOW(), a.tgl_ambil)) AS tgl FROM transaksi a WHERE a.status='0'");
while ($data = $query->fetch_assoc()) {
  if ($data["tgl"] >= 0) {
    $connection->query("UPDATE mobil SET status='0' WHERE id_mobil=$data[id_mobil]");
    $q = $connection->query("SELECT id_supir FROM detail_transaksi WHERE id_transaksi=$data[id_transaksi]");
    if ($q->num_rows) {
      $connection->query("UPDATE supir SET status='0' WHERE id_supir=$data[id_supir]");
    }
  }
}

// Pembatalan otomatis
$query = $connection->query("SELECT a.jatuh_tempo, a.id_transaksi, a.id_mobil, (TIMESTAMPDIFF(HOUR, a.tgl_sewa, NOW())) AS tempo FROM transaksi a WHERE a.konfirmasi='0'");
while ($data = $query->fetch_assoc()) {
  if ($data["tempo"] > 3) {
    $connection->query("UPDATE transaksi SET pembatalan='1' WHERE id_transaksi=$data[id_transaksi]");
    $connection->query("UPDATE mobil SET status='1' WHERE id_mobil=$data[id_mobil]");
    $q = $connection->query("SELECT id_supir FROM detail_transaksi WHERE id_transaksi=$data[id_transaksi]");
    if ($q->num_rows) {
      $id = $query->fetch_assoc();
      @$connection->query("UPDATE supir SET status='1' WHERE id_supir=".$id["id_supir"]);
      @$connection->query("DELETE FROM detail_transaksi WHERE id_transaksi=$data[id_transaksi]");
    }
  }
}

// Perhitungan denda otomatis CONTOH : ADDDATE (INTERVAL 1 HOUR)
$sql = "SELECT
          a.id_transaksi,
          (
            TIMESTAMPDIFF(
              HOUR,
              ADDDATE(a.tgl_ambil, INTERVAL a.lama DAY),
              a.tgl_kembali
            )
          ) AS terlambat,
          (0.1 * (TIMESTAMPDIFF(HOUR, ADDDATE(a.tgl_ambil, INTERVAL a.lama DAY), a.tgl_kembali)) * a.total_harga) AS denda
        FROM transaksi a
        WHERE a.tgl_kembali <> ''";
$query = $connection->query($sql);
while ($a = $query->fetch_assoc()) {
  if ($a["denda"] > 0) {
      if (!$connection->query("UPDATE transaksi SET denda=$a[denda] WHERE id_transaksi=$a[id_transaksi]")) {
        die("Hitung denda otomatis gagal.");
      }
  }
}

