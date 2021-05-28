<?php
session_start();
if (!isset($_SESSION['sepet']))
    die('Hatali istek');
$secretKey = 'SECRET_KEY';
if (!isset($_POST['HASH']) || !empty($_POST['HASH'])) {
    
    //HASH doğrulaması
    $arParams = $_POST;
    unset($arParams['HASH']);
    
    $hashString = "";
    foreach ($arParams as $val) {
        $hashString .= strlen($val) . $val;
    }
    $expectedHash = hash_hmac("md5", $hashString, $secretKey);
    if ($expectedHash != $_POST["HASH"]) {
        die("HATA. Hash doğrulanmadı");
    }
    //HASH doğrulama son     
    //Satın alama işilemi sonucu veritabanına aşağıdakileri kaydedin.
    $referans    = $_POST['REFNO'];
    $odenen      = $_POST['AMOUNT'];
    $para_birimi = $_POST['CURRENCY'];
    $taksit      = $_POST['INSTALLMENTS_NO'];
    
    if ($_POST['STATUS'] == "SUCCESS") {
        /*** Sipariş kaydı ***/
        $db = @new mysqli('localhost', 'root', '1234', 'uygulama');
        if ($db->connect_errno)
            die('Bağlantı Hatası:' . $db->connect_error);
        /* Tablo veri karakter yapısı */
        $db->set_charset("utf8");
        list($ad, $siparis, $tel, $email, $adres) = explode('*', $_SESSION['form']);
        $sql = $db->prepare('INSERT INTO siparis VALUES(NULL,?, ?, ?, ?, ?, ?,NULL) ');
        if ($sql === false)
            die('Sorgu hatası:' . $db->error);
        $sql->bind_param('ssssss', $ad, $siparis, $tel, $email, $adres, $referans);
        $sql->execute();
        $sql->close();
        $db->close();
        //sipariş işlemi bittiğine göre ilgili sessionları yok edelim
        unset($_SESSION['form'], $_SESSION['sepet']);
        echo "Teşekkürler [PayU referans numaranız: " . $referans . "]";
        echo '<br />Ekstrenizde "PayU Ödeme Hizmetleri" olarak görünür.<br />';
        /*** Sipariş kaydı ***/
    } else {
        echo "HATA " . $_POST['RETURN_MESSAGE'] . "[" . $_POST['RETURN_CODE'] . "]";
        echo " [PayU referans numarası: " . $referans . "]";
    }
} else {
    die("HATA. Hash doğrulanmadı");
}
?>