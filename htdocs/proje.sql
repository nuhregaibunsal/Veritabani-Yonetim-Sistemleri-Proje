-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 15 May 2025, 18:00:54
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `proje`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `adresler`
--

CREATE TABLE `adresler` (
  `adres_id` int(11) NOT NULL,
  `kullanici_id` int(11) DEFAULT NULL,
  `ad_soyad` varchar(100) DEFAULT NULL,
  `adres_satiri1` varchar(255) DEFAULT NULL,
  `adres_satiri2` varchar(255) DEFAULT NULL,
  `sehir` varchar(30) DEFAULT NULL,
  `posta_kodu` varchar(10) DEFAULT NULL,
  `ulke` varchar(30) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `adresler`
--

INSERT INTO `adresler` (`adres_id`, `kullanici_id`, `ad_soyad`, `adres_satiri1`, `adres_satiri2`, `sehir`, `posta_kodu`, `ulke`, `telefon`) VALUES
(1, 6, 'Nuh Ünsal', 'KONYA YOLUNDAYIM KONYA', 'asdasdasd', 'Konya', '410900', 'Türkiye', '05445686030');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kategoriler`
--

CREATE TABLE `kategoriler` (
  `kategori_id` int(11) NOT NULL,
  `ad` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kategoriler`
--

INSERT INTO `kategoriler` (`kategori_id`, `ad`) VALUES
(1, 'Elektironik Cihazlar'),
(2, 'Tablet');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `kullanici_id` int(11) NOT NULL,
  `ad_soyad` varchar(100) DEFAULT NULL,
  `eposta` varchar(100) DEFAULT NULL,
  `sifre_hash` varchar(255) DEFAULT NULL,
  `satici` int(11) NOT NULL DEFAULT 0,
  `kullaniciOnay` int(11) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `kayit_tarihi` datetime DEFAULT current_timestamp(),
  `dogum_tarihi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`kullanici_id`, `ad_soyad`, `eposta`, `sifre_hash`, `satici`, `kullaniciOnay`, `telefon`, `kayit_tarihi`, `dogum_tarihi`) VALUES
(1, NULL, NULL, NULL, 0, NULL, NULL, '2025-05-14 01:39:56', NULL),
(2, 'Nuh Regaib Ünsal', 'nuhregaibunsal4114@gmail.com', '$2y$10$9uN/GGy8lPcr01Nfo5qPE.EMj.Iw54DBUr8cHATau/teXvXx03b3S', 0, NULL, '05445686030', '2025-05-14 01:52:59', '2005-08-11 00:00:00'),
(6, 'Nuh Ünsal', 'nuhunsalregaib4114@gmail.com', '$2y$10$98WOs8idT2Tlp1Un4eWl8uZXsilMsuGetMQ6zUsXR1Zcq9uGtzJDm', 1, 234852, '05445686030', '2025-05-14 02:03:01', '1111-11-11 00:00:00'),
(7, 'asd', 'asd@fasdlmfa.com', '$2y$10$g1260Xx/6y7y4f16qS0UvutFesms/pCTALfOGcMDgFtcr1ztidVlK', 1, 351520, '12421', '2025-05-15 15:10:28', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `sepetogeleri`
--

CREATE TABLE `sepetogeleri` (
  `sepet_oge_id` int(11) NOT NULL,
  `kullanici_id` int(11) DEFAULT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `adet` int(11) DEFAULT NULL,
  `eklenme_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `sepetogeleri`
--

INSERT INTO `sepetogeleri` (`sepet_oge_id`, `kullanici_id`, `urun_id`, `adet`, `eklenme_tarihi`) VALUES
(19, 2, 5, 1, '2025-05-15 17:44:45');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparisler`
--

CREATE TABLE `siparisler` (
  `siparis_id` int(11) NOT NULL,
  `satici_id` int(11) DEFAULT NULL,
  `kullanici_id` int(11) DEFAULT NULL,
  `adres_id` int(11) DEFAULT NULL,
  `durum` varchar(50) DEFAULT NULL,
  `toplam_tutar` decimal(10,2) DEFAULT NULL,
  `siparis_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `siparisler`
--

INSERT INTO `siparisler` (`siparis_id`, `satici_id`, `kullanici_id`, `adres_id`, `durum`, `toplam_tutar`, `siparis_tarihi`) VALUES
(2, 6, 6, 1, 'Tamamlandı', 246.00, '2025-05-15 17:22:28'),
(3, 6, 2, 1, 'Tamamlandı', 123.00, '2025-05-15 17:35:59'),
(4, 6, 6, 1, 'Tamamlandı', 123.00, '2025-05-15 17:56:43');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `siparisogeleri`
--

CREATE TABLE `siparisogeleri` (
  `siparis_oge_id` int(11) NOT NULL,
  `siparis_id` int(11) DEFAULT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `adet` int(11) DEFAULT NULL,
  `birim_fiyat` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `siparisogeleri`
--

INSERT INTO `siparisogeleri` (`siparis_oge_id`, `siparis_id`, `urun_id`, `adet`, `birim_fiyat`) VALUES
(1, 2, 5, 2, 123.00),
(2, 3, 5, 1, 123.00),
(3, 4, 5, 1, 123.00);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `urun_id` int(11) NOT NULL,
  `ad` varchar(150) DEFAULT NULL,
  `aciklama` text DEFAULT NULL,
  `fiyat` decimal(10,2) DEFAULT NULL,
  `stok` int(11) DEFAULT NULL,
  `aktif` int(11) DEFAULT 1,
  `satici_id` int(11) DEFAULT NULL,
  `resim_url` varchar(255) DEFAULT NULL,
  `eklenme_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`urun_id`, `ad`, `aciklama`, `fiyat`, `stok`, `aktif`, `satici_id`, `resim_url`, `eklenme_tarihi`) VALUES
(5, 'Deneme Ürünü 6', 'Deneme Ürünü 6', 123.00, 6, 1, 6, 'img/6825f607c8948.jpeg', '2025-05-15 17:11:19');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunyorumlari`
--

CREATE TABLE `urunyorumlari` (
  `yorum_id` int(11) NOT NULL,
  `kullanici_id` int(11) DEFAULT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `puan` int(11) DEFAULT NULL CHECK (`puan` between 1 and 5),
  `yorum_metni` text DEFAULT NULL,
  `yorum_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urun_kategoriler`
--

CREATE TABLE `urun_kategoriler` (
  `urun_id` int(11) NOT NULL,
  `kategori_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Tablo döküm verisi `urun_kategoriler`
--

INSERT INTO `urun_kategoriler` (`urun_id`, `kategori_id`) VALUES
(5, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `yoneticikullanicilar`
--

CREATE TABLE `yoneticikullanicilar` (
  `yonetici_id` int(11) NOT NULL,
  `ad_soyad` varchar(100) DEFAULT NULL,
  `eposta` varchar(100) DEFAULT NULL,
  `sifre_hash` varchar(255) DEFAULT NULL,
  `rol` varchar(50) DEFAULT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `kayit_tarihi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `adresler`
--
ALTER TABLE `adresler`
  ADD PRIMARY KEY (`adres_id`),
  ADD KEY `kullanici_id` (`kullanici_id`);

--
-- Tablo için indeksler `kategoriler`
--
ALTER TABLE `kategoriler`
  ADD PRIMARY KEY (`kategori_id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`kullanici_id`),
  ADD UNIQUE KEY `eposta` (`eposta`);

--
-- Tablo için indeksler `sepetogeleri`
--
ALTER TABLE `sepetogeleri`
  ADD PRIMARY KEY (`sepet_oge_id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `urun_id` (`urun_id`);

--
-- Tablo için indeksler `siparisler`
--
ALTER TABLE `siparisler`
  ADD PRIMARY KEY (`siparis_id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `adres_id` (`adres_id`),
  ADD KEY `fk_satici_id` (`satici_id`);

--
-- Tablo için indeksler `siparisogeleri`
--
ALTER TABLE `siparisogeleri`
  ADD PRIMARY KEY (`siparis_oge_id`),
  ADD KEY `siparis_id` (`siparis_id`),
  ADD KEY `urun_id` (`urun_id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`urun_id`),
  ADD KEY `satici_id` (`satici_id`);

--
-- Tablo için indeksler `urunyorumlari`
--
ALTER TABLE `urunyorumlari`
  ADD PRIMARY KEY (`yorum_id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `urun_id` (`urun_id`);

--
-- Tablo için indeksler `urun_kategoriler`
--
ALTER TABLE `urun_kategoriler`
  ADD PRIMARY KEY (`urun_id`,`kategori_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Tablo için indeksler `yoneticikullanicilar`
--
ALTER TABLE `yoneticikullanicilar`
  ADD PRIMARY KEY (`yonetici_id`),
  ADD UNIQUE KEY `eposta` (`eposta`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `adresler`
--
ALTER TABLE `adresler`
  MODIFY `adres_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `kategoriler`
--
ALTER TABLE `kategoriler`
  MODIFY `kategori_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `kullanici_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `sepetogeleri`
--
ALTER TABLE `sepetogeleri`
  MODIFY `sepet_oge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `siparisler`
--
ALTER TABLE `siparisler`
  MODIFY `siparis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Tablo için AUTO_INCREMENT değeri `siparisogeleri`
--
ALTER TABLE `siparisogeleri`
  MODIFY `siparis_oge_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `urun_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Tablo için AUTO_INCREMENT değeri `urunyorumlari`
--
ALTER TABLE `urunyorumlari`
  MODIFY `yorum_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Tablo için AUTO_INCREMENT değeri `yoneticikullanicilar`
--
ALTER TABLE `yoneticikullanicilar`
  MODIFY `yonetici_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `adresler`
--
ALTER TABLE `adresler`
  ADD CONSTRAINT `adresler_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`kullanici_id`);

--
-- Tablo kısıtlamaları `sepetogeleri`
--
ALTER TABLE `sepetogeleri`
  ADD CONSTRAINT `sepetogeleri_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`kullanici_id`),
  ADD CONSTRAINT `sepetogeleri_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`urun_id`);

--
-- Tablo kısıtlamaları `siparisler`
--
ALTER TABLE `siparisler`
  ADD CONSTRAINT `fk_satici_id` FOREIGN KEY (`satici_id`) REFERENCES `kullanicilar` (`kullanici_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `siparisler_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`kullanici_id`),
  ADD CONSTRAINT `siparisler_ibfk_2` FOREIGN KEY (`adres_id`) REFERENCES `adresler` (`adres_id`);

--
-- Tablo kısıtlamaları `siparisogeleri`
--
ALTER TABLE `siparisogeleri`
  ADD CONSTRAINT `siparisogeleri_ibfk_1` FOREIGN KEY (`siparis_id`) REFERENCES `siparisler` (`siparis_id`),
  ADD CONSTRAINT `siparisogeleri_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`urun_id`);

--
-- Tablo kısıtlamaları `urunler`
--
ALTER TABLE `urunler`
  ADD CONSTRAINT `satici_id` FOREIGN KEY (`satici_id`) REFERENCES `kullanicilar` (`kullanici_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Tablo kısıtlamaları `urunyorumlari`
--
ALTER TABLE `urunyorumlari`
  ADD CONSTRAINT `urunyorumlari_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanicilar` (`kullanici_id`),
  ADD CONSTRAINT `urunyorumlari_ibfk_2` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`urun_id`);

--
-- Tablo kısıtlamaları `urun_kategoriler`
--
ALTER TABLE `urun_kategoriler`
  ADD CONSTRAINT `urun_kategoriler_ibfk_1` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`urun_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `urun_kategoriler_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `kategoriler` (`kategori_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
