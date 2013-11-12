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
class Technooze_Stores_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

    /**
     * This method will run when the product is saved from the Magento Admin
     * Use this function to update the product model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveProductTabData(Varien_Event_Observer $observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;

            $product = $observer->getEvent()->getProduct();
            $product_id = $product->getId();
            $selected_stores = $this->_getRequest()->getParam('stores_select');
            try {
                /**
                 * Delete old associated stores to this product
                 */
                $collection = Mage::getModel('stores/products')->getCollection();
                $collection->addFieldToFilter('products_id', $product_id)->load();
                foreach($collection as $old_store)
                {
                    $old_store->delete();
                }

                /*
                 * Now added new stores related to this product.
                 */
                if(is_array($selected_stores))
                {
                    foreach($selected_stores as $v)
                    {
                        $data = array('stores_id' => "{$v}", 'products_id' => "{$product_id}");
                        $model = Mage::getModel('stores/products')
                            ->setId(false)
                            ->setData($data)
                        ;

                        try {
                            $insertId = $model->save()->getId();
                        } catch (Exception $e){
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        }
                    }
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    /**
     * Retrieve the product model
     *
     * @return Mage_Catalog_Model_Product $product
     */
    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}
