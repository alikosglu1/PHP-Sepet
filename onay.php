<?php
session_start();
$SECRETKEY = 'SECRET_KEY'; //Mağaza şifresi

if (isset($_GET['err'])) {
    die('<h3 style="color:red"> Hata Oldu: ' . $_GET['err'] . '</h3>');
}
if (isset($_GET['ctrl'])) {
    $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    $url = str_replace('&ctrl=' . $_GET['ctrl'], '', $url);
    if (hash_hmac("md5", strlen($url) . $url, $SECRETKEY) == $_GET['ctrl']) {
        $row             = unserialize($_SESSION['musteri']);
        $last_id         = null;
        $musteri_id      = $row['id'];
        // shop adlı veritabanına bağlanalım
        $db              = new SQLite3('shop.db');
        $siparis_tablo   = $db->prepare('INSERT INTO siparis(urun_id, adet,tarih) VALUES (:urun_id, :adet, :tarih)');
        $musteri_siparis = $db->prepare('INSERT INTO musteri_siparis(musteri_id, siparis_id) VALUES (?, ?)');
        $musteri_siparis->bindValue(1, $musteri_id);
        $musteri_siparis->bindParam(2, $last_id, SQLITE3_INTEGER);
        
        foreach ($_SESSION['sepet'] as $siparis) {
            $siparis_tablo->bindValue(':urun_id', $siparis['id'], SQLITE3_INTEGER);
            $siparis_tablo->bindValue(':adet', $siparis['adet'], SQLITE3_INTEGER);
            $siparis_tablo->bindValue(':tarih', $tarih, SQLITE3_TEXT);
            //Siparis tablosu sorgusunu çalıştıralım
            $siparis_tablo->execute();
            
            //siparis tablosuna eklenen kaydın id bigisini alalım
            $last_id = $db->lastInsertRowID();
            
            //musteri_siparis tablosunun sorgusunu çalıştıralım
            $musteri_siparis->execute();
            $siparis_tablo->clear();
            $musteri_siparis->reset();
        }
        $siparis_tablo->close();
        $musteri_siparis->close();
        $db->close();
        unset($_SESSION['sepet'], $_SESSION['musteri']);
        echo '<h2>Ödemeniz alınmıştır. Teşekkür ederiz</h2>';
    } else {
        die('hash_hmac hesaplamada hata var. İşlem gerçekleşmedi.');
    }
}
?>