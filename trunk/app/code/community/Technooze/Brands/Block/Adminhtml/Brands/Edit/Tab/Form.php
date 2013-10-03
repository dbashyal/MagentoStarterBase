<?php

class Technooze_Brands_Block_Adminhtml_Brands_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset('brands_form', array('legend'=>Mage::helper('brands')->__('Item information')));
		
		$product = Mage::getModel('catalog/product');
		$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
							->setEntityTypeFilter($product->getResource()->getTypeId())
							->addFieldToFilter('attribute_code', 'manufacturer');

		$attribute = $attributes->getFirstItem()->setEntity($product->getResource());
		$manufacturers = $attribute->getSource()->getAllOptions(false);
		
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
		$manufacturers_entered = $connection->fetchAll("SELECT manufacturer_id FROM brands");
		
		$manufacturers_entered_array = array();
		if(is_array($manufacturers_entered) && count($manufacturers_entered)>0) foreach($manufacturers_entered as $v)
		{
			$manufacturers_entered_array[$v['manufacturer_id']] = $v['manufacturer_id'];
		}
		
		if ( Mage::getSingleton('adminhtml/session')->getBrandsData() )
		{
			$getBrandsData = (Mage::getSingleton('adminhtml/session')->getBrandsData());
		} elseif ( Mage::registry('brands_data') ) {
			$getBrandsData = (Mage::registry('brands_data')->getData());
		}
		
		if(!isset($getBrandsData['manufacturer_id'])) $getBrandsData['manufacturer_id'] = 0;
		
		if(is_array($manufacturers) && count($manufacturers)>0) foreach($manufacturers as $k => $v)
		{
			if($v['value'] != $getBrandsData['manufacturer_id'] && in_array($v['value'], $manufacturers_entered_array)) unset($manufacturers[$k]);
		}
		
		
		

		$fieldset->addField('manufacturer_id', 'select', array(
		  'label'     => Mage::helper('brands')->__('Brand'),
		  'name'      => 'manufacturer_id',
		  'values'    => $manufacturers
		  /*
		  array(
			  array(
				  'value'     => 1,
				  'label'     => Mage::helper('brands')->__('Enabled'),
			  ),
		
			  array(
				  'value'     => 2,
				  'label'     => Mage::helper('brands')->__('Disabled'),
			  ),
		  )*/,
          'required'  => true,
		));
	  
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('brands')->__('Brand Page Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));

      $fieldset->addField('logo_small', 'image', array(
          'label'     => Mage::helper('brands')->__('Small Logo'),
          'required'  => false,
          'name'      => 'logo_small',
	  ));

      $fieldset->addField('logo_medium', 'image', array(
          'label'     => Mage::helper('brands')->__('Medium Logo'),
          'required'  => false,
          'name'      => 'logo_medium',
	  ));

      $fieldset->addField('logo_large', 'image', array(
          'label'     => Mage::helper('brands')->__('Top Banner'),
          'required'  => false,
          'name'      => 'logo_large',
	  ));

      $fieldset->addField('body_image', 'image', array(
          'label'     => Mage::helper('brands')->__('Body BG Image'),
          'required'  => false,
          'name'      => 'body_image',
	  ));

      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('brands')->__('Description'),
          'title'     => Mage::helper('brands')->__('Description'),
          'style'     => 'width:700px; height:230px;',
          'wysiwyg'   => false,
          'required'  => true,
		  //'config'	  => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
      ));
     
      $fieldset->addField('meta_title', 'text', array(
          'name'      => 'meta_title',
          'label'     => Mage::helper('brands')->__('Meta Title'),
          'title'     => Mage::helper('brands')->__('Meta Title'),
          'required'  => false,
      ));

      $fieldset->addField('meta_keywords', 'editor', array(
          'name'      => 'meta_keywords',
          'label'     => Mage::helper('brands')->__('Meta Keywords'),
          'title'     => Mage::helper('brands')->__('Meta Keywords'),
          'style'     => 'width:700px; height:30px;',
          'wysiwyg'   => false,
      ));

      $fieldset->addField('meta_description', 'editor', array(
          'name'      => 'meta_description',
          'label'     => Mage::helper('brands')->__('Meta Description'),
          'title'     => Mage::helper('brands')->__('Meta Description'),
          'style'     => 'width:700px; height:30px;',
          'wysiwyg'   => false,
      ));
	  
      $fieldset->addField('url_key', 'text', array(
          'label'     => Mage::helper('brands')->__('URL key'),
          'name'      => 'url_key',
      ));
		
      $fieldset->addField('featured', 'select', array(
          'label'     => Mage::helper('brands')->__('Featured'),
          'name'      => 'featured',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('brands')->__('YES'),
              ),

              array(
                  'value'     => 0,
                  'label'     => Mage::helper('brands')->__('NO'),
              ),
          ),
      ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('brands')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('brands')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('brands')->__('Disabled'),
              ),
          ),
      ));
	  
      $fieldset->addField('sort_order', 'text', array(
          'label'     => Mage::helper('brands')->__('Sort Order'),
          'name'      => 'sort_order',
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getBrandsData() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getBrandsData());
          Mage::getSingleton('adminhtml/session')->setBrandsData(null);
      } elseif ( Mage::registry('brands_data') ) {
          $form->setValues(Mage::registry('brands_data')->getData());
      }
      return parent::_prepareForm();
  }
}
