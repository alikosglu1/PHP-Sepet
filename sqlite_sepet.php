<?php
session_start();
//veritabanına bağlanalım.
$db = new SQLite3('shop.db');

if (!isset($_SESSION['sepet'])) {
    $_SESSION['sepet'] = array();
}
if (isset($_POST['id'])) {
    //dizide $_POST['id'] varsa adet değeri bir arttıralım
    if (is_array($_SESSION['sepet'][$_POST['id']])) {
        $_SESSION['sepet'][$_POST['id']]['adet'] += 1;
    } else {
        //$_POST['id'] ile veritabanından ürün bilgisini alalım
        $urunler = $db->prepare('SELECT * FROM urunler WHERE id =?');
        $urunler->bindValue(1, $_POST['id'], SQLITE3_INTEGER);
        $sonuc = $urunler->execute();
        $row   = $sonuc->fetchArray();
        if (!is_array($row)){
            die('hatalı istek');
		}
        $row['adet'] = 1;
        //ürün bilgisini sepete ekleyelim 
        $_SESSION['sepet'][$_POST['id']] = $row;
    }
    header("location: " . $_SERVER['SCRIPT_NAME']);
}
if (isset($_GET['id'])) {
    //dizide GET ile gelen id varsa id bilgisine ait diziyi silelim
    if (is_array($_SESSION['sepet'][$_GET['id']])){
        unset($_SESSION['sepet'][$_GET['id']]);
	}
    header("location: " . $_SERVER['SCRIPT_NAME']);
}
?>
<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Alış Veriş Sepetim </title>
  <meta charset="utf-8">
<style>
body{margin:0; background: #EFEFEF; font: 12px Arial, Helvetica, sans-serif;}
#urunler { width: 800px; font: 12px Arial, Helvetica, sans-serif; }
.urun {width: 60%; margin-bottom: 10px; overflow: hidden; padding: 10px;
border:1px solid #DDD; border-radius: 5px; box-shadow: 3px 3px 3px #F1F1F1;
}
.urun .resim { float: left;    margin-right: 10px; }
.urun .resim img {  width: 100px; }
.urun .ad {     overflow:hidden; }
.urun .fiyat { float: right;}
.urun .fiyat button{ cursor:pointer }
#sepet{
    font: 11px Arial, Helvetica, sans-serif; border: 1px solid silver;
    border-radius: 5px; padding:10px; box-shadow: 5px 5px 2px #F0F0F0;
   }
#sepet td { padding:5px; border-top: 1px solid #F0F0F0 }
#sepet tr:first-child { background: #F0F0F0; border:0; font-weight: bold }
#ana {background: white; padding: 15px; border: 1px solid #DFDFDF; 
border-radius: 5px; }
#head {padding: 5px;}
#head h1 { font-size: 28px; color: #ff3300;}
#ic { margin-top: 15px;}
#menu { padding: 15px; background: #000; color: white; }
</style>
</head>
 <body>
<div id="menu">Menüler</div>
<div id="head"><h1>Sqlite Sepetim</h1></div>
<div id="ana">
<div id="ic">
<?php
if (count($_SESSION['sepet']) > 0) {
    echo '<table style="position:fixed; top:160px; left:530px" id="sepet">
<tr>
  <td>Ürün Adı</td><td>Ürün Fiyatı</td><td>Adet</td><td></td>
</tr>';
    $toplam = 0;
    foreach ($_SESSION['sepet'] as $id => $dizi) {
        $net_fiyat = $dizi['fiyat'] * $dizi['adet'];
        $net_kdv   = $net_fiyat * (@$dizi['kdv'] / 100);
        $toplam += $net_fiyat + $net_kdv;
        echo "<tr>
           <td>{$dizi['ad']}</td>
           <td>{$dizi['fiyat']} TL</td>
           <td>{$dizi['adet']} Adet</td>
           <td><a href='sqlite_sepet.php?id=$id'>Sil</a></td>
        </tr>";
    }
    echo '
<tr>
  <td>KDV+Toplam : ' . $toplam . ' TL </td>
  <td colspan="3"><a href="payu.php">Satın Al</a></td>
</tr>
</table>';
}
//Veritabanından ürün bilgilerini elde edelim.
echo '<div id="urunler">';
if ($sonuc = $db->query("SELECT * FROM urunler ORDER BY id DESC LIMIT 20")) {
    while ($row = $sonuc->fetchArray()) {
        echo '<div class="urun">' . PHP_EOL;
        echo '<form method="post" action="sqlite_sepet.php">' . PHP_EOL;
        echo '<div class="resim"><img src="' . $row['resim'] . '"></div>' . PHP_EOL;
        echo '<div class="ad"><h3>' . $row['ad'] . '</h3>' . PHP_EOL;
        echo '<div class="detay">' . $row['detay'] . '</div>' . PHP_EOL;
        echo '<div class="fiyat">Fiyat ' . $row['fiyat'] . ' TL <button>At Sepete</button></div>';
        echo '</div>' . PHP_EOL;
        echo '<input type="hidden" name="id" value="' . $row['id'] . '" />';
        echo '</form>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }
    
}
echo '</div>';
$db->close();
?>