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

//Create Static Block
/*$staticBlock = array(
                'title' => 'My Test Block',
                'identifier' => 'my-test-block',
                'content' => 'Lorem ipsum dolor sit, amen hte gulocse',
                'is_active' => 1,
                'stores' => array(Mage_Core_Model_App::ADMIN_STORE_ID)
                );
Mage::getModel('cms/block')->setData($staticBlock)->save();*/


//Create CMS Page
$cmsPage = array(
            'title' => 'Test Page',
            'identifier' => 'test-page',
            'content' => 'Sample Test Page',
            'is_active' => 1,
            'sort_order' => 0,
            'stores' => array(Mage_Core_Model_App::ADMIN_STORE_ID),
            'root_template' => 'three_columns'
            );

Mage::getModel('cms/page')->setData($cmsPage)->save();
