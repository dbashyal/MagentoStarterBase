<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('brands')};
CREATE TABLE {$this->getTable('brands')} (
  `brands_id` int(11) unsigned NOT NULL auto_increment,
  `manufacturer_id` int(11) NOT NULL default '0',
  `manufacturer` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `logo_small` varchar(255) NOT NULL default '',
  `logo_medium` varchar(255) NOT NULL default '',
  `logo_large` varchar(255) NOT NULL default '',
  `body_image` varchar(255) NOT NULL default '',
  `content` text NOT NULL default '',
  `meta_title` text NOT NULL default '',
  `meta_keywords` text NOT NULL default '',
  `meta_description` text NOT NULL default '',
  `url_key` varchar(255) NOT NULL default '',
  `status` smallint(6) NOT NULL default '0',
  `featured` smallint(6) NOT NULL default '0',
  `sort_order` int(11) NOT NULL default '0',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`brands_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- DROP TABLE IF EXISTS {$this->getTable('brands/categories')};
CREATE TABLE IF NOT EXISTS `brands_categories` (
  `brand_id` int(11) NOT NULL DEFAULT '0',
  `category_id` int(11) NOT NULL DEFAULT '0',
  `sort_order` int(11) NOT NULL DEFAULT '0',
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 
