<?php
session_start();
if (!isset($_SESSION['musteri'])) {
    header('location: musteri.php');
    exit();
}
if (!isset($_SESSION['sepet']) || empty($_SESSION['sepet'])) {
    header('location: sqlite_sepet.php');
    exit();
}
$INSTALLMENT_OPTIONS = 0; //Taksit için "3,6,12" gibi birtane değer girin
$MERCHANT            = 'OPU_TEST'; //Mağaza kodu
$SECRETKEY           = 'SECRET_KEY'; //Mağaza şifresi
$test                = 1; //Satış için 0, test için 1
echo '<!DOCTYPE html>
<html lang="tr">
 <head>
  <title> Satın Alma </title>
  <meta charset="utf-8">
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
</head>
<body>
<table border=1>
<tr>
  <td>Ürün Adı:</td> <td> Ürün Adeti</td> <td>Ürün Fiyatı</td>                    
</tr>';
$toplam = 0;
$kargo  = 0;
$form   = '';
foreach ($_SESSION['sepet'] as $id => $dizi) {
    $net_fiyat = $dizi['fiyat'] * $dizi['adet'];
    $net_kdv   = $net_fiyat * (@$dizi['kdv'] / 100);
    $kargo  += $dizi['kargo'] ? $dizi['kargo'] : 0;
    $toplam += number_format(($net_fiyat + $net_kdv), 2, '.', '');
    echo "<tr>
           <td>{$dizi['ad']}</td>
           <td>{$dizi['adet']} Adet</td>
           <td>{$dizi['fiyat']} TL + {$net_kdv} + KDV  </td>           
        </tr>";
    $form .= '
  <input type="hidden" name="ORDER_PNAME[]" value="' . $dizi['ad'] . '" >
  <input type="hidden" name="ORDER_QTY[]" value="' . $dizi['adet'] . '">     
  <input type="hidden" name="ORDER_PRICE[]" value="' . $dizi['fiyat'] . '">
  <input type="hidden" name="ORDER_PCODE[]" value="' . $dizi['id'] . '">
  <input type="hidden" name="ORDER_VAT[]" value="' . $dizi['kdv'] . '">';
    $ORDER_PNAME[] = $dizi['ad'];
    $ORDER_QTY[]   = $dizi['adet'];
    $ORDER_PRICE[] = $dizi['fiyat'];
    $ORDER_PCODE[] = $dizi['id'];
    $ORDER_VAT[]   = $dizi['kdv'];
}
//hash_hmac() hesaplamsı için gerekli bilgileri düzenleyelim
$order           = uniqid();
$PRICES_CURRENCY = "TRY";
$ORDER_DATE      = date("Y-m-d H:i:s");
$ORDER_SHIPPING  = $kargo;
//Sipariş onay adresi
$url             = pathinfo('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
$form .= '
<input type="hidden" name="ORDER_SHIPPING" value="' . $kargo . '" />
<input type="hidden" name="INSTALLMENT_OPTIONS" value="' . $INSTALLMENT_OPTIONS . '">
<input type="hidden" name="PRICES_CURRENCY" value="TRY">
<input type="hidden" name="LANGUAGE" value="TR">
<input type="hidden" name="ORDER_ID" value="' . $order . '" />
<input type="hidden" name="BACK_REF" value="' . $url['dirname'] . '/onay.php?order=' . $order . '">
<input type="hidden" name="MERCHANT" value="' . $MERCHANT . '">
<input type="hidden" name="ORDER_DATE" value="' . $ORDER_DATE . '">
<input type="hidden" name="TESTORDER" value="' . $test . '">
<input type="hidden" name="DEBUG" value="' . $test . '">
<input type="hidden" name="BILL_COUNTRYCODE" value="TR">
<input type="hidden" name="AUTOMODE" value="1">';

$vars    = array(
    'MERCHANT',
    'ORDER_REF',
    'ORDER_DATE',
    'ORDER_PNAME',
    'ORDER_PCODE',
    'ORDER_PINFO',
    'ORDER_PRICE',
    'ORDER_QTY',
    'ORDER_VAT',
    'ORDER_SHIPPING',
    'PRICES_CURRENCY',
    'DISCOUNT',
    'DESTINATION_CITY',
    'DESTINATION_STATE',
    'DESTINATION_COUNTRY',
    'PAY_METHOD',
    'ORDER_PRICE_TYPE',
    'INSTALLMENT_OPTIONS'
);
$Hstring = '';
foreach ($vars as $key => $val) {
    if (isset($$val) && is_array($$val)) {
        foreach ($$val as $key2 => $val2) {
            $Hstring .= (strlen($val2) > 0) ? strlen($val2) . $val2 : '0';
        }
    } else {
        if (isset($$val))
            $Hstring .= (strlen($$val) > 0) ? strlen($$val) . $$val : '0';
    }
}
$hash_hmac = hash_hmac("md5", $Hstring, $SECRETKEY);
extract(unserialize($_SESSION['musteri']));
?>
<tr>
<td colspan="3" align="right">KDV Dahil Toplam : <?php
echo $toplam;
?> TL </td>
</tr></table>
<form method="post" action="https://secure.payu.com.tr/order/lu.php">
<h3>Kişisel Bilgiler</h3>
Ad Soyad: <?php
echo $ad . ' ' . $soyad;
?><br />
Eposta: <?php
echo $email;
?><br />
Adres: <?php
echo $adres;
?><br />
<input name="BILL_FNAME" type="hidden" value="<?php
echo $ad;
?>">
<input name="BILL_LNAME" type="hidden" value="<?php
echo $soyad;
?>">
<input name="BILL_EMAIL" type="hidden" value="<?php
echo $email;
?>">
<input name="BILL_ADDRESS" type="hidden" value="<?php
echo $adres;
?>">
<input name="BILL_STATE" type="text" required> Şehir<br />
<input name="BILL_CITY" type="text" required> İlçe veya Semt<br />
<input name="BILL_ZIPCODE" type="text" size="5"> Posta kodu<br />
<input name="BILL_PHONE" type="text" required>Cep Tel<br />
<?php
echo $form;
?>
<input type="hidden" name="ORDER_HASH" value="<?php
echo $hash_hmac;
?>">
<input type="submit"  value="SATIN AL" />
</form>
<script>
$('form').submit(function(event) {
var parent = document.getElementsByTagName('form')[0],
inputs = parent.getElementsByTagName("input");
for (var i=0; i < inputs.length; i++){
inputs[i].style.border="2px solid green";
if (inputs[i].getAttribute("type") == "email"){
  if(!inputs[i].value.match(/^[\w\-\.\+]+\@[a-zA-Z0-9\.\-]+\.[a-zA-z0-9]{2,4}$/)){
     inputs[i].style.border = "1px solid red";     
     return false;
  }
}
if (inputs[i].getAttribute("required") == ""){
  if(inputs[i].value.replace(/^\s+|\s+$/g, "") == ""){
     inputs[i].style.border="2px solid red";
    $(inputs[i]).show().fadeOut();
    $(inputs[i]).show().fadeIn();
       return false;
    }
  }
 }
});
</script>
</body>
</html>