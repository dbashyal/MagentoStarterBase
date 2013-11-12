<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('stores')} ADD  `status` int(1) NOT NULL default '1' AFTER `stores`;

    ");

$installer->endSetup();