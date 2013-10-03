<?php
class Technooze_Brands_Block_Current extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBrands()     
     { 
         $collection = Mage::getModel('brands/brands')->getCollection();
         $collection->addFieldToFilter('featured', 1);
         $collection->load();

         return $collection;
     }
}