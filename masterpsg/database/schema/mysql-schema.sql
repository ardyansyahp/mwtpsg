/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `bb_receiving`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bb_receiving` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tanggal_receiving` date NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_purchase_order` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manpower` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bb_receiving_supplier_id_foreign` (`supplier_id`),
  KEY `bb_receiving_tanggal_receiving_supplier_id_index` (`tanggal_receiving`,`supplier_id`),
  CONSTRAINT `bb_receiving_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bb_receiving_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bb_receiving_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `receiving_id` bigint unsigned NOT NULL,
  `schedule_detail_id` bigint unsigned DEFAULT NULL,
  `nomor_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lot_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_lot_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(12,3) NOT NULL DEFAULT '0.000',
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bb_receiving_detail_receiving_id_foreign` (`receiving_id`),
  KEY `bb_receiving_detail_schedule_detail_id_foreign` (`schedule_detail_id`),
  KEY `bb_receiving_detail_nomor_bahan_baku_foreign` (`nomor_bahan_baku`),
  CONSTRAINT `bb_receiving_detail_nomor_bahan_baku_foreign` FOREIGN KEY (`nomor_bahan_baku`) REFERENCES `m_bahanbaku` (`nomor_bahan_baku`),
  CONSTRAINT `bb_receiving_detail_receiving_id_foreign` FOREIGN KEY (`receiving_id`) REFERENCES `bb_receiving` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bb_receiving_detail_schedule_detail_id_foreign` FOREIGN KEY (`schedule_detail_id`) REFERENCES `t_schedule_detail` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kategori` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_bahan_baku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `m_bahanbaku_nomor_bahan_baku_unique` (`nomor_bahan_baku`),
  KEY `m_bahanbaku_supplier_id_foreign` (`supplier_id`),
  CONSTRAINT `m_bahanbaku_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_box`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_box` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_box` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_box_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_box_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_layer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_layer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_layer_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_layer_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_material` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_material_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_material_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_polybag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_polybag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_polybag_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_polybag_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_rempart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_rempart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `jenis` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_rempart_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_rempart_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_bahanbaku_subpart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_bahanbaku_subpart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_bahanbaku_subpart_bahan_baku_id_foreign` (`bahan_baku_id`),
  CONSTRAINT `m_bahanbaku_subpart_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_kendaraan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_kendaraan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nopol_kendaraan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_kendaraan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `merk_kendaraan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_kendaraan` int NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `m_kendaraan_nopol_kendaraan_unique` (`nopol_kendaraan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_manpower`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_manpower` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mp_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `departemen` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bagian` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `m_manpower_mp_id_unique` (`mp_id`),
  UNIQUE KEY `m_manpower_nik_unique` (`nik`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_mesin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_mesin` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mesin_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_mesin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `merk_mesin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tonase` int NOT NULL DEFAULT '0',
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_mold`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_mold` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `mold_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan_id` bigint unsigned DEFAULT NULL,
  `part_id` bigint unsigned DEFAULT NULL,
  `kode_mold` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_mold` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cavity` int NOT NULL DEFAULT '0',
  `cycle_time` decimal(8,2) DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `lokasi_mold` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe_mold` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material_resin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warna_produk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `m_mold_kode_mold_unique` (`kode_mold`),
  UNIQUE KEY `m_mold_mold_id_unique` (`mold_id`),
  KEY `m_mold_perusahaan_id_foreign` (`perusahaan_id`),
  KEY `m_mold_part_id_foreign` (`part_id`),
  CONSTRAINT `m_mold_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL,
  CONSTRAINT `m_mold_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_perusahaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_perusahaan` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `inisial_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_supplier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `m_plantgate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `m_plantgate` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `nama_plantgate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `m_plantgate_customer_id_foreign` (`customer_id`),
  CONSTRAINT `m_plantgate_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `min_stock` int NOT NULL DEFAULT '0',
  `max_stock` int NOT NULL DEFAULT '0',
  `customer_id` bigint unsigned DEFAULT NULL,
  `tipe_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proses` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_part_id` bigint unsigned DEFAULT NULL,
  `relation_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `CT_Inject` decimal(8,2) DEFAULT NULL,
  `CT_Assy` decimal(8,2) DEFAULT NULL,
  `Warna_Label_Packing` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `QTY_Packing_Box` int DEFAULT NULL,
  `R_Karton_Box_id` int DEFAULT NULL,
  `N_Cav1` decimal(12,4) DEFAULT NULL,
  `Runner` decimal(12,4) DEFAULT NULL,
  `Avg_Brutto` decimal(12,4) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sm_part_nomor_part_unique` (`nomor_part`),
  KEY `sm_part_customer_id_foreign` (`customer_id`),
  KEY `sm_part_parent_part_id_foreign` (`parent_part_id`),
  KEY `sm_part_nama_part_index` (`nama_part`),
  KEY `sm_part_model_part_index` (`model_part`),
  KEY `sm_part_proses_index` (`proses`),
  KEY `sm_part_status_index` (`status`),
  KEY `sm_part_tipe_id_index` (`tipe_id`),
  CONSTRAINT `sm_part_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sm_part_parent_part_id_foreign` FOREIGN KEY (`parent_part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_box`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_box` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `box_id` bigint unsigned DEFAULT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_box` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_box` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_box_part_id_foreign` (`part_id`),
  KEY `sm_part_box_box_id_foreign` (`box_id`),
  CONSTRAINT `sm_part_box_box_id_foreign` FOREIGN KEY (`box_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_box_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_layer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_layer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `layer_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(12,4) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_layer_part_id_foreign` (`part_id`),
  KEY `sm_part_layer_layer_id_foreign` (`layer_id`),
  CONSTRAINT `sm_part_layer_layer_id_foreign` FOREIGN KEY (`layer_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_layer_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_material`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_material` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `material_id` bigint unsigned DEFAULT NULL,
  `material_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `std_using` decimal(12,4) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_material_part_id_foreign` (`part_id`),
  KEY `sm_part_material_material_id_foreign` (`material_id`),
  CONSTRAINT `sm_part_material_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_material_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_polybag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_polybag` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `polybag_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(12,4) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_polybag_part_id_foreign` (`part_id`),
  KEY `sm_part_polybag_polybag_id_foreign` (`polybag_id`),
  CONSTRAINT `sm_part_polybag_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_polybag_polybag_id_foreign` FOREIGN KEY (`polybag_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_rempart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_rempart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `rempart_id` bigint unsigned DEFAULT NULL,
  `qty` decimal(12,4) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_rempart_part_id_foreign` (`part_id`),
  KEY `sm_part_rempart_rempart_id_foreign` (`rempart_id`),
  CONSTRAINT `sm_part_rempart_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_rempart_rempart_id_foreign` FOREIGN KEY (`rempart_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_part_subpart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_part_subpart` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `subpart_id` bigint unsigned DEFAULT NULL,
  `std_using` decimal(12,4) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sm_part_subpart_part_id_foreign` (`part_id`),
  KEY `sm_part_subpart_subpart_id_foreign` (`subpart_id`),
  CONSTRAINT `sm_part_subpart_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_part_subpart_subpart_id_foreign` FOREIGN KEY (`subpart_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sm_plantgate_part`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sm_plantgate_part` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plantgate_id` bigint unsigned NOT NULL,
  `part_id` bigint unsigned NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sm_plantgate_part_plantgate_id_part_id_unique` (`plantgate_id`,`part_id`),
  KEY `sm_plantgate_part_plantgate_id_index` (`plantgate_id`),
  KEY `sm_plantgate_part_part_id_index` (`part_id`),
  KEY `sm_plantgate_part_status_index` (`status`),
  CONSTRAINT `sm_plantgate_part_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sm_plantgate_part_plantgate_id_foreign` FOREIGN KEY (`plantgate_id`) REFERENCES `m_plantgate` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_finishgood_in`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_finishgood_in` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assy_out_id` bigint unsigned DEFAULT NULL,
  `lot_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lot_produksi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_planning` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesin_id` bigint unsigned DEFAULT NULL,
  `tanggal_produksi` datetime NOT NULL,
  `shift` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_id` bigint unsigned NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `customer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manpower_id` bigint unsigned DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_finishgood_in_mesin_id_foreign` (`mesin_id`),
  KEY `t_finishgood_in_part_id_foreign` (`part_id`),
  KEY `t_finishgood_in_manpower_id_foreign` (`manpower_id`),
  KEY `t_finishgood_in_assy_out_id_index` (`assy_out_id`),
  KEY `t_finishgood_in_lot_number_index` (`lot_number`),
  CONSTRAINT `t_finishgood_in_manpower_id_foreign` FOREIGN KEY (`manpower_id`) REFERENCES `m_manpower` (`id`),
  CONSTRAINT `t_finishgood_in_mesin_id_foreign` FOREIGN KEY (`mesin_id`) REFERENCES `m_mesin` (`id`),
  CONSTRAINT `t_finishgood_in_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_finishgood_out`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_finishgood_out` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `finish_good_in_id` bigint unsigned NOT NULL,
  `lot_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `spk_id` bigint unsigned DEFAULT NULL,
  `part_id` bigint unsigned NOT NULL,
  `waktu_scan_out` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cycle` int NOT NULL DEFAULT '1',
  `qty` int NOT NULL DEFAULT '0',
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_finishgood_out_finish_good_in_id_foreign` (`finish_good_in_id`),
  KEY `t_finishgood_out_spk_id_foreign` (`spk_id`),
  KEY `t_finishgood_out_part_id_foreign` (`part_id`),
  KEY `t_finishgood_out_no_surat_jalan_index` (`no_surat_jalan`),
  CONSTRAINT `t_finishgood_out_finish_good_in_id_foreign` FOREIGN KEY (`finish_good_in_id`) REFERENCES `t_finishgood_in` (`id`),
  CONSTRAINT `t_finishgood_out_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`),
  CONSTRAINT `t_finishgood_out_spk_id_foreign` FOREIGN KEY (`spk_id`) REFERENCES `t_spk` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_gps_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_gps_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_header_id` bigint unsigned NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `device_info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_gps_logs_delivery_header_id_foreign` (`delivery_header_id`),
  CONSTRAINT `t_gps_logs_delivery_header_id_foreign` FOREIGN KEY (`delivery_header_id`) REFERENCES `t_shipping_delivery_header` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_purchase_order_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_purchase_order_customer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `po_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qty` int NOT NULL,
  `delivery_frequency` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `month` int NOT NULL,
  `year` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_schedule_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_schedule_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_header_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `po_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pc_plan` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pc_act` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pc_blc` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pc_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `pc_ar` decimal(10,2) NOT NULL DEFAULT '0.00',
  `pc_sr` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_schedule_detail_schedule_header_id_tanggal_index` (`schedule_header_id`,`tanggal`),
  CONSTRAINT `t_schedule_detail_schedule_header_id_foreign` FOREIGN KEY (`schedule_header_id`) REFERENCES `t_schedule_header` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_schedule_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_schedule_header` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `bahan_baku_id` bigint unsigned NOT NULL,
  `po_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_plan_auto` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_plan_manual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_plan` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_act` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_blc` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `total_ar` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_sr` decimal(10,2) NOT NULL DEFAULT '0.00',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_schedule_header_supplier_id_foreign` (`supplier_id`),
  KEY `t_schedule_header_bahan_baku_id_foreign` (`bahan_baku_id`),
  KEY `t_schedule_header_periode_supplier_id_bahan_baku_id_index` (`periode`,`supplier_id`,`bahan_baku_id`),
  KEY `t_schedule_header_po_number_index` (`po_number`),
  CONSTRAINT `t_schedule_header_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `t_schedule_header_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_shipping_delivery_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_shipping_delivery_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_header_id` bigint unsigned NOT NULL,
  `tanggal` date NOT NULL,
  `jam` int NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `lokasi_saat_ini` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `waktu_update` datetime DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto_bukti` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_shipping_delivery_detail_delivery_header_id_tanggal_jam_index` (`delivery_header_id`,`tanggal`,`jam`),
  CONSTRAINT `t_shipping_delivery_detail_delivery_header_id_foreign` FOREIGN KEY (`delivery_header_id`) REFERENCES `t_shipping_delivery_header` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_shipping_delivery_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_shipping_delivery_header` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `periode` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kendaraan_id` bigint unsigned NOT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '-',
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_berangkat` date NOT NULL,
  `waktu_berangkat` datetime DEFAULT NULL,
  `waktu_tiba` datetime DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `total_trip` int NOT NULL DEFAULT '0',
  `total_delivered` int NOT NULL DEFAULT '0',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_shipping_delivery_header_kendaraan_id_foreign` (`kendaraan_id`),
  KEY `t_shipping_delivery_header_driver_id_foreign` (`driver_id`),
  KEY `t_shipping_delivery_header_no_surat_jalan_index` (`no_surat_jalan`),
  KEY `t_shipping_delivery_header_tanggal_berangkat_index` (`tanggal_berangkat`),
  CONSTRAINT `t_shipping_delivery_header_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `m_manpower` (`id`),
  CONSTRAINT `t_shipping_delivery_header_kendaraan_id_foreign` FOREIGN KEY (`kendaraan_id`) REFERENCES `m_kendaraan` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_shipping_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_shipping_incidents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `delivery_header_id` bigint unsigned NOT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_shipping_incidents_delivery_header_id_foreign` (`delivery_header_id`),
  CONSTRAINT `t_shipping_incidents_delivery_header_id_foreign` FOREIGN KEY (`delivery_header_id`) REFERENCES `t_shipping_delivery_header` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_spk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_spk` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nomor_spk` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_spk_id` bigint unsigned DEFAULT NULL,
  `cycle_number` int DEFAULT NULL,
  `manpower_pembuat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `plantgate_id` bigint unsigned DEFAULT NULL,
  `tanggal` datetime NOT NULL,
  `jam_berangkat_plan` time DEFAULT NULL,
  `jam_datang_plan` time DEFAULT NULL,
  `cycle` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_surat_jalan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_plat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_id` bigint unsigned DEFAULT NULL,
  `model_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `t_spk_nomor_spk_unique` (`nomor_spk`),
  KEY `t_spk_customer_id_foreign` (`customer_id`),
  KEY `t_spk_plantgate_id_foreign` (`plantgate_id`),
  KEY `t_spk_driver_id_foreign` (`driver_id`),
  KEY `t_spk_parent_spk_id_foreign` (`parent_spk_id`),
  KEY `t_spk_no_surat_jalan_index` (`no_surat_jalan`),
  CONSTRAINT `t_spk_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`),
  CONSTRAINT `t_spk_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `m_manpower` (`id`),
  CONSTRAINT `t_spk_parent_spk_id_foreign` FOREIGN KEY (`parent_spk_id`) REFERENCES `t_spk` (`id`) ON DELETE SET NULL,
  CONSTRAINT `t_spk_plantgate_id_foreign` FOREIGN KEY (`plantgate_id`) REFERENCES `m_plantgate` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_spk_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_spk_detail` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `spk_id` bigint unsigned NOT NULL,
  `part_id` bigint unsigned NOT NULL,
  `qty_packing_box` int NOT NULL DEFAULT '0',
  `jadwal_delivery_pcs` int NOT NULL DEFAULT '0',
  `original_jadwal_delivery_pcs` int NOT NULL DEFAULT '0',
  `jumlah_pulling_box` int NOT NULL DEFAULT '0',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_spk_detail_spk_id_foreign` (`spk_id`),
  KEY `t_spk_detail_part_id_foreign` (`part_id`),
  CONSTRAINT `t_spk_detail_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`),
  CONSTRAINT `t_spk_detail_spk_id_foreign` FOREIGN KEY (`spk_id`) REFERENCES `t_spk` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_stock_fg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_stock_fg` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `t_stock_fg_part_id_index` (`part_id`),
  CONSTRAINT `t_stock_fg_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `t_stock_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `t_stock_opname` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `part_id` bigint unsigned NOT NULL,
  `qty_system` int NOT NULL,
  `qty_actual` int NOT NULL,
  `diff` int NOT NULL,
  `manpower_id` bigint unsigned DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_permissions` (
  `user_id` bigint unsigned NOT NULL,
  `permission_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`permission_id`),
  KEY `user_permissions_permission_id_foreign` (`permission_id`),
  CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'2024_01_01_000000_create_auth_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2024_01_02_000000_create_m_perusahaan_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2024_01_03_000000_create_m_mesin_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2024_01_04_000000_create_m_plantgate_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2024_01_05_000000_create_m_kendaraan_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2024_01_06_000000_create_m_bahanbaku_tables',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_02_03_111114_add_keterangan_to_m_bahanbaku_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_02_03_120000_create_sm_part_tables',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_02_03_120958_change_tipe_id_column_in_sm_part_table',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_02_03_122226_add_indexes_to_sm_part_table',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_02_03_122719_create_m_mold_table',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_02_03_125431_add_mold_id_to_m_mold_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_02_03_132707_create_sm_plantgate_part_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_02_03_143000_create_control_supplier_tables',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_02_03_143500_create_bahan_baku_receiving_tables',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_02_03_150000_create_spk_tables',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_02_03_150500_create_finish_good_tables',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_02_03_151000_create_shipping_distribution_tables',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_02_03_151500_create_stock_fg_table',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_02_04_084534_add_deleted_at_to_t_spk_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_02_04_122752_create_t_gps_logs_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_02_04_124639_create_t_shipping_incidents_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_02_05_135619_add_lot_produksi_to_t_finishgood_in_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_02_05_141100_create_t_stock_opname_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_02_05_141104_create_t_purchase_order_customer_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_02_06_102005_add_stock_limits_to_sm_part_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_02_08_061636_create_sessions_table',24);
