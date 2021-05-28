<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Sipariş Listesi </title>
  <meta charset="utf-8">
  <style>
  body{font: normal 13px/20px  "Times New Roman", Arial, Verdana;}
  </style>
 </head>
 <body>
<?php
$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)  die('Bağlantı Hatası:' . $db->connect_error);
/* Tablo veri karakter yapısı */
$db->set_charset("utf8");
$sql = $db->query("SELECT * FROM siparis") or die($db->error);
if($sql->num_rows < 1) echo '<h3>Siparişiniz Yok</3>';
$table =
'<table width="900" border="1">
  <tr>
   <td>id</td>
   <td>Müşteri_Adı</td>
   <td>Sipariş_Verilen_Ürünlerin_Bilgisi</td>
   <td>Telefon</td>
   <td>Email</td>
   <td>Adres</td>
   <td>PayU Referansı</td>
   <td>Tarih</td>
</tr>';

foreach ($sql->fetch_all() as $row) {
$table .= '<tr>'
  . '<td>' . $row[0] . '</td>'
  . '<td>' . $row[1] . '</td>'
  . '<td>' . $row[2] . '</td>'
  . '<td>' . $row[3] . '</td>'
  . '<td>' . $row[4] . '</td>'
  . '<td>' . $row[5] . '</td>'
  . '<td>' . $row[6] . '</td>'
  . '<td>' . $row[7] . '</td>'
  . '</tr>';
}
$table .='</table>';
echo $table;
$db->close();
?>
</body>
</html>