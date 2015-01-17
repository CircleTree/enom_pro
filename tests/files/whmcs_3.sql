-- MySQL dump 10.13  Distrib 5.5.38, for osx10.6 (i386)
--
-- Host: localhost    Database: whmcs_3
-- ------------------------------------------------------
-- Server version	5.5.38

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tbladdonmodules`
--

DROP TABLE IF EXISTS `tbladdonmodules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tbladdonmodules` (
  `module` text NOT NULL,
  `setting` text NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tbladdonmodules`
--

LOCK TABLES `tbladdonmodules` WRITE;
/*!40000 ALTER TABLE `tbladdonmodules` DISABLE KEYS */;
INSERT INTO `tbladdonmodules` VALUES ('super_quotes','debug',''),('license_cleanup','version','1.0'),('google_analytics','version','1.0'),('google_analytics','code','UA-66666'),('google_analytics','domain',''),('google_analytics','access','1'),('license_cleanup','access','1'),('enom_pro','spinner_net','on'),('enom_pro','spinner_com','on'),('enom_pro','spinner_css','on'),('enom_pro','spinner_checkout','on'),('enom_pro','version','2.1.31'),('enom_pro','quicklink',''),('enom_pro','save',''),('enom_pro','license','enom-pro-dev-24e76c34ae8f7584'),('enom_pro','api_request_limit','10'),('enom_pro','client_limit','Unlimited'),('enom_pro','balance_warning','50'),('enom_pro','debug',''),('enom_pro','beta',''),('enom_pro','import_section',''),('enom_pro','import_per_page','25'),('enom_pro','auto_activate','on'),('enom_pro','next_due_date','-3 Days'),('enom_pro','pricing_years','10'),('enom_pro','pricing_per_page','25'),('enom_pro','pricing_retail',''),('enom_pro','exchange_rate_provider','google'),('enom_pro','custom-exchange-rate',''),('enom_pro','exchange-rate-api-key',''),('enom_pro','ssl_section',''),('enom_pro','ssl_days','30'),('enom_pro','ssl_email_enabled',''),('enom_pro','ssl_email_days','30'),('enom_pro','ssl_open_ticket','Disabled'),('enom_pro','ssl_ticket_priority','Low'),('enom_pro','ssl_ticket_subject','Expiring SSL Certificate'),('enom_pro','ssl_ticket_message','We have opened a ticket to renew {$product} for {$domain_name}, which  is set to expire on {$expiry_date}. Our staff will help you get your certificate renewed.'),('enom_pro','ssl_ticket_email_enabled',''),('enom_pro','ssl_ticket_default_name',''),('enom_pro','ssl_ticket_default_email',''),('enom_pro','spinner_section',''),('enom_pro','spinner_results','10'),('enom_pro','spinner_columns','3'),('enom_pro','spinner_sortby','score'),('enom_pro','spinner_sort_order','Descending'),('enom_pro','cart_css_class','btn-primary'),('enom_pro','custom_cart_css_class',''),('enom_pro','spinner_animation','Medium'),('enom_pro','spinner_tv',''),('enom_pro','spinner_cc',''),('enom_pro','spinner_hyphens',''),('enom_pro','spinner_numbers',''),('enom_pro','spinner_sensitive',''),('enom_pro','spinner_basic','Medium'),('enom_pro','spinner_related','High'),('enom_pro','spinner_similiar','Medium'),('enom_pro','spinner_topical','High'),('enom_pro','save2',''),('enom_pro','quicklink2',''),('enom_pro','access','1'),('enom_pro','version','2.1.31'),('enom_pro','test1','1234'),('enom_pro','debug','on'),('enom_pro','ssl_hidden','a:1:{i:0;i:26773;}'),('enom_pro','debug','on'),('enom_pro','debug','on'),('enom_pro','debug','on'),('enom_pro','debug','on'),('enom_pro','debug','on'),('enom_pro','debug','on');
/*!40000 ALTER TABLE `tbladdonmodules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mod_enom_pro`
--

DROP TABLE IF EXISTS `mod_enom_pro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mod_enom_pro` (
  `id` int(1) NOT NULL,
  `local` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_enom_pro`
--

LOCK TABLES `mod_enom_pro` WRITE;
/*!40000 ALTER TABLE `mod_enom_pro` DISABLE KEYS */;
INSERT INTO `mod_enom_pro` VALUES (0,'=03OiEzMuEjLyIiO2ozc7Iibvl2cyVmd0NXZ0FGbiozMxozc7ISOwEDM1EDMyIiO4ozc7ISZ0FGZrNWZ\noNmI6kjOztjIkJGO0QzM4UTMlVjYxUDM5MjY3gzNzEjZ1YTM3QTO2ImI6IzM6M3Oig2chhWNk1mI6cjO\nztjIvJHcf12buV2Lz52bkRWYvMXZsVHZv12LwVmcw9FZlR2bj5WZvQGbpVnYv8mcw9Vbv5WZvQXan9ic\nvdWZydGdyVmYvJ3LzJXZzV1LioTN3ozc7ISey9GdjVmcpRGZpxWY2JiO0EjOztjIxojOiozM6M3OiAXa\nklGbhZnI6cjOztjI0N3boxWYj9Gbuc3d3xCdz9GasF2YvxmI6MjM6M3Oi4Wah12bkRWasFmdioTMxozc\n7ICduV3bjNWQgUWZyZkI6ITM6M3OiUGbjl3Yn5WasxWaiJiOyEjOztjIwATLwATLwADMwIiOwEjOztjI\nlRXYkVWdkRHel5mI6ETM6M3OiUTMtQDMtQTMwIjI6ATM6M3OiUGdhR2ZlJnI6cjOztjIlNnblNWaMBid\nlREIPJFUg02bOVmI6AjM6M3OiUWbh5GdjVHZvJHcioTMxozc7IiMzIiOyozc7ICZpR3Y1R2byBnI6kjO\nztjIxYDOxIiO0ozc7ICZpV2YpZnclNnI6kjOztjIt92YuMnclRXdw12bjNnYvJGQi9mYioTMyozc7ICb\npFWblJiO1ozc7ICduV3bjNWQgQ3clRlI6ITM6M3OiUWbh5WeuFGct92YioTMxozc7IicvdWZydEIi9mQ\nioDMxozc7ISZtFmbkVmclR3cpdWZyJiO0EjOztjIlZXa0NWQiojN6M3OiMXd0FGdzJiO2ozc7pjNxoTY\ne55c512331a287aa91e04fab4f796b47973a0437e743f51870ccd84755b6f28a'),(1,'a:54:{s:11:\"spinner_net\";s:2:\"on\";s:11:\"spinner_com\";s:2:\"on\";s:11:\"spinner_css\";s:2:\"on\";s:16:\"spinner_checkout\";s:2:\"on\";s:7:\"version\";s:6:\"2.1.31\";s:9:\"quicklink\";s:0:\"\";s:4:\"save\";s:0:\"\";s:7:\"license\";s:29:\"enom-pro-dev-24e76c34ae8f7584\";s:17:\"api_request_limit\";s:2:\"10\";s:12:\"client_limit\";s:9:\"Unlimited\";s:15:\"balance_warning\";s:2:\"50\";s:5:\"debug\";s:0:\"\";s:4:\"beta\";s:0:\"\";s:14:\"import_section\";s:0:\"\";s:15:\"import_per_page\";s:2:\"25\";s:13:\"auto_activate\";s:2:\"on\";s:13:\"next_due_date\";s:7:\"-3 Days\";s:13:\"pricing_years\";s:2:\"10\";s:16:\"pricing_per_page\";s:2:\"25\";s:14:\"pricing_retail\";s:0:\"\";s:22:\"exchange_rate_provider\";s:6:\"google\";s:20:\"custom-exchange-rate\";s:0:\"\";s:21:\"exchange-rate-api-key\";s:0:\"\";s:11:\"ssl_section\";s:0:\"\";s:8:\"ssl_days\";s:2:\"30\";s:17:\"ssl_email_enabled\";s:0:\"\";s:14:\"ssl_email_days\";s:2:\"30\";s:15:\"ssl_open_ticket\";s:8:\"Disabled\";s:19:\"ssl_ticket_priority\";s:3:\"Low\";s:18:\"ssl_ticket_subject\";s:24:\"Expiring SSL Certificate\";s:18:\"ssl_ticket_message\";s:160:\"We have opened a ticket to renew {$product} for {$domain_name}, which  is set to expire on {$expiry_date}. Our staff will help you get your certificate renewed.\";s:24:\"ssl_ticket_email_enabled\";s:0:\"\";s:23:\"ssl_ticket_default_name\";s:0:\"\";s:24:\"ssl_ticket_default_email\";s:0:\"\";s:15:\"spinner_section\";s:0:\"\";s:15:\"spinner_results\";s:2:\"10\";s:15:\"spinner_columns\";s:1:\"3\";s:14:\"spinner_sortby\";s:5:\"score\";s:18:\"spinner_sort_order\";s:10:\"Descending\";s:14:\"cart_css_class\";s:11:\"btn-primary\";s:21:\"custom_cart_css_class\";s:0:\"\";s:17:\"spinner_animation\";s:6:\"Medium\";s:10:\"spinner_tv\";s:0:\"\";s:10:\"spinner_cc\";s:0:\"\";s:15:\"spinner_hyphens\";s:0:\"\";s:15:\"spinner_numbers\";s:0:\"\";s:17:\"spinner_sensitive\";s:0:\"\";s:13:\"spinner_basic\";s:6:\"Medium\";s:15:\"spinner_related\";s:4:\"High\";s:16:\"spinner_similiar\";s:6:\"Medium\";s:15:\"spinner_topical\";s:4:\"High\";s:5:\"save2\";s:0:\"\";s:10:\"quicklink2\";s:0:\"\";s:6:\"access\";s:1:\"1\";}');
/*!40000 ALTER TABLE `mod_enom_pro` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-01-17 14:25:48
