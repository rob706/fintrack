-- --------------------------------------------------------

--
-- SQL specific to this branch and the associated updates
--
-- Last Update: 12th October 2025
-- Update by: rob706
--

-- --------------------------------------------------------

--
-- Branch Dependancies
--

-- - Transaction table having accounts id added - This needs dev before this branch can be pushed
-- - Based off SQL Setup Script Version 1.3

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_name` varchar(50) NOT NULL,
  `account_type` int(11) NOT NULL,
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
    `p&l` INT(4) NOT NULL DEFAULT '0',
    
    PRIMARY KEY (`type_id`),
    UNIQUE (`type_name`)
)ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Add default categories for table `account_types`
--

INSERT INTO `account_type` (`type_name`, `classification`, `p&l`) VALUES
('Real Estate', 'Asset', 0),
('Investment', 'Asset', 0),
('Bank / Savings Account', 'Equity', 1),
('Loan', 'Liability', 0),
('Pension', 'Asset', 0),
('Credit Card', 'Liability', 1);

-- --------------------------------------------------------

--
-- Add default categories to table `category`
--

INSERT INTO `category` (`category_name`, `user_id`, `income`, `expense`, `active`) VALUES
('Internal Transfer', 0, 1, 1, 1);