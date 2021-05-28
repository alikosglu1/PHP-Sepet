<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Ürün Ekleme </title>
  <meta charset="utf-8">
  </head>
<body>
<form action="" method="post" enctype="multipart/form-data">
<input type="text" name="ad" /> Ürün Adı<br />
<input type="file" name="resim" /> Resim<br />
<textarea rows="3" cols="40" name="detay"></textarea> Detay<br />
<input type="text" name="fiyat" /> Fiyat<br />
<input type="text" name="kdv" /> KDV<br />
<input type="text" name="kargo" /> Kargo Ücreti<br />
<input type="text" name="stok" /> Stok Sayısı<br />
<input type="submit" value="Ürünü Kaydet">
</form>
</body>
</html>
<?php
if(!$_POST) exit();
$db = @new mysqli("localhost", "root", "1234", "uygulama");
if($db->connect_errno) die('Bağlantı Hatası:'.$db->connect_error);

/* Tablo veri karakter yapısı ayarlayalım */
$db->set_charset("utf8");

/* Formdan yüklenecek ürün resmini kontrol edelim ve ona göre yükleyelim */
$izin_verilen = array("gif", "pjpeg", "jpeg", "jpg", "png", "x-png");

//TYPE bigisi yani image/gif gibi bilgiyi / işaretinden parçalayalım
$tip = end(explode("/",$_FILES["resim"]["type"]));

//image/gif gibi bilgiyiden gif elde ettik, izin verilenlerin içinde varmı?
if(!in_array($tip, $izin_verilen)) die('Hatalı dosya');

//resim adını düzenleylim. Dosya.gif gibi bilgiden gif elde ediyoruz
//bu bilgiye time() ekleyerek dosya adını 983544665632.gif gibi yapıyoruz
$resim_ad =time().'.'.end(explode(".", $_FILES["resim"]["name"]));

//her şey yolunda olduğuna gore move_upload ile yüklemeyi yapıyoruz
move_uploaded_file($_FILES["resim"]["tmp_name"],'./'.$resim_ad);

/* prepare ile SQL sorgusunu hazırlayalım */
$stmt = $db->prepare("INSERT INTO urunler VALUES(NULL,?,?,?,?,?,?,?)") or die($db->error);

//? için veri tiplerini ve değişkenleri tanımlayalım
$stmt->bind_param('sssssss',$_POST['ad'], $resim_ad, $_POST['detay'], $_POST['fiyat'], $_POST['kdv'], $_POST['kargo'], $_POST['stok']);

/* execute ile sorguyu çalıştıralım */
$stmt->execute();

//Kayıt durumunu bildirelim
echo ($db->sqlstate=="00000") 
             ? $db->affected_rows.' Kayıt Eklendi' 
	      : 'Hata oldu, Kayıt eklenmedi';
// Bağlantıyı sonlandıralım
$db->close();
?>