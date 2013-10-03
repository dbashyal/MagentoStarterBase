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

Mage::log($_POST);

/*

$weight = 0;

// load product data
$product = Mage::getModel('catalog/product')->load(264);

// check to see if the product is of type configurable
if($product->getData('type_id') == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
    $collection = Mage::getModel('catalog/product_type_configurable')

      // Retrieve related products collection
      ->getUsedProductCollection($product)

      // make sure weight is included in the loaded data
      ->addAttributeToSelect('weight')

      // let's sort the collection by weight in descending order
      ->setOrder('weight', 'desc')

      // and we are after just one record, so set the limit as 1
      ->setPageSize(1);

    // let's go through the collection
    foreach($collection as $v){
        // save weight info as float
        $wt = (float)$v->getData('weight');

        // now check and use the highest weight,
        // either defined or received from product info
        $weight = (($weight > $wt) ? $weight : $wt);
        //echo "{$wt}\n<br>";
    }
}

// this should be the highest weight of the associated product.
print_r($weight);*/