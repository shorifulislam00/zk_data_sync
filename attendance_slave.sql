
--
-- Database: `attendance_slave`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_log`
--

CREATE TABLE `attendance_log` (
  `id` bigint(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `mechine_serial_no` varchar(20) NOT NULL,
  `raw_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `attendance_date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1=IN,2=OUT',
  `is_process` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=unprocess,1=processed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
