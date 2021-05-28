<?php
session_start();
if (!isset($_SESSION['sepet']))
    die('Hatali istek');
$url       = "https://secure.payu.com.tr/order/alu.php";
$secretKey = 'SECRET_KEY';
$veri      = array(
    "MERCHANT" => "OPU_TEST",
    "ORDER_REF" => rand(1000, 9999),
    "ORDER_DATE" => gmdate('Y-m-d H:i:s')
);

$urun    = array();
$siparis = '';
$i = 0;
foreach ($_SESSION['sepet'] as $id => $v) {
    $net_fiyat = $v['fiyat'] * $v['adet'];
    $net_kdv   = $net_fiyat * (@$v['kdv'] / 100);
    $urun += array(
        "ORDER_PNAME[$i]" => $v['urun'],
        "ORDER_PCODE[$i]" => $v['id'],
        "ORDER_PINFO[$i]" => $v['urun'],
        "ORDER_PRICE[$i]" => $net_fiyat + $net_kdv,
        "ORDER_QTY[$i]" => $v['adet']
    );
    $toplam = $net_fiyat + $net_kdv;
    $siparis .= "<b>{$v['urun']}</b> Adet:{$v['adet']}, Fiyat: <i>{$v['fiyat']}</i> + {$net_kdv} KDV, Toplam: {$toplam}<br />";
	$i++;
}
$_SESSION['form'] = $_POST['ad'] . ' ' . $_POST['soyad'] . '*' . $siparis . '*' . $_POST['email'] . '*' . $_POST['tel'] . '*' . $_POST['adres'];
$hesap            = array(
    "PRICES_CURRENCY" => "TRY",
    "PAY_METHOD" => "CCVISAMC",
    "SELECTED_INSTALLMENTS_NUMBER" => $_POST['taksit'],
    "CC_NUMBER" => $_POST['cart'],
    "EXP_MONTH" => $_POST['mon'],
    "EXP_YEAR" => $_POST['year'],
    "CC_CVV" => $_POST['cvv'],
    "CC_OWNER" => $_POST['ad'] . ' ' . $_POST['soyad'],
    
    "BACK_REF" => "http://localhost/3ds_return.php",
    "CLIENT_IP" => $_SERVER['REMOTE_ADDR'],
    "BILL_LNAME" => $_POST['soyad'],
    "BILL_FNAME" => $_POST['ad'],
    "BILL_EMAIL" => $_POST['email'],
    "BILL_PHONE" => $_POST['tel'],
    "BILL_COUNTRYCODE" => "TR"
);

$arParams = $veri + $urun + $hesap;
//HASH hesaplamaya başla
ksort($arParams);
$hashString = "";
foreach ($arParams as $key => $val) {
    $hashString .= strlen($val) . $val;
}
$arParams["ORDER_HASH"] = hash_hmac("md5", $hashString, $secretKey);
//HASH hesaplama son

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arParams));
$response = curl_exec($ch);

$curlerrcode = curl_errno($ch);
$curlerr     = curl_error($ch);

if (empty($curlerr) && empty($curlerrcode)) {
    $parsedXML = @simplexml_load_string($response);
    if ($parsedXML !== FALSE) {
        $referans = $parsedXML->REFNO;
        if ($parsedXML->STATUS == "SUCCESS") {
            if (($parsedXML->RETURN_CODE == "3DS_ENROLLED") && (!empty($parsedXML->URL_3DS))) {
                header("Location:" . $parsedXML->URL_3DS);
                die();
            }
            /*** Sipariş kaydı ***/
            $db = @new mysqli('localhost', 'root', '1234', 'uygulama');
            if ($db->connect_errno)
                die('Bağlantı Hatası:' . $db->connect_error);
            /* Tablo veri karakter yapısı */
            $db->set_charset("utf8");
            list($ad, $siparis, $tel, $email, $adres) = explode('*', $_SESSION['form']);
            $sql = $db->prepare('INSERT INTO siparis VALUES(NULL,?,?,?,?,?, ?,NULL)');
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
            $mes = $parsedXML->RETURN_MESSAGE;
            if ($parsedXML->RETURN_CODE == 'ORDER_TOO_OLD') {
                echo '<b>Bu sipariş zaten var. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'INVALID_PAYMENT_INFO') {
                echo '<b>Geçersiz Kart Numarası. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'INVALID_PAYMENT_METHOD_CODE') {
                echo '<b>Bu hesap için geçersiz ödeme yöntemi: CCVISAMC </b>';
                echo $mes;
            } else if ($parsedXML->RETURN_CODE == 'AUTHORIZATION_FAILED') {
                echo '<b>Yetkilendirme Red edildi. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'INVALID_CUSTOMER_INFO') {
                echo '<b>Hatalı Müşteri bilgisi. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'INVALID_ACCOUNT') {
                echo '<b>Geçersiz hesap. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'REQUEST_EXPIRED') {
                echo '<b>İstek zaman aşımına uğradı. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'INVALID_CURRENCY') {
                echo '<b>Hatalı para birimi. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'HASH_MISMATCH') {
                echo '<b>HASH hesaplamda hata var. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'GWERROR_-9') {
                echo '<b>Kartın son kullanım tarihi geçmiş </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'GWERROR_14') {
                echo '<b>Böyle bir kredi kartı yok </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'GWERROR_34') {
                echo '<b>Kredi kartı çalıntıdır. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'GWERROR_54') {
                echo '<b>Kart kullanılmıyor. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == 'GWERROR_84') {
                echo '<b>CVV2 hatalı. </b>' . $mes;
            } else if ($parsedXML->RETURN_CODE == '10101') {
                echo '<b>Satıcı taksitli ödeme izni vermemiş.
               <br>Alıcı tek çekimde ödemeyi denemelidir. </b>';
            } else {
                echo $parsedXML->RETURN_CODE . ' : ' . $mes;
            }
            
            if (!empty($referans)) {
                echo "<br> [PayU referans numarası: " . $referans . "]";
            }
        }
    }
} else {
    echo "cURL error: " . $curlerr;
}
?>