<?php
/**
 * Technooze_Stores extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Technooze
 * @package    Technooze_Stores
 * @copyright  Copyright (c) 2008 Technooze LLC
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * @category   Technooze
 * @package    Technooze_Stores
 * @author     Technooze <info@technooze.com>
 */
$this->startSetup()->run("
CREATE TABLE {$this->getTable('stores')} (
  `stores_id` int(10) unsigned NOT NULL auto_increment,
  `stores` text  NOT NULL default '',
  `status` int(1) NOT NULL default '1',
  `title` varchar(255)  NOT NULL default '',
  `address` text  NOT NULL default '',
  `suburb` varchar(255)  NOT NULL default '',
  `country` varchar(2)  NOT NULL default '',
  `latitude` decimal(15,10) NOT NULL,
  `longitude` decimal(15,10) NOT NULL,
  `address_display` text  NOT NULL default '',
  `notes` text  NOT NULL default '',
  `hours` text  NOT NULL default '',
  `location_type` varchar(255) NOT NULL default 'Stockist',
  `year` varchar(255) NOT NULL default '',
  `month` varchar(255) NOT NULL default '',
  `email` varchar(255)  NOT NULL default '',
  `website_url` varchar(255)  NOT NULL default '',
  `phone` varchar(50)  NOT NULL default '',
  `fax` varchar(50)  NOT NULL default '',
  `product_types` varchar(255)  NOT NULL default '',
  `url_key` varchar(255)  NOT NULL default '',
  `logo_small` varchar(255) NOT NULL default '',
  `logo_medium` varchar(255) NOT NULL default '',
  `logo_large` varchar(255) NOT NULL default '',
  `store_photo` varchar(255) NOT NULL default '',
  `store_pdf` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`stores_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
")->endSetup();

$this->startSetup()->run("

CREATE TABLE IF NOT EXISTS {$this->getTable('stores_products')} (
      `stores_products_id` int(11) NOT NULL AUTO_INCREMENT,
      `stores_id` int(11) NOT NULL,
      `products_id` int(11) NOT NULL,
      PRIMARY KEY (`stores_products_id`),
     KEY `products_id` (`products_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

")->endSetup();
