<?php
session_start();
if(!isset($_SESSION['sepet'])){
   $_SESSION['sepet']=array();
}
if($_POST){
	//dizide POST ile gelen id varsa 
	//id bilgisine ait dizinin adet değeri bir arttırsın
    if(is_array($_SESSION['sepet'][$_POST['id']])){
         $_SESSION['sepet'][$_POST['id']]['adet']+=1;
    }else{
	    //dizide id yoksa diziye POST bilgileri eklensin 
         $_SESSION['sepet'][$_POST['id']]=$_POST;
     }
    header("location: sepet.php");
}

if($_GET){
  //dizide GET ile gelen id varsa id bilgisine ait diziyi silsin
  if(is_array($_SESSION['sepet'][$_GET['id']])) 
     unset($_SESSION['sepet'][$_GET['id']]);
     header("location: sepet.php");
}
?>
<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Alış Veriş Sepetim </title>
  <meta charset="utf-8">
<style>
body{background: #EFEFEF; font: 12px Arial, Helvetica, sans-serif;}
#urunler {
	width: 800px;
	font: 12px Arial, Helvetica, sans-serif;
}
.urun {
	width: 60%;
	margin-bottom: 10px;
	overflow: hidden;	
	padding: 10px;
	border: 1px solid #DDD;
	border-radius: 5px;
	box-shadow: 3px 3px 3px #F1F1F1;
	
}
.urun .resim {
	float: left;
	height: 100px;
	width: 100px;
	margin-right: 10px;
}
.urun .resim img { width: 100px; }
.urun .ad{ overflow:hidden;      }
.ad h3 {
	font-size: 18px;
	margin: 0px;
	padding: 0px;
	color: #707070;
}
.urun .fiyat {
	float: right;
	font-size: 13px;
	font-weight: bold;
	margin-top:10px;
}
#sepet{
	font: 11px Arial, Helvetica, sans-serif;
	border: 1px solid silver;
	border-radius: 5px;
	padding:10px;
	box-shadow: 5px 5px 2px #F0F0F0;
}
#sepet td{ padding:5px; border-top: 1px solid #F0F0F0 }
#sepet tr:first-child{
	background: #F0F0F0;
	border:0;
	font-weight: bold
}
#ana,#head,#menu{  width:70%; margin: 0 auto; text-align:left; }
#ana{
	background: white;
	padding: 15px;
	border: 1px solid #DFDFDF;
	border-radius: 5px;
}
#head{ padding: 5px; }
#head h1{ font-size: 28px; color: #4E4E4E; }
#ic{ margin-top: 15px; }
#menu{
   padding: 15px;
   margin-bottom: 20px;
   background: #4E4E4E;
   color: white;
   font-weight: bold
}
</style>
</head>
 <body>
<div id="head"><h1>Sepetim</h1></div>
<div id="menu">Menüler</div>
<div id="ana">
<div id="ic">
<?php 
if(count($_SESSION['sepet']) > 0){
echo '<table style="position:fixed; top:190px; left:730px" id="sepet">
<tr><td>Ürün Adı</td><td>Ürün Fiyatı</td><td>Adet</td><td></td></tr>';
foreach($_SESSION['sepet'] as $id => $dizi){
	$net_fiyat = $dizi['fiyat'] * $dizi['adet'];
	$net_kdv   = $net_fiyat * (@$dizi['kdv']/100);
	$toplam   += $net_fiyat + $net_kdv;
       echo "<tr>
           <td>{$dizi['urun']}</td>
           <td>{$dizi['fiyat']} TL</td>
           <td>{$dizi['adet']} Adet</td>
           <td><a href='sepet.php?id=$id'>Sil</a></td>
        </tr>";
}
  echo '<tr>
          <td>Toplam : '.$toplam.' TL </td>
          <td colspan="3"><a href="form.php">Satın Al</a></td>
        </tr>';
  echo '</table>';
}
$db = @new mysqli('localhost', 'root', '1234', 'uygulama');
if ($db->connect_errno)  die('Bağlantı Hatası:' . $db->connect_error);
/* Tablo veri karakter yapısı */
$db->set_charset("utf8");
echo '<div id="urunler">';
    if ($sonuc = $db->query("SELECT * FROM urunler")) {
		
     while($row = $sonuc->fetch_object()){
       echo '<div class="urun">'; 
       echo '<form method="post" action="sepet.php">';
	echo '<div class="resim"><img src="'.$row->resim.'"></div>';
       echo '<div class="ad"><h3>'.$row->ad.'</h3>';
       echo '<div class="detay">'.$row->detay.'</div>';
       echo '<div class="fiyat">Fiyat '.$row->fiyat.' TL 
                   <button>At Sepete</button></div>';
       echo '</div>';
       echo '<input type="hidden" name="id" value="'.$row->id.'"/>';
       echo '<input type="hidden" name="adet" value="1"/>';
       echo '<input type="hidden" name="urun" value="'.$row->ad.'"/>';
       echo '<input type="hidden" name="fiyat" value="'.$row->fiyat.'"/>';
       echo '<input type="hidden" name="kdv" value="'.$row->kdv.'"/>';
       echo '</form>';
       echo '</div>'; 
    } 
 }
echo '</div>';
$db->close();
?>
</div>
</div>
 </body>
</html>