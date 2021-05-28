<?php
header('Content-type: text/html; charset=utf-8');
$db = new SQLite3('shop.db');
$db->createFunction('md5', 'md5');
$sql = <<<'SQL'
DROP TABLE IF EXISTS urunler;
CREATE TABLE IF NOT EXISTS urunler (
  id    INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  ad    TEXT,
  detay TEXT,
  resim TEXT,
  fiyat REAL CHECK( fiyat > 0 ),
  kdv   INTEGER CHECK( fiyat > 0 ),
  kargo REAL,
  stok  INTEGER
);

INSERT INTO urunler VALUES (NULL,'Televizyon','Güzel lcd tv', 'tv.jpg', '1980.99', '18','90','3');
INSERT INTO urunler VALUES (NULL,'Bilgisayar','Süper performanslı', 'laptop.jpg', '1250.50', '18', '30','6');
INSERT INTO urunler VALUES (NULL,'Masa Ustu PC','Masa üstülerin en yisi', 'mpc.jpg', '898.05', '18', '50', '2');

DROP TABLE IF EXISTS musteri;
CREATE TABLE IF NOT EXISTS musteri (
  id    INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
  ad    TEXT,
  soyad TEXT,
  email TEXT,
  sifre TEXT,
  adres TEXT
);
INSERT INTO musteri VALUES (NULL,'riza','celik','test@test.com', MD5('1234'), 'test adres');


DROP TABLE IF EXISTS siparis;
CREATE TABLE IF NOT EXISTS siparis (
  id       INTEGER PRIMARY KEY AUTOINCREMENT,
  urun_id  INTEGER REFERENCES urunler( id ),
  adet     INTEGER CHECK( adet > 0 ),
  tarih    TEXT,
  iptal    INTEGER DEFAULT 0
);

DROP TABLE IF EXISTS musteri_siparis;
CREATE TABLE IF NOT EXISTS musteri_siparis (
  musteri_id INTEGER REFERENCES musteri(id),
  siparis_id INTEGER REFERENCES siparis(id)
);
SQL;

$db->exec( $sql ) or die("tablolar oluşturulmadı");
echo 'tablolar oluşturuldu';
?>