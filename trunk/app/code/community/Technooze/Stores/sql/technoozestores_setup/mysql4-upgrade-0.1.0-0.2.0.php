<?php

$installer = $this;

$installer->startSetup();

$installer->run("

ALTER TABLE {$this->getTable('stores')} ADD `stores` text default '' AFTER `stores_id`;

    ");

$installer->endSetup();