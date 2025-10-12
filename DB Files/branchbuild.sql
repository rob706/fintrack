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
  `user_id` INT NOT NULL,
  `active` int(4) NOT NULL DEFAULT 1,

  PRIMARY KEY (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Default Account for current transactions
--

insert into accounts (`account_name`,`account_type`,`user_id`,`active`) 
	select 'Bank Account',
    (select type_id from account_type where type_name='bank / savings account') as account_type,
    user_id,
    1 FROM `users`;

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

--
-- Add default categories to table `category`
--

INSERT INTO `category` (`category_name`, `user_id`, `income`, `expense`, `active`) VALUES
('Internal Transfer', 0, 1, 1, 1);

-- --------------------------------------------------------

--
-- Update the table `transactions`
--

-- Update user_id to be Integer

ALTER TABLE `transactions` CHANGE `user_id` `user_id` INT(11) NOT NULL;

-- Add account_id field

ALTER TABLE `transactions` ADD `account_id` INT NOT NULL AFTER `category_id`;

-- Map all transactions to a default bank account

update `transactions` as t left join (select user_id, account_id from accounts as a inner join account_type as t on t.type_id = a.account_type and t.type_name = 'bank / savings account') as a on t.user_id = a.user_id set t.account_id = a.account_id;

-- fix date field

ALTER TABLE `transactions` CHANGE `date` `date` DATE NOT NULL;

-- --------------------------------------------------------