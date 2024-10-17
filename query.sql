-- Query untuk menampilkan denda perhari/pertanggal
SELECT
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
WHERE tgl_kembali <> ''
UNION ALL
-- Query untuk menampilkan denda perjam
SELECT
    a.id_transaksi,
    0.1 * (TIMESTAMPDIFF(HOUR, ADDDATE(a.tgl_ambil, INTERVAL a.lama DAY), a.tgl_kembali)) * a.total_harga) AS denda
FROM transaksi a
WHERE tgl_kembali <> ''
UNION ALL
-- Query untuk menampilkan pelanggan yang membatalkan transaksi
SELECT p.nama, t.pembatalan, t.tgl_sewa, m.nama_mobil 
FROM pelanggan p JOIN transaksi t USING(id_pelanggan) JOIN mobil m USING(id_mobil) 
WHERE pembatalan='0'
UNION ALL
-- Query untuk menampilkan nama supir pelanggan tertentu (4 tabel)
SELECT pelanggan.nama, transaksi.tgl_sewa, detail_transaksi.jasa_supir, supir.nama 
FROM pelanggan JOIN transaksi ON pelanggan.id_pelanggan = transaksi.id_pelanggan 
JOIN detail_transaksi ON detail_transaksi.id_transaksi=transaksi.id_transaksi 
JOIN supir ON detail_transaksi.id_supir = supir.id_supir 
WHERE pelanggan.nama = 'Telolet'

-- dg USING (g pake inisial)
SELECT pelanggan.nama, transaksi.tgl_sewa, detail_transaksi.jasa_supir, supir.nama 
FROM pelanggan  JOIN transaksi USING (id_pelanggan)
JOIN detail_transaksi USING(id_transaksi)
JOIN supir USING (id_supir)
WHERE pelanggan.nama = 'Telolet'

SELECT p.nama, t.tgl_sewa, d.jasa_supir, s.nama 
FROM pelanggan p JOIN transaksi t USING (id_pelanggan) 
JOIN detail_transaksi d USING(id_transaksi) 
JOIN supir s USING (id_supir) WHERE p.nama = 'Telolet' 

