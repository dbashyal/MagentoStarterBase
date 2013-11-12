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
class Technooze_Stores_Block_Stores extends Mage_Core_Block_Template
{
    private $_stores = array();
    public  $_radius = 25;
    public  $_redirectToFirstResult = false;

    public function _construct(){
        parent::_construct();
        $this->_init();
    }

    public function _init(){
        // set radius to search
        $this->setRadius();

        // check if it needs to redirect to first result
        $this->_redirectToFirstResult = Mage::getStoreConfig('stores/displaysettings/autoselectfirst');
        $this->setGoToFirstStore($this->_redirectToFirstResult);
    }

     public function getStoreLocations(){
         if(empty($this->_stores)){
             $collection = Mage::getModel('stores/stores')->getCollection();
             $collection->addFieldToFilter('status', 1);
             //$collection->setOrder('manufacturer', 'asc');

             $stores = $collection->load();
             $this->_stores = $stores->getData();
         }
         return $this->_stores;
    }

    public function setRadius($rad=0){
        $radius = 0;
        if($rad){
            $radius = $rad;
        } else {
            $radius = Mage::getStoreConfig('stores/general/default_radius');
        }
        if(!$radius){
            $radius = 25;
        }
        $this->_radius = $radius;

        return $this;
    }

    public function getRadius(){
        return $this->_radius;
    }

     public function findStoreLocations(){
         $collection = array();
         $model = Mage::getModel('stores/location');
         $model->setAddress($this->getRequest()->getParam('address').',Australia');
         $model->fetchCoordinates();
         $lat = $model->getLatitude();
         $lng = $model->getLongitude();

        try {
            $num = (int)Mage::getStoreConfig('stores/general/num_results');
            $units = Mage::getStoreConfig('stores/general/distance_units');
            $collection = Mage::getModel('stores/location')->getCollection()
                ->addAreaFilter(
                    $lat,
                    $lng,
                    $this->getRadius(),
                    $units
                )
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', Mage::app()->getStore()->getStoreId())
                //->addProductTypeFilter($this->getRequest()->getParam('type'))
            ;
            if($this->_redirectToFirstResult){
                $num = 1;
            }
            $collection->getSelect()->limit($num);

            //$privateFields = Mage::getConfig()->getNode('global/stores/private_fields'); // see config.xml
            //@todo: hide these fields(columns) from collection
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($this->__($e->getMessage()));
        }

         return $collection;
    }
}