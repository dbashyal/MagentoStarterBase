<?php
/*
 * Package: Magento E-Commerce
 * Purpose: Test
 * File URL: http://www.technooze.com/create_new_admin_user.php
 * Author: Damodar Bashyal
 */
include_once 'app/Mage.php';
umask(0);
Mage::app("default");

error_reporting(E_ALL);
$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
$proxy = new SoapClient($baseUrl . 'api/soap/?wsdl');

// If somestuff requires api authentification,
// we should get session token
$sessionId = $proxy->login('login', array('admin', 'acidgreen12'));

$filters = array(
    'sku' => array('like'=>'zol%')
);

$products = $proxy->call($sessionId, 'product.list', array($filters));

var_dump($products);