-- Local development bootstrap SQL for A Plus Digitizing
-- Includes legacy blank schema, phase-two portal additions, localhost site domains, and a default admin user.

/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.4.10-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: digixjhl_1dollar
-- ------------------------------------------------------
-- Server version	11.4.10-MariaDB-cll-lve

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `advancepayment`
--

DROP TABLE IF EXISTS `advancepayment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `advancepayment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `advance_pay` varchar(11) NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6144 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `attach_files`
--

DROP TABLE IF EXISTS `attach_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `attach_files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_name_with_date` varchar(255) DEFAULT NULL,
  `file_name_with_order_id` varchar(255) DEFAULT NULL,
  `file_source` varchar(150) DEFAULT NULL,
  `date_added` datetime DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=595517 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing`
--

DROP TABLE IF EXISTS `billing`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing` (
  `user_id` bigint(11) NOT NULL,
  `order_id` bigint(11) NOT NULL,
  `bill_id` int(11) NOT NULL AUTO_INCREMENT,
  `approved` text DEFAULT NULL,
  `amount` text DEFAULT NULL,
  `earned_amount` varchar(255) NOT NULL,
  `payment` text DEFAULT NULL,
  `approve_date` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `transid` text DEFAULT NULL,
  `trandtime` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  `website` varchar(30) NOT NULL DEFAULT '1dollar',
  `payer_id` varchar(100) DEFAULT NULL,
  `is_paid` int(11) NOT NULL,
  `is_advance` int(11) NOT NULL,
  PRIMARY KEY (`bill_id`),
  KEY `payer_id` (`payer_id`),
  KEY `user_id` (`user_id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=100708 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `billing_credit`
--

DROP TABLE IF EXISTS `billing_credit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `billing_credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` varchar(255) NOT NULL,
  `transid` varchar(255) NOT NULL,
  `total_billing` varchar(255) NOT NULL,
  `credit_points` varchar(255) NOT NULL,
  `submitdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `block_ip`
--

DROP TABLE IF EXISTS `block_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `block_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ipaddress` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `decription` varchar(255) NOT NULL,
  `attached_file` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `end_date` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `cartid` bigint(11) NOT NULL AUTO_INCREMENT,
  `sessionid` varchar(255) DEFAULT NULL,
  `designid` int(11) DEFAULT NULL,
  PRIMARY KEY (`cartid`),
  KEY `sessionid` (`sessionid`),
  KEY `designid` (`designid`)
) ENGINE=InnoDB AUTO_INCREMENT=481104 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comethrough`
--

DROP TABLE IF EXISTS `comethrough`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comethrough` (
  `Seq_No` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(30) NOT NULL,
  `user_password` varchar(30) NOT NULL,
  `effective_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  PRIMARY KEY (`Seq_No`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `source_page` varchar(150) DEFAULT NULL,
  `comment_source` varchar(150) DEFAULT NULL,
  `date_added` datetime DEFAULT '0000-00-00 00:00:00',
  `date_modified` datetime DEFAULT '0000-00-00 00:00:00',
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=230357 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `earned_credit`
--

DROP TABLE IF EXISTS `earned_credit`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `earned_credit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `refre_id` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `credit` varchar(255) NOT NULL,
  `status` enum('add','del') DEFAULT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=587 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `login_history`
--

DROP TABLE IF EXISTS `login_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `IP_Address` varchar(50) NOT NULL,
  `Login_Name` varchar(100) NOT NULL,
  `Password` varchar(100) NOT NULL,
  `Status` varchar(100) NOT NULL,
  `Date_Added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=372676 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `order_num` varchar(255) NOT NULL,
  `design_name` text DEFAULT NULL,
  `format` text DEFAULT NULL,
  `fabric_type` text DEFAULT NULL,
  `sew_out` text DEFAULT NULL,
  `width` text DEFAULT NULL,
  `height` text DEFAULT NULL,
  `measurement` text DEFAULT NULL,
  `no_of_colors` bigint(20) DEFAULT 0,
  `color_names` text DEFAULT NULL,
  `appliques` text DEFAULT NULL,
  `no_of_appliques` bigint(20) DEFAULT 0,
  `applique_colors` text DEFAULT NULL,
  `starting_point` text DEFAULT NULL,
  `comments1` text DEFAULT NULL,
  `comments2` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `stitches_price` decimal(10,2) DEFAULT 0.00,
  `total_amount` varchar(20) DEFAULT '0.00',
  `turn_around_time` text DEFAULT NULL,
  `submit_date` datetime DEFAULT '0000-00-00 00:00:00',
  `modified_date` datetime DEFAULT '0000-00-00 00:00:00',
  `completion_date` datetime DEFAULT '0000-00-00 00:00:00',
  `assigned_date` datetime DEFAULT '0000-00-00 00:00:00',
  `vender_complete_date` datetime DEFAULT '0000-00-00 00:00:00',
  `stitches` text DEFAULT NULL,
  `assign_to` bigint(20) DEFAULT 0,
  `subject` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `order_type` varchar(150) DEFAULT 'order',
  `order_status` varchar(25) NOT NULL,
  `advance_pay` enum('0','1') NOT NULL DEFAULT '0',
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  `website` varchar(30) NOT NULL DEFAULT '1dollar',
  `notes_by_user` int(11) NOT NULL,
  `notes_by_admin` int(11) NOT NULL,
  `sent` varchar(255) DEFAULT 'Normal',
  `working` varchar(50) NOT NULL,
  `del_attachment` int(11) NOT NULL DEFAULT 0,
  `type` enum('digitizing','vector','quote') NOT NULL DEFAULT 'quote',
  PRIMARY KEY (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=110896 DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tbl_payment`
--

DROP TABLE IF EXISTS `tbl_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tbl_payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `item_number` varchar(255) NOT NULL,
  `amount` double(10,2) NOT NULL,
  `currency_code` varchar(55) NOT NULL,
  `txn_id` varchar(255) NOT NULL,
  `payment_status` varchar(255) NOT NULL,
  `payment_response` text NOT NULL,
  `create_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `team_comments`
--

DROP TABLE IF EXISTS `team_comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_comments` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) DEFAULT NULL,
  `comments1` varchar(255) DEFAULT NULL,
  `comments2` varchar(255) DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_types`
--

DROP TABLE IF EXISTS `user_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usercomments`
--

DROP TABLE IF EXISTS `usercomments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usercomments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `digi_comment` text NOT NULL,
  `digi_image` varchar(255) NOT NULL,
  `vector_comment` text NOT NULL,
  `vector_image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=66 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(150) DEFAULT NULL,
  `user_password` varchar(50) DEFAULT NULL,
  `security_key` varchar(255) NOT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `company_type` varchar(150) DEFAULT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `alternate_email` varchar(255) NOT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `zip_code` varchar(20) DEFAULT NULL,
  `user_city` varchar(150) DEFAULT NULL,
  `user_country` varchar(150) DEFAULT NULL,
  `user_phone` varchar(150) DEFAULT NULL,
  `user_fax` varchar(150) DEFAULT NULL,
  `contact_person` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `normal_fee` decimal(10,2) DEFAULT 1.00,
  `middle_fee` decimal(10,2) NOT NULL DEFAULT 1.50,
  `urgent_fee` decimal(10,2) DEFAULT 1.50,
  `super_fee` decimal(10,2) NOT NULL,
  `payment_terms` int(5) DEFAULT 7,
  `usre_type_id` int(11) DEFAULT 1,
  `date_added` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `max_num_stiches` int(11) NOT NULL DEFAULT 0,
  `customer_approval_limit` decimal(12,2) NOT NULL DEFAULT 25.00,
  `single_approval_limit` decimal(12,2) NOT NULL DEFAULT 15.00,
  `customer_pending_order_limit` int(11) NOT NULL DEFAULT 3,
  `userip_addrs` varchar(25) NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  `website` varchar(30) NOT NULL DEFAULT '1dollar',
  `digitzing_format` varchar(255) NOT NULL,
  `vertor_format` varchar(255) NOT NULL,
  `topup` varchar(255) NOT NULL,
  `exist_customer` enum('0','1') NOT NULL DEFAULT '0',
  `user_term` varchar(255) NOT NULL,
  `package_type` varchar(255) NOT NULL,
  `real_user` varchar(255) NOT NULL,
  `ref_code` varchar(255) NOT NULL,
  `ref_code_other` varchar(255) NOT NULL,
  `register_by` varchar(255) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4352 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_1dollor`
--

DROP TABLE IF EXISTS `users_1dollor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users_1dollor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emailaddress` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3691 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-03-20 11:27:14


-- Phase-two platform additions

-- Unified Laravel release rollout for A Plus Digitizing

-- Generated from individual SQL scripts in documented install order.

-- ==================================================================
-- Begin: sites.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `sites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `legacy_key` varchar(30) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand_name` varchar(150) NOT NULL,
  `primary_domain` varchar(255) DEFAULT NULL,
  `website_address` varchar(255) DEFAULT NULL,
  `support_email` varchar(255) DEFAULT NULL,
  `from_email` varchar(255) DEFAULT NULL,
  `timezone` varchar(100) NOT NULL DEFAULT 'UTC',
  `pricing_strategy` varchar(50) NOT NULL DEFAULT 'customer_rate',
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `settings_json` mediumtext DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sites_legacy_key_unique` (`legacy_key`),
  UNIQUE KEY `sites_slug_unique` (`slug`),
  KEY `sites_primary_idx` (`is_primary`),
  KEY `sites_active_idx` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: sites.sql

-- ==================================================================
-- Begin: site_domains.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `site_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `host` varchar(255) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `site_domains_host_unique` (`host`),
  KEY `site_domains_site_id_idx` (`site_id`),
  KEY `site_domains_primary_idx` (`is_primary`),
  CONSTRAINT `site_domains_site_id_fk` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: site_domains.sql

-- ==================================================================
-- Begin: site_pricing_profiles.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `site_pricing_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `profile_name` varchar(150) NOT NULL,
  `work_type` varchar(50) NOT NULL DEFAULT 'digitizing',
  `turnaround_code` varchar(50) DEFAULT NULL,
  `pricing_mode` varchar(50) NOT NULL DEFAULT 'customer_rate',
  `fixed_price` decimal(12,2) DEFAULT NULL,
  `per_thousand_rate` decimal(12,4) DEFAULT NULL,
  `minimum_charge` decimal(12,2) DEFAULT NULL,
  `included_units` decimal(12,2) DEFAULT NULL,
  `overage_rate` decimal(12,4) DEFAULT NULL,
  `package_name` varchar(150) DEFAULT NULL,
  `config_json` mediumtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_pricing_profiles_site_id_idx` (`site_id`),
  KEY `site_pricing_profiles_lookup_idx` (`site_id`,`work_type`,`turnaround_code`,`is_active`),
  CONSTRAINT `site_pricing_profiles_site_id_fk` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: site_pricing_profiles.sql

-- ==================================================================
-- Begin: site_promotions.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `site_promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `promotion_name` varchar(150) NOT NULL,
  `promotion_code` varchar(100) DEFAULT NULL,
  `work_type` varchar(50) DEFAULT NULL,
  `discount_type` varchar(50) NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(12,2) NOT NULL DEFAULT 0.00,
  `minimum_order_amount` decimal(12,2) DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `config_json` mediumtext DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_promotions_site_id_idx` (`site_id`),
  KEY `site_promotions_active_idx` (`site_id`,`is_active`,`starts_at`,`ends_at`),
  CONSTRAINT `site_promotions_site_id_fk` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: site_promotions.sql

-- ==================================================================
-- Begin: site_promotion_claims.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `site_promotion_claims` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned NOT NULL,
  `site_promotion_id` bigint unsigned NOT NULL,
  `user_id` bigint NOT NULL,
  `website` varchar(30) NOT NULL DEFAULT '1dollar',
  `status` varchar(50) NOT NULL DEFAULT 'pending_verification',
  `verification_required` tinyint(1) NOT NULL DEFAULT 1,
  `verified_at` datetime DEFAULT NULL,
  `payment_required` tinyint(1) NOT NULL DEFAULT 0,
  `required_payment_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `first_order_flat_amount` decimal(12,2) DEFAULT NULL,
  `offer_snapshot_json` mediumtext DEFAULT NULL,
  `payment_transaction_id` bigint unsigned DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `redeemed_order_id` bigint DEFAULT NULL,
  `redeemed_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `site_promotion_claims_site_id_idx` (`site_id`),
  KEY `site_promotion_claims_promotion_id_idx` (`site_promotion_id`),
  KEY `site_promotion_claims_user_status_idx` (`user_id`,`status`),
  KEY `site_promotion_claims_payment_reference_idx` (`payment_reference`),
  KEY `site_promotion_claims_txn_idx` (`payment_transaction_id`),
  CONSTRAINT `site_promotion_claims_site_id_fk` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`),
  CONSTRAINT `site_promotion_claims_promotion_id_fk` FOREIGN KEY (`site_promotion_id`) REFERENCES `site_promotions` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: site_promotion_claims.sql

-- ==================================================================
-- Begin: customer_activation_tokens.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `customer_activation_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `site_legacy_key` varchar(100) NOT NULL,
  `customer_user_id` bigint unsigned NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_activation_selector_unique` (`selector`),
  KEY `customer_activation_site_user_idx` (`site_legacy_key`, `customer_user_id`),
  KEY `customer_activation_expires_idx` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: customer_activation_tokens.sql

-- ==================================================================
-- Begin: customer_password_reset_tokens.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `customer_password_reset_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `site_legacy_key` varchar(100) NOT NULL,
  `customer_user_id` bigint unsigned NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_password_reset_selector_unique` (`selector`),
  KEY `customer_password_reset_site_user_idx` (`site_legacy_key`, `customer_user_id`),
  KEY `customer_password_reset_expires_idx` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: customer_password_reset_tokens.sql

-- ==================================================================
-- Begin: customer_remember_tokens.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `customer_remember_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `site_legacy_key` varchar(100) NOT NULL,
  `customer_user_id` bigint unsigned NOT NULL,
  `selector` varchar(32) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_remember_selector_unique` (`selector`),
  KEY `customer_remember_site_user_idx` (`site_legacy_key`, `customer_user_id`),
  KEY `customer_remember_expires_idx` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: customer_remember_tokens.sql

-- ==================================================================
-- Begin: customer_credit_ledger.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `customer_credit_ledger` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `billing_id` bigint(20) DEFAULT NULL,
  `order_id` bigint(20) DEFAULT NULL,
  `website` varchar(30) NOT NULL DEFAULT '1dollar',
  `entry_type` varchar(50) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `reference_no` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `billing_id` (`billing_id`),
  KEY `order_id` (`order_id`),
  KEY `website` (`website`),
  KEY `entry_type` (`entry_type`),
  KEY `date_added` (`date_added`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- End: customer_credit_ledger.sql

-- ==================================================================
-- Begin: payment_transactions.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint NOT NULL,
  `order_id` bigint DEFAULT NULL,
  `billing_id` bigint DEFAULT NULL,
  `legacy_website` varchar(30) NOT NULL DEFAULT '1dollar',
  `provider` varchar(50) NOT NULL,
  `provider_transaction_id` varchar(255) DEFAULT NULL,
  `merchant_reference` varchar(255) NOT NULL,
  `payment_scope` varchar(50) NOT NULL DEFAULT 'single_order',
  `status` varchar(50) NOT NULL DEFAULT 'initiated',
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `requested_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `confirmed_amount` decimal(12,2) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `return_url` varchar(500) DEFAULT NULL,
  `failure_reason` text DEFAULT NULL,
  `provider_payload` mediumtext DEFAULT NULL,
  `reconciled_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_transactions_merchant_reference_unique` (`merchant_reference`),
  KEY `payment_transactions_site_id_idx` (`site_id`),
  KEY `payment_transactions_user_id_idx` (`user_id`),
  KEY `payment_transactions_order_id_idx` (`order_id`),
  KEY `payment_transactions_provider_txn_idx` (`provider`,`provider_transaction_id`),
  KEY `payment_transactions_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: payment_transactions.sql

-- ==================================================================
-- Begin: payment_transaction_items.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `payment_transaction_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_transaction_id` bigint unsigned NOT NULL,
  `billing_id` bigint DEFAULT NULL,
  `order_id` bigint DEFAULT NULL,
  `user_id` bigint NOT NULL,
  `legacy_website` varchar(30) NOT NULL DEFAULT '1dollar',
  `requested_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `confirmed_amount` decimal(12,2) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'initiated',
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_transaction_items_txn_idx` (`payment_transaction_id`),
  KEY `payment_transaction_items_billing_idx` (`billing_id`),
  KEY `payment_transaction_items_order_idx` (`order_id`),
  KEY `payment_transaction_items_user_idx` (`user_id`),
  KEY `payment_transaction_items_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: payment_transaction_items.sql

-- ==================================================================
-- Begin: payment_provider_events.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `payment_provider_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `payment_transaction_id` bigint unsigned DEFAULT NULL,
  `provider` varchar(50) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `event_reference` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `payload` mediumtext NOT NULL,
  `received_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_provider_events_site_id_idx` (`site_id`),
  KEY `payment_provider_events_txn_idx` (`payment_transaction_id`),
  KEY `payment_provider_events_provider_idx` (`provider`,`event_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: payment_provider_events.sql

-- ==================================================================
-- Begin: quote_negotiations.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `quote_negotiations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint NOT NULL,
  `customer_user_id` bigint NOT NULL,
  `legacy_website` varchar(30) NOT NULL DEFAULT '1dollar',
  `status` varchar(50) NOT NULL DEFAULT 'pending_admin_review',
  `customer_reason_code` varchar(100) DEFAULT NULL,
  `customer_reason_text` text DEFAULT NULL,
  `customer_target_amount` decimal(12,2) DEFAULT NULL,
  `quoted_amount` decimal(12,2) DEFAULT NULL,
  `admin_counter_amount` decimal(12,2) DEFAULT NULL,
  `admin_note` text DEFAULT NULL,
  `resolved_by_user_id` bigint DEFAULT NULL,
  `resolved_by_name` varchar(150) DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quote_negotiations_site_id_idx` (`site_id`),
  KEY `quote_negotiations_order_id_idx` (`order_id`),
  KEY `quote_negotiations_customer_idx` (`customer_user_id`),
  KEY `quote_negotiations_status_idx` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: quote_negotiations.sql

-- ==================================================================
-- Begin: order_workflow_meta.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `order_workflow_meta` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) NOT NULL,
  `created_source` varchar(30) NOT NULL DEFAULT 'customer',
  `historical_backfill` tinyint(1) NOT NULL DEFAULT 0,
  `suppress_customer_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `delivery_override` varchar(20) NOT NULL DEFAULT 'auto',
  `order_credit_limit` decimal(12,2) DEFAULT NULL,
  `created_by_user_id` bigint(20) DEFAULT NULL,
  `created_by_name` varchar(150) DEFAULT NULL,
  `date_added` datetime NOT NULL,
  `date_modified` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_workflow_meta_order_id_unique` (`order_id`),
  KEY `order_workflow_meta_created_source_idx` (`created_source`),
  KEY `order_workflow_meta_end_date_idx` (`end_date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;


-- End: order_workflow_meta.sql

-- ==================================================================
-- Begin: email_templates.sql
-- ==================================================================

CREATE TABLE `email_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(150) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` mediumtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email_templates_is_active_idx` (`is_active`),
  KEY `email_templates_name_idx` (`template_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: email_templates.sql

-- ==================================================================
-- Begin: security_audit_events.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `security_audit_events` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `event_type` varchar(80) NOT NULL,
  `severity` varchar(20) NOT NULL,
  `portal` varchar(30) NOT NULL,
  `site_legacy_key` varchar(100) DEFAULT NULL,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `actor_login` varchar(150) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `request_path` varchar(255) DEFAULT NULL,
  `request_method` varchar(10) NOT NULL,
  `message` varchar(255) NOT NULL,
  `details_json` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `security_audit_events_type_idx` (`event_type`),
  KEY `security_audit_events_severity_idx` (`severity`),
  KEY `security_audit_events_portal_idx` (`portal`),
  KEY `security_audit_events_site_idx` (`site_legacy_key`),
  KEY `security_audit_events_actor_idx` (`actor_user_id`),
  KEY `security_audit_events_ip_idx` (`ip_address`),
  KEY `security_audit_events_created_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: security_audit_events.sql

-- ==================================================================
-- Begin: admin_login_attempts.sql
-- ==================================================================

CREATE TABLE `admin_login_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `login_name` varchar(100) NOT NULL,
  `matched_user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `request_path` varchar(255) DEFAULT NULL,
  `attempt_outcome` varchar(30) NOT NULL,
  `status` varchar(150) NOT NULL,
  `is_rate_limited` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_login_attempts_login_name_idx` (`login_name`),
  KEY `admin_login_attempts_ip_address_idx` (`ip_address`),
  KEY `admin_login_attempts_attempted_at_idx` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- End: admin_login_attempts.sql

-- ==================================================================
-- Begin: supervisor_team_members.sql
-- ==================================================================

CREATE TABLE IF NOT EXISTS `supervisor_team_members` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `supervisor_user_id` bigint(20) NOT NULL,
  `member_user_id` bigint(20) NOT NULL,
  `date_added` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `deleted_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `supervisor_user_id` (`supervisor_user_id`),
  KEY `member_user_id` (`member_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

INSERT IGNORE INTO `user_types` (`id`, `user_type`)
VALUES (4, 'Supervisor');


-- End: supervisor_team_members.sql

-- ==================================================================
-- Begin: performance_indexes.sql
-- ==================================================================



DROP PROCEDURE IF EXISTS add_index_if_missing;
DELIMITER $$

CREATE PROCEDURE add_index_if_missing(
    IN p_table_name VARCHAR(128),
    IN p_index_name VARCHAR(128),
    IN p_statement TEXT
)
BEGIN
    IF EXISTS (
        SELECT 1
        FROM information_schema.tables
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
    ) AND NOT EXISTS (
        SELECT 1
        FROM information_schema.statistics
        WHERE table_schema = DATABASE()
          AND table_name = p_table_name
          AND index_name = p_index_name
    ) THEN
        SET @ddl = p_statement;
        PREPARE stmt FROM @ddl;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Core admin tables used heavily by the Laravel app.
CALL add_index_if_missing(
    'orders',
    'idx_orders_work_queue',
    'ALTER TABLE `orders` ADD INDEX `idx_orders_work_queue` (`end_date`, `order_type`, `status`(20), `assign_to`, `order_id`)'
);

CALL add_index_if_missing(
    'orders',
    'idx_orders_completion_board',
    'ALTER TABLE `orders` ADD INDEX `idx_orders_completion_board` (`end_date`, `status`(20), `assign_to`, `completion_date`, `order_id`)'
);

CALL add_index_if_missing(
    'orders',
    'idx_orders_customer_lookup',
    'ALTER TABLE `orders` ADD INDEX `idx_orders_customer_lookup` (`user_id`, `end_date`, `order_id`)'
);

CALL add_index_if_missing(
    'billing',
    'idx_billing_due_received',
    'ALTER TABLE `billing` ADD INDEX `idx_billing_due_received` (`end_date`, `approved`(8), `payment`(8), `user_id`, `website`, `bill_id`)'
);

CALL add_index_if_missing(
    'billing',
    'idx_billing_order_status',
    'ALTER TABLE `billing` ADD INDEX `idx_billing_order_status` (`order_id`, `end_date`, `approved`(8), `payment`(8))'
);

CALL add_index_if_missing(
    'billing',
    'idx_billing_order_paid',
    'ALTER TABLE `billing` ADD INDEX `idx_billing_order_paid` (`order_id`, `is_paid`)'
);

CALL add_index_if_missing(
    'users',
    'idx_users_admin_login',
    'ALTER TABLE `users` ADD INDEX `idx_users_admin_login` (`usre_type_id`, `user_name`, `is_active`, `end_date`)'
);

CALL add_index_if_missing(
    'users',
    'idx_users_type_status',
    'ALTER TABLE `users` ADD INDEX `idx_users_type_status` (`usre_type_id`, `is_active`, `end_date`, `real_user`, `user_id`)'
);

CALL add_index_if_missing(
    'users',
    'idx_users_email',
    'ALTER TABLE `users` ADD INDEX `idx_users_email` (`user_email`)'
);

CALL add_index_if_missing(
    'attach_files',
    'idx_attach_files_order_source',
    'ALTER TABLE `attach_files` ADD INDEX `idx_attach_files_order_source` (`order_id`, `file_source`, `end_date`, `id`)'
);

CALL add_index_if_missing(
    'attach_files',
    'idx_attach_files_order_source_name',
    'ALTER TABLE `attach_files` ADD INDEX `idx_attach_files_order_source_name` (`order_id`, `file_source`, `file_name_with_date`)'
);

CALL add_index_if_missing(
    'comments',
    'idx_comments_order_source',
    'ALTER TABLE `comments` ADD INDEX `idx_comments_order_source` (`order_id`, `comment_source`, `end_date`, `id`)'
);

CALL add_index_if_missing(
    'comments',
    'idx_comments_order_page',
    'ALTER TABLE `comments` ADD INDEX `idx_comments_order_page` (`order_id`, `source_page`, `end_date`, `id`)'
);

CALL add_index_if_missing(
    'advancepayment',
    'idx_advancepayment_order_status',
    'ALTER TABLE `advancepayment` ADD INDEX `idx_advancepayment_order_status` (`order_id`, `status`)'
);

CALL add_index_if_missing(
    'login_history',
    'idx_login_history_ip_date',
    'ALTER TABLE `login_history` ADD INDEX `idx_login_history_ip_date` (`IP_Address`, `Date_Added`)'
);

CALL add_index_if_missing(
    'login_history',
    'idx_login_history_date',
    'ALTER TABLE `login_history` ADD INDEX `idx_login_history_date` (`Date_Added`)'
);

CALL add_index_if_missing(
    'block_ip',
    'idx_block_ip_ipaddress',
    'ALTER TABLE `block_ip` ADD UNIQUE INDEX `idx_block_ip_ipaddress` (`ipaddress`)'
);

CALL add_index_if_missing(
    'blogs',
    'idx_blogs_end_date',
    'ALTER TABLE `blogs` ADD INDEX `idx_blogs_end_date` (`end_date`, `id`)'
);

-- Optional tables used by the Laravel admin when they exist in your live database.
CALL add_index_if_missing(
    'customerpayments',
    'idx_customerpayments_active_effective',
    'ALTER TABLE `customerpayments` ADD INDEX `idx_customerpayments_active_effective` (`End_Date`, `Effective_Date`)'
);

CALL add_index_if_missing(
    'customerpayments',
    'idx_customerpayments_active_website',
    'ALTER TABLE `customerpayments` ADD INDEX `idx_customerpayments_active_website` (`End_Date`, `Website`, `Effective_Date`)'
);

CALL add_index_if_missing(
    'qucik_quote_users',
    'idx_qucik_quote_users_customer_oid',
    'ALTER TABLE `qucik_quote_users` ADD INDEX `idx_qucik_quote_users_customer_oid` (`customer_oid`)'
);

DROP PROCEDURE IF EXISTS add_index_if_missing;

-- Helpful checks after running:
-- SHOW INDEX FROM orders;
-- SHOW INDEX FROM billing;
-- SHOW INDEX FROM users;
-- SHOW INDEX FROM attach_files;
-- SHOW INDEX FROM comments;


-- End: performance_indexes.sql

-- ==================================================================
-- Begin: phase_two_safe_columns.sql
-- ==================================================================

SET @schema_name = DATABASE();

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `website`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'users' AND INDEX_NAME = 'users_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD KEY `users_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `orders` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `website`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND INDEX_NAME = 'orders_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `orders` ADD KEY `orders_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'billing' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `billing` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `website`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'billing' AND INDEX_NAME = 'billing_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `billing` ADD KEY `billing_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customerpayments' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `customerpayments` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `Website`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customerpayments' AND INDEX_NAME = 'customerpayments_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `customerpayments` ADD KEY `customerpayments_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customer_credit_ledger' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `customer_credit_ledger` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `website`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'customer_credit_ledger' AND INDEX_NAME = 'customer_credit_ledger_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `customer_credit_ledger` ADD KEY `customer_credit_ledger_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_workflow_meta' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `order_workflow_meta` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `order_id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'order_workflow_meta' AND INDEX_NAME = 'order_workflow_meta_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `order_workflow_meta` ADD KEY `order_workflow_meta_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'email_templates' AND COLUMN_NAME = 'site_id'
  ),
  'SELECT 1',
  'ALTER TABLE `email_templates` ADD COLUMN `site_id` bigint unsigned DEFAULT NULL AFTER `id`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'email_templates' AND INDEX_NAME = 'email_templates_site_id_idx'
  ),
  'SELECT 1',
  'ALTER TABLE `email_templates` ADD KEY `email_templates_site_id_idx` (`site_id`)'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `users` u
INNER JOIN `sites` s ON s.`legacy_key` = u.`website`
SET u.`site_id` = s.`id`
WHERE u.`site_id` IS NULL;

UPDATE `orders` o
INNER JOIN `sites` s ON s.`legacy_key` = o.`website`
SET o.`site_id` = s.`id`
WHERE o.`site_id` IS NULL;

UPDATE `billing` b
INNER JOIN `sites` s ON s.`legacy_key` = b.`website`
SET b.`site_id` = s.`id`
WHERE b.`site_id` IS NULL;

UPDATE `customerpayments` cp
INNER JOIN `sites` s ON s.`legacy_key` = cp.`Website`
SET cp.`site_id` = s.`id`
WHERE cp.`site_id` IS NULL;

UPDATE `customer_credit_ledger` ccl
INNER JOIN `sites` s ON s.`legacy_key` = ccl.`website`
SET ccl.`site_id` = s.`id`
WHERE ccl.`site_id` IS NULL;


-- End: phase_two_safe_columns.sql

-- ==================================================================
-- Begin: phase_two_primary_site_backfill.sql
-- ==================================================================

SET @primary_legacy_key = '1dollar';
SET @primary_legacy_key_binary = BINARY '1dollar';
SET @primary_site_id = (
  SELECT `id`
  FROM `sites`
  WHERE BINARY `legacy_key` = @primary_legacy_key_binary
  LIMIT 1
);

UPDATE `users`
SET `website` = @primary_legacy_key
WHERE (`website` IS NULL OR `website` = '' OR BINARY `website` <> @primary_legacy_key_binary);

UPDATE `orders`
SET `website` = @primary_legacy_key
WHERE (`website` IS NULL OR `website` = '' OR BINARY `website` <> @primary_legacy_key_binary);

UPDATE `billing`
SET `website` = @primary_legacy_key
WHERE (`website` IS NULL OR `website` = '' OR BINARY `website` <> @primary_legacy_key_binary);

UPDATE `customerpayments`
SET `Website` = @primary_legacy_key
WHERE (`Website` IS NULL OR `Website` = '' OR BINARY `Website` <> @primary_legacy_key_binary);

UPDATE `customer_credit_ledger`
SET `website` = @primary_legacy_key
WHERE (`website` IS NULL OR `website` = '' OR BINARY `website` <> @primary_legacy_key_binary);

UPDATE `users`
SET `site_id` = @primary_site_id
WHERE @primary_site_id IS NOT NULL
  AND (`site_id` IS NULL OR `site_id` = 0 OR `site_id` <> @primary_site_id);

UPDATE `orders`
SET `site_id` = @primary_site_id
WHERE @primary_site_id IS NOT NULL
  AND (`site_id` IS NULL OR `site_id` = 0 OR `site_id` <> @primary_site_id);

UPDATE `billing`
SET `site_id` = @primary_site_id
WHERE @primary_site_id IS NOT NULL
  AND (`site_id` IS NULL OR `site_id` = 0 OR `site_id` <> @primary_site_id);

UPDATE `customerpayments`
SET `site_id` = @primary_site_id
WHERE @primary_site_id IS NOT NULL
  AND (`site_id` IS NULL OR `site_id` = 0 OR `site_id` <> @primary_site_id);

UPDATE `customer_credit_ledger`
SET `site_id` = @primary_site_id
WHERE @primary_site_id IS NOT NULL
  AND (`site_id` IS NULL OR `site_id` = 0 OR `site_id` <> @primary_site_id);


-- End: phase_two_primary_site_backfill.sql

-- ==================================================================
-- Begin: phase_two_primary_site_seed.sql
-- ==================================================================

INSERT INTO `sites` (
  `legacy_key`,
  `slug`,
  `name`,
  `brand_name`,
  `primary_domain`,
  `website_address`,
  `support_email`,
  `from_email`,
  `timezone`,
  `pricing_strategy`,
  `is_primary`,
  `is_active`,
  `settings_json`,
  `created_at`,
  `updated_at`
)
SELECT
  '1dollar',
  '1dollar',
  'A Plus Digitizing',
  'A Plus Digitizing',
  'aplusdigitising.com',
  'https://aplusdigitising.com',
  'contact@aplusdigitising.com',
  'contact@aplusdigitising.com',
  'America/Detroit',
  'site_profile',
  1,
  1,
  NULL,
  NOW(),
  NOW()
WHERE NOT EXISTS (
  SELECT 1
  FROM `sites`
  WHERE `legacy_key` = '1dollar'
);

INSERT INTO `site_domains` (
  `site_id`,
  `host`,
  `is_primary`,
  `is_active`,
  `created_at`,
  `updated_at`
)
SELECT
  `id`,
  'aplusdigitising.com',
  1,
  1,
  NOW(),
  NOW()
FROM `sites`
WHERE `legacy_key` = '1dollar'
  AND NOT EXISTS (
    SELECT 1
    FROM `site_domains`
    WHERE `host` = 'aplusdigitising.com'
  );

UPDATE `sites`
SET `slug` = '1dollar',
    `name` = 'A Plus Digitizing',
    `brand_name` = 'A Plus Digitizing',
    `primary_domain` = 'aplusdigitising.com',
    `website_address` = 'https://aplusdigitising.com',
    `support_email` = 'contact@aplusdigitising.com',
    `from_email` = 'contact@aplusdigitising.com',
    `timezone` = 'America/Detroit',
    `pricing_strategy` = 'site_profile',
    `is_primary` = 1,
    `is_active` = 1,
    `updated_at` = NOW()
WHERE `legacy_key` = '1dollar';

INSERT INTO `site_pricing_profiles` (
  `site_id`,
  `profile_name`,
  `work_type`,
  `turnaround_code`,
  `pricing_mode`,
  `fixed_price`,
  `per_thousand_rate`,
  `minimum_charge`,
  `included_units`,
  `overage_rate`,
  `package_name`,
  `config_json`,
  `is_active`,
  `created_at`,
  `updated_at`
)
SELECT
  s.`id`,
  seed.`profile_name`,
  seed.`work_type`,
  seed.`turnaround_code`,
  seed.`pricing_mode`,
  seed.`fixed_price`,
  seed.`per_thousand_rate`,
  seed.`minimum_charge`,
  seed.`included_units`,
  seed.`overage_rate`,
  seed.`package_name`,
  seed.`config_json`,
  1,
  NOW(),
  NOW()
FROM `sites` s
JOIN (
  SELECT 'Digitizing Standard' AS `profile_name`, 'digitizing' AS `work_type`, 'standard' AS `turnaround_code`, 'per_thousand' AS `pricing_mode`, NULL AS `fixed_price`, 1.0000 AS `per_thousand_rate`, 6.00 AS `minimum_charge`, NULL AS `included_units`, NULL AS `overage_rate`, NULL AS `package_name`, NULL AS `config_json`
  UNION ALL
  SELECT 'Digitizing Priority', 'digitizing', 'priority', 'per_thousand', NULL, 1.5000, 9.00, NULL, NULL, NULL, NULL
  UNION ALL
  SELECT 'Digitizing Super Rush', 'digitizing', 'superrush', 'per_thousand', NULL, 2.0000, 12.00, NULL, NULL, NULL, NULL
  UNION ALL
  SELECT 'Vector Standard', 'vector', 'standard', 'fixed_price', 6.00, NULL, NULL, NULL, 6.0000, NULL, NULL
) seed
WHERE s.`legacy_key` = '1dollar'
  AND NOT EXISTS (
    SELECT 1
    FROM `site_pricing_profiles` existing
    WHERE existing.`site_id` = s.`id`
      AND existing.`profile_name` = seed.`profile_name`
  );


-- End: phase_two_primary_site_seed.sql

-- ==================================================================
-- Begin: site_payment_settings.sql
-- ==================================================================

SET @site_payment_provider_column_exists := (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'sites'
      AND COLUMN_NAME = 'active_payment_provider'
);

SET @site_payment_provider_add_sql := IF(
    @site_payment_provider_column_exists = 0,
    'ALTER TABLE `sites` ADD COLUMN `active_payment_provider` varchar(50) NULL AFTER `website_address`',
    'SELECT 1'
);

PREPARE stmt FROM @site_payment_provider_add_sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

UPDATE `sites`
SET `active_payment_provider` = '2checkout_hosted'
WHERE BINARY COALESCE(`legacy_key`, '') = '1dollar';


-- End: site_payment_settings.sql

-- ==================================================================
-- Begin: system_email_templates_seed.sql
-- ==================================================================

SET @primary_site_id = (
  SELECT `id`
  FROM `sites`
  WHERE BINARY `legacy_key` = BINARY '1dollar'
  LIMIT 1
);

INSERT INTO `email_templates` (
  `site_id`,
  `template_name`,
  `subject`,
  `body`,
  `is_active`,
  `created_by`,
  `updated_by`,
  `created_at`,
  `updated_at`
)
SELECT
  @primary_site_id,
  seed.`template_name`,
  seed.`subject`,
  seed.`body`,
  1,
  'system',
  'system',
  NOW(),
  NOW()
FROM (
  SELECT
    'Customer Account Activation' AS `template_name`,
    'Activate your account - {{site_label}}' AS `subject`,
    '<p>Hello {{customer_name}},</p><p>Thank you for creating an account with {{site_label}}.</p><p>Please activate your account using the link below:</p><p><a href="{{activation_url}}">Activate Account</a></p><p>If the button does not open, use this link:</p><p>{{activation_url}}</p><p>If you need help, contact us at {{support_email}}.</p><p>Thank you,<br>{{site_label}}</p>' AS `body`
  UNION ALL
  SELECT
    'Customer Password Reset',
    'Reset your password - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received a request to reset your password for {{site_label}}.</p><p>You can set a new password using the link below:</p><p><a href="{{reset_url}}">Reset Password</a></p><p>This link expires on {{expires_at}}.</p><p>If you did not request this change, you can safely ignore this email.</p><p>If you need help, contact us at {{support_email}}.</p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Digitizing Order Confirmation',
    'Your digitizing order has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your digitizing order and it is now in our workflow.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{orders_url}}">View My Orders</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Vector Order Confirmation',
    'Your vector order has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your vector order and it is now in our workflow.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{orders_url}}">View My Orders</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Digitizing Quote Confirmation',
    'Your digitizing quote request has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your digitizing quote request and our team will review it shortly.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{quotes_url}}">View My Quotes</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Vector Quote Confirmation',
    'Your vector quote request has been received - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>We received your vector quote request and our team will review it shortly.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Format:</strong> {{format}}</p><p><strong>Turnaround:</strong> {{turnaround}}</p><p>You can review the latest status in your account here:</p><p><a href="{{quotes_url}}">View My Quotes</a></p><p>Thank you,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Order Completed',
    'Your order with {{site_label}} has been completed',
    '<p>Hello {{customer_name}},</p><p>Your order with {{site_label}} has been completed.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p>Please review it in your account using the link below:</p><p><a href="{{review_url}}">Review Completed Order</a></p><p><strong>DISCLAIMER:</strong> Please conduct a test run and verify the sample against your design before proceeding with production. aplusdigitizing.com is not responsible for any damage to materials incurred during use. Designs are provided for lawful use only. The recipient assumes all responsibility for ensuring reproduction rights and maintaining compliance with intellectual property laws.</p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quote Completed',
    'Your quote from {{site_label}} is ready',
    '<p>Hello {{customer_name}},</p><p>Your quote from {{site_label}} is ready for review.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p>You can review it in your account using the link below:</p><p><a href="{{review_url}}">Review Quote</a></p><p><strong>PLEASE NOTE:</strong> This quotation is a preliminary estimate only. Final pricing may vary up to +/- 10% based on final design output. Should the cost exceed this range, we will notify you for approval prior to proceeding.</p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quick Quote Completed',
    'Your quick quote from {{site_label}} is ready',
    '<p>Hello {{customer_name}},</p><p>Your quick quote from {{site_label}} is ready.</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Amount:</strong> {{amount}}</p><p>You can review and complete payment using the link below:</p><p><a href="{{payment_url}}">Review Quick Quote</a></p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
  UNION ALL
  SELECT
    'Customer Quote Negotiation Response',
    'Your quote request has been reviewed - {{site_label}}',
    '<p>Hello {{customer_name}},</p><p>{{message}}</p><p><strong>Reference ID:</strong> {{order_id}}</p><p><strong>Design Name:</strong> {{design_name}}</p><p><strong>Current Amount:</strong> {{amount}}</p><p>You can review the latest quote status here:</p><p><a href="{{review_url}}">Review Quote</a></p><p>If you need help, contact us at {{support_email}}.</p><p>Kind regards,<br>{{site_label}}</p>'
) AS seed
WHERE NOT EXISTS (
  SELECT 1
  FROM `email_templates` existing
  WHERE existing.`template_name` = seed.`template_name`
    AND (
      existing.`site_id` = @primary_site_id
      OR (@primary_site_id IS NULL AND existing.`site_id` IS NULL)
    )
);

-- End: system_email_templates_seed.sql

-- ==================================================================
-- Begin: phase_two_legacy_datetime_normalization.sql
-- ==================================================================

SET @schema_name = DATABASE();

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'assigned_date'
  ),
  'ALTER TABLE `orders` MODIFY `assigned_date` datetime NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'vender_complete_date'
  ),
  'ALTER TABLE `orders` MODIFY `vender_complete_date` datetime NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'completion_date'
  ),
  'ALTER TABLE `orders` MODIFY `completion_date` datetime NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'submit_date'
  ),
  'ALTER TABLE `orders` MODIFY `submit_date` datetime NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'modified_date'
  ),
  'ALTER TABLE `orders` MODIFY `modified_date` datetime NULL DEFAULT NULL',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- End: phase_two_legacy_datetime_normalization.sql

-- ==================================================================
-- Begin: phase_two_password_security.sql
-- ==================================================================

SET @schema_name = DATABASE();

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password_hash'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `password_hash` varchar(255) DEFAULT NULL AFTER `user_password`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(
  EXISTS (
    SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = @schema_name AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password_migrated_at'
  ),
  'SELECT 1',
  'ALTER TABLE `users` ADD COLUMN `password_migrated_at` datetime DEFAULT NULL AFTER `password_hash`'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- End: phase_two_password_security.sql



-- Local development helpers
INSERT INTO `site_domains` (`site_id`,`host`,`is_primary`,`is_active`,`created_at`,`updated_at`)
SELECT `id`, 'localhost', 0, 1, NOW(), NOW()
FROM `sites`
WHERE `legacy_key` = '1dollar'
  AND NOT EXISTS (
    SELECT 1 FROM `site_domains` WHERE `host` = 'localhost'
  );

INSERT INTO `site_domains` (`site_id`,`host`,`is_primary`,`is_active`,`created_at`,`updated_at`)
SELECT `id`, '127.0.0.1', 0, 1, NOW(), NOW()
FROM `sites`
WHERE `legacy_key` = '1dollar'
  AND NOT EXISTS (
    SELECT 1 FROM `site_domains` WHERE `host` = '127.0.0.1'
  );

INSERT INTO `users` (
  `user_name`,
  `user_password`,
  `password_hash`,
  `password_migrated_at`,
  `security_key`,
  `first_name`,
  `last_name`,
  `company`,
  `company_type`,
  `user_email`,
  `alternate_email`,
  `company_address`,
  `zip_code`,
  `user_city`,
  `user_country`,
  `user_phone`,
  `user_fax`,
  `contact_person`,
  `is_active`,
  `normal_fee`,
  `middle_fee`,
  `urgent_fee`,
  `super_fee`,
  `payment_terms`,
  `usre_type_id`,
  `date_added`,
  `max_num_stiches`,
  `customer_approval_limit`,
  `single_approval_limit`,
  `customer_pending_order_limit`,
  `userip_addrs`,
  `end_date`,
  `deleted_by`,
  `website`,
  `site_id`,
  `digitzing_format`,
  `vertor_format`,
  `topup`,
  `exist_customer`,
  `user_term`,
  `package_type`,
  `real_user`,
  `ref_code`,
  `ref_code_other`,
  `register_by`
)
SELECT
  'admin',
  '',
  '$2y$12$yvfLe4.qqhnTnSnIwHXjDuGkriN6t3UDoPuzXZasaEJLNcRsbkEAG',
  NOW(),
  'dev-admin-login',
  'Local',
  'Admin',
  '',
  '',
  'admin@example.test',
  '',
  '',
  '',
  '',
  'United States',
  '',
  '',
  '',
  1,
  1.00,
  1.50,
  1.50,
  2.00,
  7,
  3,
  NOW(),
  0,
  25.00,
  15.00,
  3,
  '127.0.0.1',
  NULL,
  NULL,
  '1dollar',
  (SELECT `id` FROM `sites` WHERE `legacy_key` = '1dollar' LIMIT 1),
  'dst',
  'ai',
  '0',
  '0',
  '',
  '',
  '1',
  '',
  '',
  'local_setup'
WHERE NOT EXISTS (
  SELECT 1 FROM `users` WHERE `user_name` = 'admin' AND `usre_type_id` = 3
);
