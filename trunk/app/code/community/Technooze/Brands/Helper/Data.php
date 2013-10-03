<?php

class Technooze_Brands_Helper_Data extends Mage_Core_Helper_Abstract
{
	function get_brands()
	{
		$resource = Mage::getSingleton('core/resource');
		$read= $resource->getConnection('core_read');
		$brand = $resource->getTableName('brands');
		
		$select = $read ->select()
						->from($brand)
						->where("status=1");

		//http://framework.zend.com/manual/en/zend.db.adapter.html
		//echo $select->__toString();
		
		return $read->fetchAll($select);
	}

    public function getAttributeOptions($attribute_code) {
      $attribute = Mage::getModel('catalog/entity_attribute');
        $attribute->setStoreId(Mage::app()->getStore()->getId())->loadByCode('catalog_product', $attribute_code);
        $options = array();
       if ($attribute->getData('is_visible')) {
         $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
           ->setAttributeFilter($attribute->getId())
           ->setStoreFilter()
           ->load()
           ->toOptionArray();
      }  
 
           return $options;
   }
 
   protected function _addCategoryOption($categoryData, $levelPrefix = '&raquo;', $startAtLevel = 2)
   {
       if ($categoryData['level'] >= $startAtLevel) {
         return array(
           'value'  => $categoryData['entity_id'],
           'label'  => str_repeat($levelPrefix, $categoryData['level'] - $startAtLevel) . $categoryData['name']
         );
       }
 
       return null;
   }
 
   public function getCategoryOptions()
   {
    $options = array();
       $categoryCollection = Mage::getModel('catalog/category')->load(Mage::app()->getStore()->getRootCategoryId())->getCollection()->addAttributeToSelect('name');
    foreach ($categoryCollection as $categoryData) {
      if ($option = $this->_addCategoryOption($categoryData)) {
        $options[] = $option;
      }
    }
 
    return $options;
   }

   	public function getRoute(){
		$route = "filter";
		return $route;
	}

}