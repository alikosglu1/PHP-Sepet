<?php
session_start();
$hata = '';
if (isset($_POST['email']) && !empty($_POST['email'])) {
    // shop adlı veritabanına bağlanalım
    $db = new SQLite3('shop.db');
    
    // prepare() metodu ile SQL sorgusunu hazırlayalım
    $stmt = $db->prepare("SELECT * FROM musteri WHERE email=? AND sifre=?") or die($db->lastErrorMsg());
    
    //Dışarıdan gelen değişkenleri güvenli hale getirelim
    $stmt->bindValue(1, $_POST['email'], SQLITE3_TEXT);
    $stmt->bindValue(2, md5($_POST['sifre']), SQLITE3_TEXT);
    
    //Hazırlanan sorguyu çalıştıralım
    $sonuc = $stmt->execute();
    if (is_array($row = $sonuc->fetchArray(SQLITE3_ASSOC))) {
        $_SESSION['musteri']    = serialize($row);
        $_SESSION['musteri_id'] = $row['id'];
        header('location: payu.php');
    } else {
        $hata = 'Eposta yada Şifre hatalı';
    }
}
?>
<h3>Sisteme Giriş Yapın</h3>
<div><?php
echo $hata;
?></div>
<form method="post" action="">
    <input type="email" name="email" /> Eposta<br />
    <input type="password" name="sifre" /> Şifre<br />
    <input type="submit" value="Giriş Yap" />
</form>