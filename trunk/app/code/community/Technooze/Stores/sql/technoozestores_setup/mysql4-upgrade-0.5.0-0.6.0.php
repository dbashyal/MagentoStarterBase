<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('stores')} ADD  `location_type` varchar(255) NOT NULL default 'Stockist' AFTER `status`;
ALTER TABLE {$this->getTable('stores')} ADD  `year` varchar(255) AFTER `hours`;
ALTER TABLE {$this->getTable('stores')} ADD  `month` varchar(255) AFTER `hours`;


    ");

$installer->endSetup();