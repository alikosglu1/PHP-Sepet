<?php
session_start();
$aylar = array();
for ($i = 12; $i > 0; $i--) {
    $aylar[] = array(
        'text' => str_pad($i, 2, "0", STR_PAD_LEFT),
        'value' => str_pad($i, 2, "0", STR_PAD_LEFT)
    );
}
$yil    = date("Y");
$yillar = array();
for ($i = $yil; $i < $yil + 10; $i++) {
    $yillar[] = array(
        'text' => $i,
        'value' => $i
    );
}
?>
<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Alış Veriş Sepetim </title>
  <meta charset="utf-8">
<style>
body{font: normal 13px/20px Arial, Verdana}
label{
 display: inline-block;
  width: 100px;
}
</style>
</head>
 <body>
<h2>Ödeme Bilgileri</h2>
<div>
<form method="post" action="satinal.php">	
Peşin ödeme: <input type="radio" name="taksit" checked value="0" /> 
2 Taksit: <input type="radio" name="taksit" value="2" /><br />
<label>Kart Ad:</label><input name="ad" type="text" /><br />
<label>Kart Soyad:</label><input name="soyad" type="text" /><br />
<label>Eposta:</label><input name="email" type="text" /><br />
<label>Telefon:</label><input name="tel" type="text" /><br />
<label>Kart no:</label><input name="cart" type="text" /><br />
Son kullanım tarihi:
<select name="mon">
  <?php
foreach ($aylar as $ay) {
?>
  <option value="<?= $ay['value']; ?>"><?= $ay['text']; ?>
</option>
<?php
}
?>
</select>
<select name="year">
<?php
foreach ($yillar as $yil) {
?>
  <option value="<?= $yil['value']; ?>"><?= $yil['text']; ?>
</option>
<?php
}
?>
</select>
<br />
<label>cvv2:</label><input name="cvv" type="text" /><br />
<label>Tam Adres:</label><textarea name="adres" rows="3" cols="30"></textarea><br />
<input type="submit" value="Gönder" />
</form>
</div>
</body>
</html>