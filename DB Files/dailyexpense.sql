-- --------------------------------------------------------

--
-- MYSQL Manual SQL Setup Script Version 1.4
--
-- Last Update: 12th October 2025
-- Update by: rob706
--

-- --------------------------------------------------------


SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `dailyexpense`
--

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(20) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(15) NOT NULL,
  `date` date NOT NULL,
  `description` TEXT NULL,
  `tags` TEXT NULL,
  `category_id` int(11) NOT NULL,
  `account_id` INT NOT NULL,
  `value` double not null,

  PRIMARY KEY (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `profile_path` varchar(50) NOT NULL DEFAULT 'default_profile.png',
  `password` varchar(50) NOT NULL,
  `trn_date` datetime NOT NULL,
  `reset_token` VARCHAR(255) DEFAULT NULL,
  `reset_token_expiry` DATETIME DEFAULT NULL,

  PRIMARY KEY (`user_id`),
  UNIQUE(`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
 `category_id` int(11) NOT NULL AUTO_INCREMENT,
 `category_name` varchar(50) NOT NULL,
 `user_id` int(11) NOT NULL DEFAULT 0,
 `income` tinyint(1) NOT NULL DEFAULT 0,
 `expense` tinyint(1) NOT NULL DEFAULT 0,
 `active` tinyint(1) NOT NULL DEFAULT 1,

 PRIMARY KEY (`category_id`),
 UNIQUE KEY `category_name` (`category_name`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

--
-- Add default categories for table `category`
--

INSERT INTO `category` (`category_name`, `user_id`, `income`, `expense`, `active`) VALUES
('Salary', 0, 1, 0, 1),
('Rent', 0, 0, 1, 1),
('Loan', 0, 1, 1, 1),
('Investment', 0, 1, 0, 1),
('Insurance', 0, 0, 1, 1),
('Gift', 0, 1, 1, 1),
('Gas', 0, 0, 1, 1),
('Freelance', 0, 1, 0, 1),
('Electricity', 0, 0, 1, 1),
('Bonus', 0, 1, 0, 1),
('Other', 0, 1, 1, 1),
('Internal Transfer', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(50) NOT NULL,
  `account_type` int(11) NOT NULL,
  `user_id` INT NOT NULL,
  `active` int(4) NOT NULL DEFAULT 1,

  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `account_type`
--

CREATE TABLE `account_type` (
    `type_id` INT NOT NULL AUTO_INCREMENT,
    `type_name` VARCHAR(50) NOT NULL,
    `classification` VARCHAR(50) NOT NULL,
    `pl` INT(4) NOT NULL DEFAULT '0' COMMENT 'Profit & Loss',
    
    PRIMARY KEY (`type_id`),
    UNIQUE (`type_name`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Add default categories for table `account_types`
--

INSERT INTO `account_type` (`type_name`, `classification`, `pl`) VALUES
('Real Estate', 'Asset', 0),
('Investment', 'Asset', 0),
('Bank / Savings Account', 'Equity', 1),
('Loan', 'Liability', 0),
('Pension', 'Asset', 0),
('Credit Card', 'Liability', 1);

-- --------------------------------------------------------
