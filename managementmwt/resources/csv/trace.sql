-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 03, 2026 at 02:11 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `trace`
--

-- --------------------------------------------------------

--
-- Table structure for table `bb_receiving`
--

CREATE TABLE `bb_receiving` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal_receiving` date NOT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `no_surat_jalan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_purchase_order` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manpower` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shift` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bb_receiving_detail`
--

CREATE TABLE `bb_receiving_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `receiving_id` bigint UNSIGNED NOT NULL,
  `nomor_bahan_baku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_lot_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(12,3) NOT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2024_01_01_000001_create_m_perusahaan_table', 1),
(4, '2024_01_01_000004_create_sm_material_table', 1),
(5, '2026_01_15_074536_create_m_bahan_baku_material_table', 2),
(6, '2026_01_15_100829_create_m_bahan_baku_subpart_table', 3),
(7, '2026_01_15_074540_create_m_bahan_baku_box_table', 4),
(8, '2026_01_15_071118_create_sm_part_material_table', 5),
(9, '2024_01_01_000008_create_sm_part_subpart_table', 6),
(10, '2024_01_01_000009_create_sm_part_layer_table', 7),
(11, '2026_01_15_065936_create_sm_part_box_table', 8),
(12, '2026_01_15_065944_create_sm_part_polybag_table', 9),
(13, '2026_01_15_071114_create_sm_part_rempart_table', 10),
(14, '2026_01_07_072255_update_sm_part_layer_to_use_bahan_baku_id', 11),
(15, '2026_01_15_131403_update_sm_part_polybag_to_match_layer_structure', 12),
(16, '2026_01_15_091343_add_parent_part_relation_to_sm_part_table', 13),
(17, '2026_01_18_142816_create_sessions_table', 14),
(18, '2026_01_15_074549_create_m_bahan_baku_polybag_table', 15),
(19, '2026_01_15_074544_create_m_bahan_baku_layer_table', 16),
(20, '2026_01_15_074554_create_m_bahan_baku_rempart_table', 17),
(21, '2026_01_07_013858_update_m_mold_table_structure', 18),
(22, '2025_12_18_030010_create_m_mesin_table', 19),
(23, '2026_01_07_024108_create_m_manpower_table', 20),
(24, '2026_01_07_014440_add_mesin_id_to_m_mesin_table', 21),
(25, '2026_01_07_031003_add_qrcode_to_m_manpower_and_m_mesin_tables', 22),
(26, '2026_01_08_042921_create_m_plantgate_table', 23),
(27, '2026_01_05_061625_create_t_inject_out_detail_table', 24),
(28, '2026_01_05_035038_create_t_assy_out_table', 25),
(29, '2026_01_05_072450_create_t_wip_out_detail_table', 26),
(30, '2026_01_08_000000_create_t_finish_good_in_table', 27),
(31, '2026_01_18_092523_create_t_shipping_delivery_detail_table', 28),
(32, '2026_01_06_000001_add_fk_inject_out_to_inject_in', 29),
(33, '2026_01_07_000002_add_fk_assy_in_to_wip_out', 30),
(34, '2026_01_07_000003_add_fk_wip_out_detail_to_wip_out', 31),
(35, '2025_12_18_030020_create_t_planning_day_table', 32),
(36, '2025_12_18_030030_create_t_planning_run_table', 33),
(37, '2026_01_08_042927_create_t_spk_table', 34),
(38, '2026_01_08_050000_create_m_kendaraan_table', 35),
(39, '2026_01_06_000000_create_t_inject_in_table', 36),
(40, '2026_01_05_031139_create_t_inject_out_table', 37),
(41, '2026_01_05_034242_create_t_assy_in_table', 38),
(42, '2026_01_07_000000_create_t_wip_in_table', 39),
(43, '2026_01_07_000001_create_t_wip_out_table', 40),
(44, '2026_01_09_000000_create_t_finish_good_out_table', 41),
(45, '2025_12_18_024625_create_receivings_table', 42),
(46, '2025_12_18_024626_create_receiving_details_table', 43),
(47, '2025_12_18_030040_create_t_planning_run_hourly_target_table', 44),
(48, '2025_12_18_030050_create_t_planning_run_hourly_actual_table', 45),
(49, '2025_12_18_030060_create_t_planning_run_kebutuhan_table', 46),
(50, '2025_12_18_030070_create_t_planning_run_material_table', 47),
(51, '2025_12_18_030080_create_t_planning_run_material_shift_table', 48),
(52, '2025_12_18_030090_create_t_planning_material_order_produksi_table', 49),
(53, '2025_12_18_030100_create_t_planning_run_subpart_table', 50),
(54, '2025_12_18_030110_create_t_planning_run_subpart_shift_table', 51),
(55, '2026_01_05_014059_add_part_id_to_t_supply_table', 52),
(56, '2026_01_05_014507_add_meja_to_t_supply_table', 53),
(58, '2026_01_18_154816_make_tipe_id_nullable_in_sm_part_table', 54),
(59, '2026_01_18_155514_change_tipe_id_to_string_in_sm_part_table', 55),
(60, '2026_01_13_100000_create_t_schedule_header_table', 56),
(61, '2026_01_13_100001_create_t_schedule_detail_table', 57),
(62, '2026_01_07_160254_make_mold_id_nullable_in_planning_run_table', 58),
(63, '2026_01_07_160251_add_tipe_and_meja_to_planning_day_table', 59),
(64, '2026_01_08_042930_create_t_spk_detail_table', 60),
(65, '2026_01_18_092513_create_t_shipping_loading_table', 61),
(66, '2026_01_21_133719_fix_manpower_type_in_bb_receiving_table', 62),
(67, '2026_01_07_153144_add_uom_to_bb_receiving_detail_table', 63),
(68, '2026_02_03_085350_enhance_m_plantgate_table', 64),
(69, '2026_01_25_110732_create_permissions_table', 65),
(70, '2026_01_25_110732_create_users_table', 65),
(71, '2026_01_25_110735_create_user_permissions_table', 65),
(72, '2026_01_25_120352_update_m_perusahaan_table_for_sap_import', 65),
(73, '2026_01_25_134132_add_qrcode_to_m_kendaraan_table', 65),
(74, '2026_01_25_172436_add_details_to_t_finishgood_in_table', 65),
(75, '2026_01_25_174435_update_relations_t_finishgood_in', 65),
(83, '2026_01_25_192347_update_t_finishgood_out_add_cycle_qty', 66),
(84, '2026_01_25_201356_add_no_surat_jalan_to_t_finishgood_out', 66),
(85, '2026_01_25_212000_add_foto_bukti_to_t_shipping_delivery_detail_table', 66),
(86, '2026_01_25_213245_update_status_enum_in_shipping_tables', 66),
(87, '2026_01_25_220142_create_t_gps_logs_table', 66),
(88, '2026_01_26_083410_add_planning_fields_to_t_spk_table', 66),
(89, '2026_01_26_083814_add_driver_id_to_t_spk_table', 66),
(90, '2026_02_03_090405_fix_missing_columns_manpower_plantgate', 67);

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku`
--

CREATE TABLE `m_bahanbaku` (
  `id` bigint UNSIGNED NOT NULL,
  `kategori` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_bahan_baku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_box`
--

CREATE TABLE `m_bahanbaku_box` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `jenis` enum('polybox','impraboard') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_box` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_layer`
--

CREATE TABLE `m_bahanbaku_layer` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `jenis` enum('ldpe','polyfoam_sheet','layer_sheet','karton','foam_sheet','foam_bag') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_material`
--

CREATE TABLE `m_bahanbaku_material` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_polybag`
--

CREATE TABLE `m_bahanbaku_polybag` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `jenis` enum('ldpe') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_rempart`
--

CREATE TABLE `m_bahanbaku_rempart` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `jenis` enum('karton_box_p0_d0','polybag_p0_p0','gasket_duplex_p0_ld','foam_sheet_p0_s0','hologram_p0_h0','label_a','label_b') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_bahanbaku_subpart`
--

CREATE TABLE `m_bahanbaku_subpart` (
  `id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `nama_bahan_baku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `std_packing` decimal(10,2) DEFAULT NULL,
  `uom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_kendaraan`
--

CREATE TABLE `m_kendaraan` (
  `id` bigint UNSIGNED NOT NULL,
  `nopol_kendaraan` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `qrcode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kendaraan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `merk_kendaraan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tahun_kendaraan` int NOT NULL,
  `status_kendaraan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_manpower`
--

CREATE TABLE `m_manpower` (
  `id` bigint UNSIGNED NOT NULL,
  `mp_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `departemen` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bagian` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_mesin`
--

CREATE TABLE `m_mesin` (
  `id` bigint UNSIGNED NOT NULL,
  `mesin_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_mesin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `merk_mesin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tonase` int UNSIGNED DEFAULT NULL,
  `qrcode` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_mold`
--

CREATE TABLE `m_mold` (
  `id` bigint UNSIGNED NOT NULL,
  `mold_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `perusahaan_id` bigint UNSIGNED NOT NULL,
  `kode_mold` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_mold` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `tipe_id` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cavity` int UNSIGNED NOT NULL DEFAULT '1',
  `cycle_time` decimal(10,2) DEFAULT NULL,
  `capacity` int UNSIGNED DEFAULT NULL,
  `lokasi_mold` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe_mold` enum('single','family') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `material_resin` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warna_produk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jumlah_manpower_proses` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_perusahaan`
--

CREATE TABLE `m_perusahaan` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `inisial_perusahaan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_perusahaan` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_supplier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `m_plantgate`
--

CREATE TABLE `m_plantgate` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `nama_plantgate` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('sKUXkkEpsItsPDx8l4rqMLS0e5fVT31XeC2qyB7E', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicUUwM2lJNUZabnZGNWFRWUJFQlMyTUxRMGhBRlB1TUkxVnRCR0FaaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHA6Ly90cmFjZS50ZXN0L2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9fQ==', 1770084543);

-- --------------------------------------------------------

--
-- Table structure for table `sm_part`
--

CREATE TABLE `sm_part` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_part` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_part` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_part_forecast` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_part_sap` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `tipe_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `model_part` enum('regular','ckd','cbu','rempart') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `proses` enum('inject','assy','kompleks') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_part_id` bigint UNSIGNED DEFAULT NULL,
  `relation_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'inject_to_assy, assy_to_packing, variant, revision',
  `CT_Inject` decimal(10,2) DEFAULT NULL,
  `CT_Assy` decimal(10,2) DEFAULT NULL,
  `Warna_Label_Packing` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `QTY_Packing_Box` int DEFAULT NULL,
  `N_Cav1` decimal(10,3) DEFAULT NULL,
  `Runner` decimal(10,3) DEFAULT NULL,
  `Avg_Brutto` decimal(10,3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_box`
--

CREATE TABLE `sm_part_box` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `box_id` bigint UNSIGNED DEFAULT NULL,
  `tipe` enum('Inject','Assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_box` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_box` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_layer`
--

CREATE TABLE `sm_part_layer` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED DEFAULT NULL,
  `layer_number` int NOT NULL,
  `std_using` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_material`
--

CREATE TABLE `sm_part_material` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED DEFAULT NULL,
  `material_type` enum('material','masterbatch') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'material',
  `tipe` enum('Inject','Assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `std_using` decimal(10,2) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_polybag`
--

CREATE TABLE `sm_part_polybag` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED DEFAULT NULL,
  `polybag_number` int DEFAULT NULL,
  `tipe` enum('Inject','Assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_polybag` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `panjang` decimal(10,2) DEFAULT NULL,
  `lebar` decimal(10,2) DEFAULT NULL,
  `tinggi` decimal(10,2) DEFAULT NULL,
  `std_using` decimal(10,2) DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_rempart`
--

CREATE TABLE `sm_part_rempart` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `tipe` enum('Inject','Assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `R_Polybag_id` bigint UNSIGNED DEFAULT NULL,
  `R_Gasket_Duplex_id` bigint UNSIGNED DEFAULT NULL,
  `R_Foam_Sheet_id` bigint UNSIGNED DEFAULT NULL,
  `R_Hologram_id` bigint UNSIGNED DEFAULT NULL,
  `R_LabelA_id` bigint UNSIGNED DEFAULT NULL,
  `R_LabelB_id` bigint UNSIGNED DEFAULT NULL,
  `R_Qty_Pcs` int DEFAULT NULL,
  `urutan` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_part_subpart`
--

CREATE TABLE `sm_part_subpart` (
  `id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `subpart_id` bigint UNSIGNED NOT NULL,
  `nomor_part_subpart` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `std_using` decimal(10,2) NOT NULL DEFAULT '1.00',
  `urutan` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sm_plantgate_part`
--

CREATE TABLE `sm_plantgate_part` (
  `id` bigint UNSIGNED NOT NULL,
  `plantgate_id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_assy_in`
--

CREATE TABLE `t_assy_in` (
  `id` bigint UNSIGNED NOT NULL,
  `supply_detail_id` bigint UNSIGNED DEFAULT NULL,
  `wip_out_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `manpower` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_assy_out`
--

CREATE TABLE `t_assy_out` (
  `id` bigint UNSIGNED NOT NULL,
  `assy_in_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_finishgood_in`
--

CREATE TABLE `t_finishgood_in` (
  `id` bigint UNSIGNED NOT NULL,
  `assy_out_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_planning` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mesin_id` bigint UNSIGNED DEFAULT NULL,
  `manpower_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_produksi` date DEFAULT NULL,
  `shift` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `customer` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_finishgood_out`
--

CREATE TABLE `t_finishgood_out` (
  `id` bigint UNSIGNED NOT NULL,
  `finish_good_in_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spk_id` bigint UNSIGNED DEFAULT NULL,
  `cycle` int NOT NULL DEFAULT '1',
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `waktu_scan_out` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `no_surat_jalan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_gps_logs`
--

CREATE TABLE `t_gps_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_header_id` bigint UNSIGNED NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `device_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_inject_in`
--

CREATE TABLE `t_inject_in` (
  `id` bigint UNSIGNED NOT NULL,
  `supply_detail_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planning_run_id` bigint UNSIGNED DEFAULT NULL,
  `mesin_id` bigint UNSIGNED NOT NULL,
  `manpower` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_inject_out`
--

CREATE TABLE `t_inject_out` (
  `id` bigint UNSIGNED NOT NULL,
  `inject_in_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `planning_run_id` bigint UNSIGNED DEFAULT NULL,
  `box_number` int UNSIGNED DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_inject_out_detail`
--

CREATE TABLE `t_inject_out_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `inject_out_id` bigint UNSIGNED DEFAULT NULL,
  `box_number` int UNSIGNED DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_day`
--

CREATE TABLE `t_planning_day` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `tipe` enum('inject','assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inject',
  `mesin_id` bigint UNSIGNED DEFAULT NULL,
  `meja` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_material_order_produksi`
--

CREATE TABLE `t_planning_material_order_produksi` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_material_id` bigint UNSIGNED NOT NULL,
  `qty_prd_order` decimal(18,3) NOT NULL DEFAULT '0.000',
  `qty_return` decimal(18,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run`
--

CREATE TABLE `t_planning_run` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_day_id` bigint UNSIGNED NOT NULL,
  `urutan_run` tinyint UNSIGNED DEFAULT NULL,
  `mold_id` bigint UNSIGNED DEFAULT NULL,
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `lot_produksi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_at` datetime NOT NULL,
  `end_at` datetime NOT NULL,
  `qty_target_total` int UNSIGNED DEFAULT NULL,
  `qty_actual_total` int UNSIGNED DEFAULT NULL,
  `downtime_menit` int UNSIGNED DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_hourly_actual`
--

CREATE TABLE `t_planning_run_hourly_actual` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `hour_start` datetime NOT NULL,
  `hour_end` datetime NOT NULL,
  `qty_actual` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_hourly_target`
--

CREATE TABLE `t_planning_run_hourly_target` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `hour_start` datetime NOT NULL,
  `hour_end` datetime NOT NULL,
  `qty_target` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_kebutuhan`
--

CREATE TABLE `t_planning_run_kebutuhan` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `qty_polybox` int UNSIGNED NOT NULL DEFAULT '0',
  `qty_partisi` int UNSIGNED NOT NULL DEFAULT '0',
  `qty_imfrabolt` int UNSIGNED NOT NULL DEFAULT '0',
  `qty_karton` int UNSIGNED NOT NULL DEFAULT '0',
  `qty_troly` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_material`
--

CREATE TABLE `t_planning_run_material` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `material_id` bigint UNSIGNED NOT NULL,
  `qty_total` decimal(18,3) NOT NULL DEFAULT '0.000',
  `uom` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_material_shift`
--

CREATE TABLE `t_planning_run_material_shift` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_material_id` bigint UNSIGNED NOT NULL,
  `shift_no` tinyint UNSIGNED NOT NULL,
  `qty` decimal(18,3) NOT NULL DEFAULT '0.000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_subpart`
--

CREATE TABLE `t_planning_run_subpart` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `partsubpart_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_planning_run_subpart_shift`
--

CREATE TABLE `t_planning_run_subpart_shift` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_subpart_id` bigint UNSIGNED NOT NULL,
  `shift_no` tinyint UNSIGNED NOT NULL,
  `qty` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_schedule_detail`
--

CREATE TABLE `t_schedule_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `schedule_header_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `po_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pc_plan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pc_act` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pc_blc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `pc_status` enum('PENDING','CLOSE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `pc_ar` decimal(5,2) NOT NULL DEFAULT '0.00',
  `pc_sr` decimal(5,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_schedule_header`
--

CREATE TABLE `t_schedule_header` (
  `id` bigint UNSIGNED NOT NULL,
  `periode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `bahan_baku_id` bigint UNSIGNED NOT NULL,
  `po_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_plan_auto` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_plan_manual` decimal(15,2) DEFAULT NULL,
  `total_plan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_act` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_blc` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_status` enum('OPEN','PENDING','CLOSE') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `total_ar` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_sr` decimal(5,2) NOT NULL DEFAULT '0.00',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_shipping_delivery_detail`
--

CREATE TABLE `t_shipping_delivery_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `delivery_header_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam` tinyint UNSIGNED NOT NULL,
  `status` enum('OPEN','IN_TRANSIT','ARRIVED','DELIVERED','PENDING','CANCELLED','ADVANCED','NORMAL') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `lokasi_saat_ini` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `waktu_update` datetime DEFAULT NULL,
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `foto_bukti` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_shipping_delivery_header`
--

CREATE TABLE `t_shipping_delivery_header` (
  `id` bigint UNSIGNED NOT NULL,
  `periode` varchar(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kendaraan_id` bigint UNSIGNED NOT NULL,
  `driver_id` bigint UNSIGNED DEFAULT NULL,
  `destination` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_surat_jalan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_berangkat` date DEFAULT NULL,
  `waktu_berangkat` datetime DEFAULT NULL,
  `waktu_tiba` datetime DEFAULT NULL,
  `status` enum('OPEN','IN_TRANSIT','ARRIVED','DELIVERED','CANCELLED','ADVANCED','NORMAL','PENDING') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `total_trip` int NOT NULL DEFAULT '0',
  `total_delivered` int NOT NULL DEFAULT '0',
  `keterangan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_shipping_loading`
--

CREATE TABLE `t_shipping_loading` (
  `id` bigint UNSIGNED NOT NULL,
  `finish_good_out_id` bigint UNSIGNED NOT NULL,
  `waktu_loading` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ready',
  `kendaraan_id` bigint UNSIGNED DEFAULT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_spk`
--

CREATE TABLE `t_spk` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_spk` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `manpower_pembuat` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `plantgate_id` bigint UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `jam_berangkat_plan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Contoh: 09:00',
  `cycle` int NOT NULL DEFAULT '1',
  `no_surat_jalan` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_plat` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `driver_id` bigint UNSIGNED DEFAULT NULL,
  `model_part` enum('regular','ckd','cbu','rempart') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_spk_detail`
--

CREATE TABLE `t_spk_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `spk_id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED NOT NULL,
  `qty_packing_box` int NOT NULL DEFAULT '0',
  `jadwal_delivery_pcs` int NOT NULL DEFAULT '0',
  `jumlah_pulling_box` int NOT NULL DEFAULT '0',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_supply`
--

CREATE TABLE `t_supply` (
  `id` bigint UNSIGNED NOT NULL,
  `planning_run_id` bigint UNSIGNED NOT NULL,
  `part_id` bigint UNSIGNED DEFAULT NULL,
  `meja` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan` enum('inject','assy') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_supply` date NOT NULL,
  `shift_no` tinyint UNSIGNED DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DRAFT',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_supply_detail`
--

CREATE TABLE `t_supply_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `supply_id` bigint UNSIGNED NOT NULL,
  `presupply_detail_id` bigint UNSIGNED DEFAULT NULL,
  `receiving_detail_id` bigint UNSIGNED DEFAULT NULL,
  `mixing_id` bigint UNSIGNED DEFAULT NULL,
  `nomor_bahan_baku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` decimal(12,3) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_wip_in`
--

CREATE TABLE `t_wip_in` (
  `id` bigint UNSIGNED NOT NULL,
  `inject_out_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `box_number` int UNSIGNED DEFAULT NULL,
  `planning_run_id` bigint UNSIGNED DEFAULT NULL,
  `waktu_scan_in` datetime NOT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_wip_out`
--

CREATE TABLE `t_wip_out` (
  `id` bigint UNSIGNED NOT NULL,
  `wip_in_id` bigint UNSIGNED DEFAULT NULL,
  `inject_out_id` bigint UNSIGNED DEFAULT NULL,
  `lot_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `box_number` int UNSIGNED DEFAULT NULL,
  `planning_run_id` bigint UNSIGNED DEFAULT NULL,
  `waktu_scan_out` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `t_wip_out_detail`
--

CREATE TABLE `t_wip_out_detail` (
  `id` bigint UNSIGNED NOT NULL,
  `wip_out_id` bigint UNSIGNED DEFAULT NULL,
  `box_number` int UNSIGNED DEFAULT NULL,
  `waktu_scan` datetime NOT NULL,
  `catatan` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_superadmin` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `permission_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bb_receiving`
--
ALTER TABLE `bb_receiving`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bb_receiving_tanggal_receiving_index` (`tanggal_receiving`),
  ADD KEY `bb_receiving_supplier_id_index` (`supplier_id`);

--
-- Indexes for table `bb_receiving_detail`
--
ALTER TABLE `bb_receiving_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bb_receiving_detail_qrcode_unique` (`qrcode`),
  ADD KEY `bb_receiving_detail_receiving_id_index` (`receiving_id`),
  ADD KEY `bb_receiving_detail_nomor_bahan_baku_index` (`nomor_bahan_baku`),
  ADD KEY `bb_receiving_detail_lot_number_index` (`lot_number`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_bahanbaku`
--
ALTER TABLE `m_bahanbaku`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_nomor_bahan_baku_unique` (`nomor_bahan_baku`),
  ADD KEY `m_bahanbaku_kategori_index` (`kategori`),
  ADD KEY `m_bahanbaku_supplier_id_index` (`supplier_id`);

--
-- Indexes for table `m_bahanbaku_box`
--
ALTER TABLE `m_bahanbaku_box`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_box_bahan_baku_id_unique` (`bahan_baku_id`),
  ADD KEY `m_bahanbaku_box_bahan_baku_id_index` (`bahan_baku_id`);

--
-- Indexes for table `m_bahanbaku_layer`
--
ALTER TABLE `m_bahanbaku_layer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_layer_bahan_baku_id_unique` (`bahan_baku_id`),
  ADD KEY `m_bahanbaku_layer_bahan_baku_id_index` (`bahan_baku_id`);

--
-- Indexes for table `m_bahanbaku_material`
--
ALTER TABLE `m_bahanbaku_material`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_material_bahan_baku_id_unique` (`bahan_baku_id`),
  ADD KEY `m_bahanbaku_material_bahan_baku_id_index` (`bahan_baku_id`);

--
-- Indexes for table `m_bahanbaku_polybag`
--
ALTER TABLE `m_bahanbaku_polybag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_polybag_bahan_baku_id_unique` (`bahan_baku_id`),
  ADD KEY `m_bahanbaku_polybag_bahan_baku_id_index` (`bahan_baku_id`);

--
-- Indexes for table `m_bahanbaku_rempart`
--
ALTER TABLE `m_bahanbaku_rempart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_rempart_bahan_baku_id_unique` (`bahan_baku_id`),
  ADD KEY `m_bahanbaku_rempart_bahan_baku_id_index` (`bahan_baku_id`);

--
-- Indexes for table `m_bahanbaku_subpart`
--
ALTER TABLE `m_bahanbaku_subpart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_bahanbaku_subpart_bahan_baku_id_unique` (`bahan_baku_id`);

--
-- Indexes for table `m_kendaraan`
--
ALTER TABLE `m_kendaraan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_kendaraan_nopol_kendaraan_unique` (`nopol_kendaraan`),
  ADD KEY `m_kendaraan_nopol_kendaraan_index` (`nopol_kendaraan`),
  ADD KEY `m_kendaraan_status_kendaraan_index` (`status_kendaraan`);

--
-- Indexes for table `m_manpower`
--
ALTER TABLE `m_manpower`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_manpower_mp_id_unique` (`mp_id`);

--
-- Indexes for table `m_mesin`
--
ALTER TABLE `m_mesin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `m_mesin_no_mesin_unique` (`no_mesin`);

--
-- Indexes for table `m_mold`
--
ALTER TABLE `m_mold`
  ADD PRIMARY KEY (`id`),
  ADD KEY `m_mold_perusahaan_id_foreign` (`perusahaan_id`),
  ADD KEY `m_mold_part_id_foreign` (`part_id`);

--
-- Indexes for table `m_perusahaan`
--
ALTER TABLE `m_perusahaan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_plantgate`
--
ALTER TABLE `m_plantgate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pg_customer` (`customer_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_slug_unique` (`slug`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `sm_part`
--
ALTER TABLE `sm_part`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sm_part_customer_id_foreign` (`customer_id`),
  ADD KEY `sm_part_parent_part_id_foreign` (`parent_part_id`);

--
-- Indexes for table `sm_part_box`
--
ALTER TABLE `sm_part_box`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_box_part_id_urutan_unique` (`part_id`,`urutan`),
  ADD KEY `sm_part_box_part_id_index` (`part_id`),
  ADD KEY `sm_part_box_box_id_index` (`box_id`);

--
-- Indexes for table `sm_part_layer`
--
ALTER TABLE `sm_part_layer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_layer_part_id_layer_number_unique` (`part_id`,`layer_number`),
  ADD KEY `sm_part_layer_part_id_index` (`part_id`),
  ADD KEY `sm_part_layer_bahan_baku_id_foreign` (`bahan_baku_id`);

--
-- Indexes for table `sm_part_material`
--
ALTER TABLE `sm_part_material`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_material_part_id_material_type_urutan_unique` (`part_id`,`material_type`,`urutan`),
  ADD KEY `sm_part_material_part_id_index` (`part_id`),
  ADD KEY `sm_part_material_material_id_index` (`material_id`);

--
-- Indexes for table `sm_part_polybag`
--
ALTER TABLE `sm_part_polybag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_polybag_part_id_urutan_unique` (`part_id`,`urutan`),
  ADD KEY `sm_part_polybag_part_id_index` (`part_id`),
  ADD KEY `sm_part_polybag_bahan_baku_id_foreign` (`bahan_baku_id`);

--
-- Indexes for table `sm_part_rempart`
--
ALTER TABLE `sm_part_rempart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_rempart_part_id_urutan_unique` (`part_id`,`urutan`),
  ADD KEY `sm_part_rempart_r_polybag_id_foreign` (`R_Polybag_id`),
  ADD KEY `sm_part_rempart_r_gasket_duplex_id_foreign` (`R_Gasket_Duplex_id`),
  ADD KEY `sm_part_rempart_r_foam_sheet_id_foreign` (`R_Foam_Sheet_id`),
  ADD KEY `sm_part_rempart_r_hologram_id_foreign` (`R_Hologram_id`),
  ADD KEY `sm_part_rempart_r_labela_id_foreign` (`R_LabelA_id`),
  ADD KEY `sm_part_rempart_r_labelb_id_foreign` (`R_LabelB_id`),
  ADD KEY `sm_part_rempart_part_id_index` (`part_id`);

--
-- Indexes for table `sm_part_subpart`
--
ALTER TABLE `sm_part_subpart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sm_part_subpart_part_id_urutan_unique` (`part_id`,`urutan`),
  ADD KEY `sm_part_subpart_subpart_id_foreign` (`subpart_id`),
  ADD KEY `sm_part_subpart_part_id_index` (`part_id`);

--
-- Indexes for table `sm_plantgate_part`
--
ALTER TABLE `sm_plantgate_part`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_assy_in`
--
ALTER TABLE `t_assy_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assyin_wipout` (`wip_out_id`),
  ADD KEY `fk_assyin_sd` (`supply_detail_id`),
  ADD KEY `fk_assyin_part` (`part_id`);

--
-- Indexes for table `t_assy_out`
--
ALTER TABLE `t_assy_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_assyout_assyin` (`assy_in_id`),
  ADD KEY `idx_assyout_lot` (`lot_number`),
  ADD KEY `idx_assyout_part` (`part_id`),
  ADD KEY `idx_assyout_waktu` (`waktu_scan`);

--
-- Indexes for table `t_finishgood_in`
--
ALTER TABLE `t_finishgood_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fgin_assyout` (`assy_out_id`),
  ADD KEY `idx_fgin_lot` (`lot_number`),
  ADD KEY `idx_fgin_part` (`part_id`),
  ADD KEY `idx_fgin_waktu` (`waktu_scan`);

--
-- Indexes for table `t_finishgood_out`
--
ALTER TABLE `t_finishgood_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fgout_fgin` (`finish_good_in_id`),
  ADD KEY `fk_fgout_spk` (`spk_id`),
  ADD KEY `fk_fgout_part` (`part_id`);

--
-- Indexes for table `t_gps_logs`
--
ALTER TABLE `t_gps_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_gps_logs_delivery_header_id_recorded_at_index` (`delivery_header_id`,`recorded_at`);

--
-- Indexes for table `t_inject_in`
--
ALTER TABLE `t_inject_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_injectin_sd` (`supply_detail_id`),
  ADD KEY `fk_injectin_pr` (`planning_run_id`),
  ADD KEY `fk_injectin_mesin` (`mesin_id`);

--
-- Indexes for table `t_inject_out`
--
ALTER TABLE `t_inject_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_injectout_injectin` (`inject_in_id`),
  ADD KEY `fk_injectout_pr` (`planning_run_id`);

--
-- Indexes for table `t_inject_out_detail`
--
ALTER TABLE `t_inject_out_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_injectoutdetail_injectout` (`inject_out_id`),
  ADD KEY `idx_injectoutdetail_box` (`box_number`),
  ADD KEY `idx_injectoutdetail_waktu` (`waktu_scan`),
  ADD KEY `idx_injectoutdetail_out_box` (`inject_out_id`,`box_number`);

--
-- Indexes for table `t_planning_day`
--
ALTER TABLE `t_planning_day`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_day_tanggal_mesin_id_unique` (`tanggal`,`mesin_id`),
  ADD KEY `t_planning_day_tanggal_index` (`tanggal`),
  ADD KEY `t_planning_day_mesin_id_index` (`mesin_id`);

--
-- Indexes for table `t_planning_material_order_produksi`
--
ALTER TABLE `t_planning_material_order_produksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pmo_prm` (`planning_run_material_id`);

--
-- Indexes for table `t_planning_run`
--
ALTER TABLE `t_planning_run`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_planning_run_planning_day_id_index` (`planning_day_id`),
  ADD KEY `t_planning_run_mold_id_index` (`mold_id`),
  ADD KEY `t_planning_run_start_at_index` (`start_at`),
  ADD KEY `t_planning_run_part_id_foreign` (`part_id`);

--
-- Indexes for table `t_planning_run_hourly_actual`
--
ALTER TABLE `t_planning_run_hourly_actual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_run_hourly_actual_planning_run_id_hour_start_unique` (`planning_run_id`,`hour_start`),
  ADD KEY `t_planning_run_hourly_actual_hour_start_index` (`hour_start`);

--
-- Indexes for table `t_planning_run_hourly_target`
--
ALTER TABLE `t_planning_run_hourly_target`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_run_hourly_target_planning_run_id_hour_start_unique` (`planning_run_id`,`hour_start`),
  ADD KEY `t_planning_run_hourly_target_hour_start_index` (`hour_start`);

--
-- Indexes for table `t_planning_run_kebutuhan`
--
ALTER TABLE `t_planning_run_kebutuhan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_run_kebutuhan_planning_run_id_unique` (`planning_run_id`);

--
-- Indexes for table `t_planning_run_material`
--
ALTER TABLE `t_planning_run_material`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_run_material_planning_run_id_material_id_unique` (`planning_run_id`,`material_id`),
  ADD KEY `t_planning_run_material_material_id_index` (`material_id`);

--
-- Indexes for table `t_planning_run_material_shift`
--
ALTER TABLE `t_planning_run_material_shift`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prm_shift` (`planning_run_material_id`,`shift_no`);

--
-- Indexes for table `t_planning_run_subpart`
--
ALTER TABLE `t_planning_run_subpart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_planning_run_subpart_planning_run_id_partsubpart_id_unique` (`planning_run_id`,`partsubpart_id`),
  ADD KEY `t_planning_run_subpart_partsubpart_id_index` (`partsubpart_id`);

--
-- Indexes for table `t_planning_run_subpart_shift`
--
ALTER TABLE `t_planning_run_subpart_shift`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_prsp_shift` (`planning_run_subpart_id`,`shift_no`);

--
-- Indexes for table `t_schedule_detail`
--
ALTER TABLE `t_schedule_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_detail_date` (`schedule_header_id`,`tanggal`),
  ADD KEY `t_schedule_detail_schedule_header_id_index` (`schedule_header_id`),
  ADD KEY `t_schedule_detail_tanggal_index` (`tanggal`),
  ADD KEY `t_schedule_detail_po_number_index` (`po_number`),
  ADD KEY `t_schedule_detail_pc_status_index` (`pc_status`);

--
-- Indexes for table `t_schedule_header`
--
ALTER TABLE `t_schedule_header`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_schedule` (`periode`,`supplier_id`,`bahan_baku_id`,`po_number`),
  ADD KEY `t_schedule_header_periode_index` (`periode`),
  ADD KEY `t_schedule_header_supplier_id_index` (`supplier_id`),
  ADD KEY `t_schedule_header_bahan_baku_id_index` (`bahan_baku_id`),
  ADD KEY `t_schedule_header_po_number_index` (`po_number`),
  ADD KEY `t_schedule_header_total_status_index` (`total_status`);

--
-- Indexes for table `t_shipping_delivery_detail`
--
ALTER TABLE `t_shipping_delivery_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_delivery_hour` (`delivery_header_id`,`tanggal`,`jam`),
  ADD KEY `t_shipping_delivery_detail_delivery_header_id_index` (`delivery_header_id`),
  ADD KEY `t_shipping_delivery_detail_tanggal_index` (`tanggal`),
  ADD KEY `t_shipping_delivery_detail_jam_index` (`jam`),
  ADD KEY `t_shipping_delivery_detail_status_index` (`status`),
  ADD KEY `t_shipping_delivery_detail_tanggal_jam_index` (`tanggal`,`jam`);

--
-- Indexes for table `t_shipping_delivery_header`
--
ALTER TABLE `t_shipping_delivery_header`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `t_shipping_loading`
--
ALTER TABLE `t_shipping_loading`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_shipping_loading_finish_good_out_id_index` (`finish_good_out_id`),
  ADD KEY `t_shipping_loading_kendaraan_id_index` (`kendaraan_id`),
  ADD KEY `t_shipping_loading_waktu_loading_index` (`waktu_loading`),
  ADD KEY `t_shipping_loading_status_index` (`status`);

--
-- Indexes for table `t_spk`
--
ALTER TABLE `t_spk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `t_spk_nomor_spk_unique` (`nomor_spk`),
  ADD KEY `idx_spk_customer` (`customer_id`),
  ADD KEY `idx_spk_plantgate` (`plantgate_id`),
  ADD KEY `idx_spk_tanggal` (`tanggal`),
  ADD KEY `idx_spk_nomor` (`nomor_spk`),
  ADD KEY `t_spk_driver_id_foreign` (`driver_id`);

--
-- Indexes for table `t_spk_detail`
--
ALTER TABLE `t_spk_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_spkd_spk` (`spk_id`),
  ADD KEY `idx_spkd_part` (`part_id`);

--
-- Indexes for table `t_supply`
--
ALTER TABLE `t_supply`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_supply_part_id_foreign` (`part_id`);

--
-- Indexes for table `t_supply_detail`
--
ALTER TABLE `t_supply_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `t_supply_detail_supply_id_foreign` (`supply_id`);

--
-- Indexes for table `t_wip_in`
--
ALTER TABLE `t_wip_in`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wipin_injectout` (`inject_out_id`),
  ADD KEY `fk_wipin_pr` (`planning_run_id`);

--
-- Indexes for table `t_wip_out`
--
ALTER TABLE `t_wip_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wipout_wipin` (`wip_in_id`),
  ADD KEY `fk_wipout_injectout` (`inject_out_id`),
  ADD KEY `fk_wipout_pr` (`planning_run_id`);

--
-- Indexes for table `t_wip_out_detail`
--
ALTER TABLE `t_wip_out_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_wipoutdetail_wipout` (`wip_out_id`),
  ADD KEY `idx_wipoutdetail_box` (`box_number`),
  ADD KEY `idx_wipoutdetail_waktu` (`waktu_scan`),
  ADD KEY `idx_wipoutdetail_out_box` (`wip_out_id`,`box_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_user_id_unique` (`user_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_permissions_user_id_permission_id_unique` (`user_id`,`permission_id`),
  ADD KEY `user_permissions_permission_id_foreign` (`permission_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bb_receiving`
--
ALTER TABLE `bb_receiving`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bb_receiving_detail`
--
ALTER TABLE `bb_receiving_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `m_bahanbaku`
--
ALTER TABLE `m_bahanbaku`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_box`
--
ALTER TABLE `m_bahanbaku_box`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_layer`
--
ALTER TABLE `m_bahanbaku_layer`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_material`
--
ALTER TABLE `m_bahanbaku_material`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_polybag`
--
ALTER TABLE `m_bahanbaku_polybag`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_rempart`
--
ALTER TABLE `m_bahanbaku_rempart`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_bahanbaku_subpart`
--
ALTER TABLE `m_bahanbaku_subpart`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_kendaraan`
--
ALTER TABLE `m_kendaraan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_manpower`
--
ALTER TABLE `m_manpower`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_mesin`
--
ALTER TABLE `m_mesin`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_mold`
--
ALTER TABLE `m_mold`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_perusahaan`
--
ALTER TABLE `m_perusahaan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `m_plantgate`
--
ALTER TABLE `m_plantgate`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part`
--
ALTER TABLE `sm_part`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_box`
--
ALTER TABLE `sm_part_box`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_layer`
--
ALTER TABLE `sm_part_layer`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_material`
--
ALTER TABLE `sm_part_material`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_polybag`
--
ALTER TABLE `sm_part_polybag`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_rempart`
--
ALTER TABLE `sm_part_rempart`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_part_subpart`
--
ALTER TABLE `sm_part_subpart`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sm_plantgate_part`
--
ALTER TABLE `sm_plantgate_part`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_assy_in`
--
ALTER TABLE `t_assy_in`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_assy_out`
--
ALTER TABLE `t_assy_out`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_finishgood_in`
--
ALTER TABLE `t_finishgood_in`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_finishgood_out`
--
ALTER TABLE `t_finishgood_out`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_gps_logs`
--
ALTER TABLE `t_gps_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_inject_in`
--
ALTER TABLE `t_inject_in`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_inject_out`
--
ALTER TABLE `t_inject_out`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_inject_out_detail`
--
ALTER TABLE `t_inject_out_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_day`
--
ALTER TABLE `t_planning_day`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_material_order_produksi`
--
ALTER TABLE `t_planning_material_order_produksi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run`
--
ALTER TABLE `t_planning_run`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_hourly_actual`
--
ALTER TABLE `t_planning_run_hourly_actual`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_hourly_target`
--
ALTER TABLE `t_planning_run_hourly_target`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_kebutuhan`
--
ALTER TABLE `t_planning_run_kebutuhan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_material`
--
ALTER TABLE `t_planning_run_material`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_material_shift`
--
ALTER TABLE `t_planning_run_material_shift`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_subpart`
--
ALTER TABLE `t_planning_run_subpart`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_planning_run_subpart_shift`
--
ALTER TABLE `t_planning_run_subpart_shift`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_schedule_detail`
--
ALTER TABLE `t_schedule_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_schedule_header`
--
ALTER TABLE `t_schedule_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_shipping_delivery_detail`
--
ALTER TABLE `t_shipping_delivery_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_shipping_delivery_header`
--
ALTER TABLE `t_shipping_delivery_header`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_shipping_loading`
--
ALTER TABLE `t_shipping_loading`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_spk`
--
ALTER TABLE `t_spk`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_spk_detail`
--
ALTER TABLE `t_spk_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_supply`
--
ALTER TABLE `t_supply`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_supply_detail`
--
ALTER TABLE `t_supply_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_wip_in`
--
ALTER TABLE `t_wip_in`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_wip_out`
--
ALTER TABLE `t_wip_out`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `t_wip_out_detail`
--
ALTER TABLE `t_wip_out_detail`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_permissions`
--
ALTER TABLE `user_permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bb_receiving`
--
ALTER TABLE `bb_receiving`
  ADD CONSTRAINT `bb_receiving_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bb_receiving_detail`
--
ALTER TABLE `bb_receiving_detail`
  ADD CONSTRAINT `bb_receiving_detail_nomor_bahan_baku_foreign` FOREIGN KEY (`nomor_bahan_baku`) REFERENCES `m_bahanbaku` (`nomor_bahan_baku`) ON DELETE SET NULL,
  ADD CONSTRAINT `bb_receiving_detail_receiving_id_foreign` FOREIGN KEY (`receiving_id`) REFERENCES `bb_receiving` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku`
--
ALTER TABLE `m_bahanbaku`
  ADD CONSTRAINT `m_bahanbaku_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `m_bahanbaku_box`
--
ALTER TABLE `m_bahanbaku_box`
  ADD CONSTRAINT `m_bahanbaku_box_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku_layer`
--
ALTER TABLE `m_bahanbaku_layer`
  ADD CONSTRAINT `m_bahanbaku_layer_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku_material`
--
ALTER TABLE `m_bahanbaku_material`
  ADD CONSTRAINT `m_bahanbaku_material_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku_polybag`
--
ALTER TABLE `m_bahanbaku_polybag`
  ADD CONSTRAINT `m_bahanbaku_polybag_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku_rempart`
--
ALTER TABLE `m_bahanbaku_rempart`
  ADD CONSTRAINT `m_bahanbaku_rempart_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_bahanbaku_subpart`
--
ALTER TABLE `m_bahanbaku_subpart`
  ADD CONSTRAINT `m_bahanbaku_subpart_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `m_mold`
--
ALTER TABLE `m_mold`
  ADD CONSTRAINT `m_mold_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `m_mold_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `m_plantgate`
--
ALTER TABLE `m_plantgate`
  ADD CONSTRAINT `m_plantgate_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sm_part`
--
ALTER TABLE `sm_part`
  ADD CONSTRAINT `sm_part_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sm_part_parent_part_id_foreign` FOREIGN KEY (`parent_part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sm_part_box`
--
ALTER TABLE `sm_part_box`
  ADD CONSTRAINT `sm_part_box_box_id_foreign` FOREIGN KEY (`box_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_box_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sm_part_layer`
--
ALTER TABLE `sm_part_layer`
  ADD CONSTRAINT `sm_part_layer_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_layer_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sm_part_material`
--
ALTER TABLE `sm_part_material`
  ADD CONSTRAINT `sm_part_material_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_material_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sm_part_polybag`
--
ALTER TABLE `sm_part_polybag`
  ADD CONSTRAINT `sm_part_polybag_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_polybag_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sm_part_rempart`
--
ALTER TABLE `sm_part_rempart`
  ADD CONSTRAINT `sm_part_rempart_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sm_part_rempart_r_foam_sheet_id_foreign` FOREIGN KEY (`R_Foam_Sheet_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_rempart_r_gasket_duplex_id_foreign` FOREIGN KEY (`R_Gasket_Duplex_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_rempart_r_hologram_id_foreign` FOREIGN KEY (`R_Hologram_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_rempart_r_labela_id_foreign` FOREIGN KEY (`R_LabelA_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_rempart_r_labelb_id_foreign` FOREIGN KEY (`R_LabelB_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sm_part_rempart_r_polybag_id_foreign` FOREIGN KEY (`R_Polybag_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sm_part_subpart`
--
ALTER TABLE `sm_part_subpart`
  ADD CONSTRAINT `sm_part_subpart_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sm_part_subpart_subpart_id_foreign` FOREIGN KEY (`subpart_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_assy_in`
--
ALTER TABLE `t_assy_in`
  ADD CONSTRAINT `fk_assyin_part` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assyin_sd` FOREIGN KEY (`supply_detail_id`) REFERENCES `t_supply_detail` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assyin_wipout` FOREIGN KEY (`wip_out_id`) REFERENCES `t_wip_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t_assy_out`
--
ALTER TABLE `t_assy_out`
  ADD CONSTRAINT `fk_assyout_assyin` FOREIGN KEY (`assy_in_id`) REFERENCES `t_assy_in` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assyout_part` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_assy_out_assy_in_id_foreign` FOREIGN KEY (`assy_in_id`) REFERENCES `t_assy_in` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_assy_out_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_finishgood_in`
--
ALTER TABLE `t_finishgood_in`
  ADD CONSTRAINT `fk_fgin_assyout` FOREIGN KEY (`assy_out_id`) REFERENCES `t_assy_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fgin_part` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_finishgood_in_assy_out_id_foreign` FOREIGN KEY (`assy_out_id`) REFERENCES `t_assy_out` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_finishgood_in_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_finishgood_out`
--
ALTER TABLE `t_finishgood_out`
  ADD CONSTRAINT `fk_fgout_fgin` FOREIGN KEY (`finish_good_in_id`) REFERENCES `t_finishgood_in` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fgout_part` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fgout_spk` FOREIGN KEY (`spk_id`) REFERENCES `t_spk` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_finishgood_out_finish_good_in_id_foreign` FOREIGN KEY (`finish_good_in_id`) REFERENCES `t_finishgood_in` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_gps_logs`
--
ALTER TABLE `t_gps_logs`
  ADD CONSTRAINT `t_gps_logs_delivery_header_id_foreign` FOREIGN KEY (`delivery_header_id`) REFERENCES `t_shipping_delivery_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_inject_in`
--
ALTER TABLE `t_inject_in`
  ADD CONSTRAINT `fk_injectin_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `m_mesin` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_injectin_pr` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_injectin_sd` FOREIGN KEY (`supply_detail_id`) REFERENCES `t_supply_detail` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t_inject_out`
--
ALTER TABLE `t_inject_out`
  ADD CONSTRAINT `fk_injectout_injectin` FOREIGN KEY (`inject_in_id`) REFERENCES `t_inject_in` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_injectout_pr` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `t_inject_out_detail`
--
ALTER TABLE `t_inject_out_detail`
  ADD CONSTRAINT `fk_injectoutdetail_injectout` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_inject_out_detail_inject_out_id_foreign` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_planning_day`
--
ALTER TABLE `t_planning_day`
  ADD CONSTRAINT `t_planning_day_mesin_id_foreign` FOREIGN KEY (`mesin_id`) REFERENCES `m_mesin` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_planning_material_order_produksi`
--
ALTER TABLE `t_planning_material_order_produksi`
  ADD CONSTRAINT `fk_pmo_prm` FOREIGN KEY (`planning_run_material_id`) REFERENCES `t_planning_run_material` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `t_planning_run`
--
ALTER TABLE `t_planning_run`
  ADD CONSTRAINT `t_planning_run_mold_id_foreign` FOREIGN KEY (`mold_id`) REFERENCES `m_mold` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_planning_run_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_planning_run_planning_day_id_foreign` FOREIGN KEY (`planning_day_id`) REFERENCES `t_planning_day` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `t_planning_run_hourly_actual`
--
ALTER TABLE `t_planning_run_hourly_actual`
  ADD CONSTRAINT `t_planning_run_hourly_actual_planning_run_id_foreign` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_hourly_target`
--
ALTER TABLE `t_planning_run_hourly_target`
  ADD CONSTRAINT `t_planning_run_hourly_target_planning_run_id_foreign` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_kebutuhan`
--
ALTER TABLE `t_planning_run_kebutuhan`
  ADD CONSTRAINT `t_planning_run_kebutuhan_planning_run_id_foreign` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_material`
--
ALTER TABLE `t_planning_run_material`
  ADD CONSTRAINT `t_planning_run_material_material_id_foreign` FOREIGN KEY (`material_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `t_planning_run_material_planning_run_id_foreign` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_material_shift`
--
ALTER TABLE `t_planning_run_material_shift`
  ADD CONSTRAINT `t_planning_run_material_shift_planning_run_material_id_foreign` FOREIGN KEY (`planning_run_material_id`) REFERENCES `t_planning_run_material` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_subpart`
--
ALTER TABLE `t_planning_run_subpart`
  ADD CONSTRAINT `t_planning_run_subpart_partsubpart_id_foreign` FOREIGN KEY (`partsubpart_id`) REFERENCES `sm_part_subpart` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `t_planning_run_subpart_planning_run_id_foreign` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_planning_run_subpart_shift`
--
ALTER TABLE `t_planning_run_subpart_shift`
  ADD CONSTRAINT `t_planning_run_subpart_shift_planning_run_subpart_id_foreign` FOREIGN KEY (`planning_run_subpart_id`) REFERENCES `t_planning_run_subpart` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_schedule_detail`
--
ALTER TABLE `t_schedule_detail`
  ADD CONSTRAINT `t_schedule_detail_schedule_header_id_foreign` FOREIGN KEY (`schedule_header_id`) REFERENCES `t_schedule_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_schedule_header`
--
ALTER TABLE `t_schedule_header`
  ADD CONSTRAINT `t_schedule_header_bahan_baku_id_foreign` FOREIGN KEY (`bahan_baku_id`) REFERENCES `m_bahanbaku` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_schedule_header_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_shipping_delivery_detail`
--
ALTER TABLE `t_shipping_delivery_detail`
  ADD CONSTRAINT `t_shipping_delivery_detail_delivery_header_id_foreign` FOREIGN KEY (`delivery_header_id`) REFERENCES `t_shipping_delivery_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_shipping_loading`
--
ALTER TABLE `t_shipping_loading`
  ADD CONSTRAINT `t_shipping_loading_finish_good_out_id_foreign` FOREIGN KEY (`finish_good_out_id`) REFERENCES `t_finishgood_out` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `t_shipping_loading_kendaraan_id_foreign` FOREIGN KEY (`kendaraan_id`) REFERENCES `m_kendaraan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_spk`
--
ALTER TABLE `t_spk`
  ADD CONSTRAINT `t_spk_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `m_perusahaan` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `t_spk_driver_id_foreign` FOREIGN KEY (`driver_id`) REFERENCES `m_manpower` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_spk_plantgate_id_foreign` FOREIGN KEY (`plantgate_id`) REFERENCES `m_plantgate` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `t_spk_detail`
--
ALTER TABLE `t_spk_detail`
  ADD CONSTRAINT `t_spk_detail_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `t_spk_detail_spk_id_foreign` FOREIGN KEY (`spk_id`) REFERENCES `t_spk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_supply`
--
ALTER TABLE `t_supply`
  ADD CONSTRAINT `t_supply_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `sm_part` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_supply_detail`
--
ALTER TABLE `t_supply_detail`
  ADD CONSTRAINT `t_supply_detail_supply_id_foreign` FOREIGN KEY (`supply_id`) REFERENCES `t_supply` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `t_wip_in`
--
ALTER TABLE `t_wip_in`
  ADD CONSTRAINT `fk_wipin_injectout` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wipin_pr` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_wip_in_inject_out_id_foreign` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_wip_out`
--
ALTER TABLE `t_wip_out`
  ADD CONSTRAINT `fk_wipout_injectout` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wipout_pr` FOREIGN KEY (`planning_run_id`) REFERENCES `t_planning_run` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_wipout_wipin` FOREIGN KEY (`wip_in_id`) REFERENCES `t_wip_in` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `t_wip_out_inject_out_id_foreign` FOREIGN KEY (`inject_out_id`) REFERENCES `t_inject_out` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `t_wip_out_wip_in_id_foreign` FOREIGN KEY (`wip_in_id`) REFERENCES `t_wip_in` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `t_wip_out_detail`
--
ALTER TABLE `t_wip_out_detail`
  ADD CONSTRAINT `fk_wipoutdetail_wipout` FOREIGN KEY (`wip_out_id`) REFERENCES `t_wip_out` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_permissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
