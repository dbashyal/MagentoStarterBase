<?php
  class Technooze_Brands_Block_Layer_View extends Mage_Catalog_Block_Layer_View
  {
    public function getLayer()
    {
        return Mage::getSingleton('brands/layer');
    }
  }