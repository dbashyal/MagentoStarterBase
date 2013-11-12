<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('stores')} ADD  `suburb` varchar(255) NOT NULL default '' AFTER `address`;
ALTER TABLE {$this->getTable('stores')} ADD  `country` varchar(2) NOT NULL default '' AFTER `suburb`;

    ");

$installer->endSetup();