<?php

class Technooze_Brands_Model_Mysql4_Categories extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the brands_id refers to the key field in your database table.
        $this->_init('brands/categories', 'brands_categories_id');
    }
}