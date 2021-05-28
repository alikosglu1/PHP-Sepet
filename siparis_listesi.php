<!DOCTYPE html>
<html lang="tr">
<meta charset="utf-8">
<body>
<h3>Sipariş Listesi</h3>
<?php
$db = new SQLite3('shop.db');
$sonuc = $db->query("SELECT m.id as musteri_id, m.ad as musteri_ad,m.soyad, u.ad, s.adet,s.tarih, s.iptal FROM musteri as m, urunler as u, siparis as s, musteri_siparis as ms WHERE m.id = ms.musteri_id AND s.id = ms.siparis_id AND u.id = s.urun_id");
echo "<table border=1>
      <tr><td>Musteri id</td><td>Musteri Ad</td><td>Ürün ad</td>
      <td>Adet</td><td>Tarih</td></tr>";
while($row = $sonuc->fetchArray(SQLITE3_ASSOC)){
echo "<tr>
	<td>{$row['musteri_id']}</td>
	<td>{$row['musteri_ad']} {$row['soyad']}</td>
	<td>{$row['ad']}</td>
	<td>{$row['adet']}</td>
	<td>{$row['tarih']}</td>
    </tr>";
}
echo '</table>';
?>
</body>
</html>
