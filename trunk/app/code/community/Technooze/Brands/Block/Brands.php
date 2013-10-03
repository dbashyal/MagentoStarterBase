<?php
class Technooze_Brands_Block_Brands extends Mage_Core_Block_Template
{
    private $_brands = array();

     public function getBrands(){
         if(empty($this->_brands)){
             $collection = Mage::getModel('brands/brands')->getCollection();
             $collection->addFieldToFilter('status', 1);
             $collection->setOrder('manufacturer', 'asc');

             $brands = $collection->load();
             $this->_brands = $brands->getData();
         }
         return $this->_brands;
    }
}