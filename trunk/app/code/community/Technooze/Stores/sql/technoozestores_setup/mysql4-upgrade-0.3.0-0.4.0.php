<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('stores')} ADD  `email` varchar(255) NOT NULL default '' AFTER `hours`;

    ");

$installer->endSetup();