<?php

class Technooze_Brands_Block_Adminhtml_Brands_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('brands_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('brands')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('brands')->__('Item Information'),
          'title'     => Mage::helper('brands')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('brands/adminhtml_brands_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}