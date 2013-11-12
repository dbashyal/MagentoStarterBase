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
class Technooze_Stores_Block_Adminhtml_Location_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $store_data = Mage::getModel('stores/location')->load($this->getRequest()->getParam('id'));

        $form = new Varien_Data_Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('location_stores_form', array(
            'legend'=>Mage::helper('stores')->__('General Setup')
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('stores', 'select', array(
                'name'      => 'stores',
                'label'     => Mage::helper('stores')->__('Store View'),
                'title'     => Mage::helper('stores')->__('Store View'),
                'required'  => true,
                'values'    => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }
        else {
            $fieldset->addField('stores', 'hidden', array(
                'name'      => 'stores',
                'value'     => Mage::app()->getStore(true)->getId()
            ));
            $store_data->setStoreId(Mage::app()->getStore(true)->getId());
        }

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('stores')->__('Store Status'),
            'title'     => Mage::helper('stores')->__('Store Status'),
            'name'      => 'status',
            'required'  => true,
            'options'   => array(
                1 => Mage::helper('stores')->__('Open'),
                0 => Mage::helper('stores')->__('Closed'),
            ),
        ));

        /*$fieldset->addField('location_type', 'select', array(
            'label'     => Mage::helper('stores')->__('Location Type'),
            'title'     => Mage::helper('stores')->__('Location Type'),
            'name'      => 'location_type',
            'required'  => true,
            'options'   => array(
                0 => Mage::helper('stores')->__('Stockist'),
                1 => Mage::helper('stores')->__('Market'),
                2 => Mage::helper('stores')->__('Event'),
            ),
        ));*/
        $fieldset = $form->addFieldset('location_form', array(
            'legend'=>Mage::helper('stores')->__('Store Location Info')
        ));

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('stores')->__('Title'),
            'class'     => 'required-entry',
            'required'  => true,
        ));

        $fieldset->addField('address_display', 'textarea', array(
            'name'      => 'address_display',
            'label'     => Mage::helper('stores')->__('Address to be displayed'),
            'class'     => 'required-entry',
            'style'     => 'height:90px',
            'required'  => true,
            'note'      => Mage::helper('stores')->__('This address will be shown to visitor and should have multiple lines formatting'),
        ));

        $fieldset->addField('suburb', 'text', array(
            'name'      => 'suburb',
            'label'     => Mage::helper('stores')->__('Suburb'),
        ));

        $fieldset->addField('country', 'select', array(
            'name'  => 'country',
            'label'     => 'Country',
            'values'    => Mage::getModel('adminhtml/system_config_source_country')->toOptionArray(),
            'required'  => true,
        ));

        $fieldset->addField('phone', 'text', array(
            'name'      => 'phone',
            'label'     => Mage::helper('stores')->__('Phone'),
        ));

        $fieldset->addField('fax', 'text', array(
            'name'      => 'fax',
            'label'     => Mage::helper('stores')->__('Fax'),
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('stores')->__('Store Email'),
        ));

        $fieldset->addField('website_url', 'text', array(
            'name'      => 'website_url',
            'label'     => Mage::helper('stores')->__('Store URL'),
            'note'      => Mage::helper('stores')->__('For URL please start with http://'),
        ));

        $fieldset->addField('hours', 'textarea', array(
            'name'      => 'hours',
            'style'     => 'height:125px',
            'label'     => Mage::helper('stores')->__('Opening Hours'),
			'note'      => Mage::helper('stores')->__('Set Opening Hours as <strong>Monday | 9am-5.30pm</strong> with "|" as separator'),
        ));

        /*$fieldset->addField('month', 'select', array(
            'label'     => Mage::helper('stores')->__('Month'),
            'title'     => Mage::helper('stores')->__('Month'),
            'name'  => 'month',
            'label'     => Mage::helper('stores')->__('Month'),
            'options'   => array(
                '01'   => Mage::helper('stores')->__('January'),
                '02'  => Mage::helper('stores')->__('February'),
                '03'     => Mage::helper('stores')->__('March'),
                '04'     => Mage::helper('stores')->__('April'),
                '05'       => Mage::helper('stores')->__('May'),
                '06'      => Mage::helper('stores')->__('June'),
                '07'      => Mage::helper('stores')->__('July'),
                '08'    => Mage::helper('stores')->__('August'),
                '09' => Mage::helper('stores')->__('September'),
                '10'   => Mage::helper('stores')->__('October'),
                '11'  => Mage::helper('stores')->__('November'),
                '12'  => Mage::helper('stores')->__('December')
            ),
        ));
        
        $fieldset->addField('year', 'select', array(
            'label'     => Mage::helper('stores')->__('Year'),
            'title'     => Mage::helper('stores')->__('Year'),
            'name'  => 'year',
            'label'     => Mage::helper('stores')->__('Year'),
            'options'   => $this->_getYear()
        ));
        
        
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap('location_type', 'location_type')
                ->addFieldMap('month', 'month')
                ->addFieldMap('year', 'year')
                ->addFieldDependence('month', 'location_type', array('1', '2'))
                ->addFieldDependence('year', 'location_type', array('1', '2'))
                
        );*/
        $fieldset->addField('notes', 'textarea', array(
            'name'      => 'notes',
            'style'     => 'height:50px',
            'label'     => Mage::helper('stores')->__('Notes'),
        ));

        $fieldset = $form->addFieldset('images_form', array(
            'legend'=>Mage::helper('stores')->__('Store Images')
        ));

          $fieldset->addField('logo_small', 'file', array(
              'label'       => Mage::helper('stores')->__('Small Logo'),
              'required'    => false,
              'name'        => 'logo_small',
              'note'        => $this->_imagePreview($store_data, 'logo_small')
          ));

          $fieldset->addField('logo_medium', 'file', array(
              'label'     => Mage::helper('stores')->__('Medium Logo'),
              'required'  => false,
              'name'      => 'logo_medium',
              'note'        => $this->_imagePreview($store_data, 'logo_medium'),
          ));

          $fieldset->addField('logo_large', 'file', array(
              'label'     => Mage::helper('stores')->__('Large Logo'),
              'required'  => false,
              'name'      => 'logo_large',
              'note'        => $this->_imagePreview($store_data, 'logo_large'),
          ));

          $fieldset->addField('store_photo', 'file', array(
              'label'     => Mage::helper('stores')->__('Store Photo'),
              'required'  => false,
              'name'      => 'store_photo',
              'note'        => $this->_imagePreview($store_data, 'store_photo'),
          ));

        $fieldset = $form->addFieldset('storepdf_form', array(
            'legend'=>Mage::helper('stores')->__('Store PDF')
        ));

          $fieldset->addField('store_pdf', 'file', array(
              'label'       => Mage::helper('stores')->__('PDF'),
              'required'    => false,
              'name'        => 'store_pdf',
              'note'        => $this->_imagePreview($store_data, 'store_pdf', 'file')
          ));

        $fieldset = $form->addFieldset('geo_form', array(
            'legend'=>Mage::helper('stores')->__('Geo Location')
        ));

        $fieldset->addField('address', 'textarea', array(
            'name'      => 'address',
            'style'     => 'height:50px',
            'label'     => Mage::helper('stores')->__('Address for geo location'),
            'note'      => Mage::helper('stores')->__('This address will be used to calculate latitude and longitude, free format is allowed.<br/>If left empty, will be copied from address to be displayed.'),
        ));

        $fieldset->addField('longitude', 'text', array(
            'name'      => 'longitude',
            'label'     => Mage::helper('stores')->__('Longitude'),
            'note'      => Mage::helper('stores')->__('If empty, will attempt to retrieve using the geo location address.'),
        ));

        $fieldset->addField('latitude', 'text', array(
            'name'      => 'latitude',
            'label'     => Mage::helper('stores')->__('Latitude'),
        ));

        $fieldset = $form->addFieldset('extra_configurations', array(
            'legend'=>Mage::helper('stores')->__('Configurations')
        ));

        $fieldset->addField('url_key', 'text', array(
            'name'      => 'url_key',
            'label'     => Mage::helper('stores')->__('URL Key'),
			'note'      => Mage::helper('stores')->__('Any key excluding <strong>.html</strong> e.g. rockdale'),
        ));

        Mage::dispatchEvent('stores_adminhtml_edit_prepare_form', array('block'=>$this, 'form'=>$form));

        if (Mage::registry('location_data')) {
            $form->setValues(Mage::registry('location_data')->getData());
        }

        return parent::_prepareForm();
    }

    private function _imagePreview($obj=false, $field_name=false, $file=false)
    {
        if(!is_object($obj) || empty($field_name))
        {
            return 'Not yet uploaded!';
        }

        if( $obj->getData($field_name) )
        {
            return '
            <a class="img-preview" href="'.Mage::getBaseUrl('media').$obj->getData($field_name).'" target="_blank">
                '.(($file) ? 'view': '<img border="0" alt="" src="'.Mage::getBaseUrl('media').$obj->getData($field_name).'" style="width:20px;height:20px;"/>').'
            </a>';
        }
        else
        {
            return 'Not yet uploaded!';
        }
    }
    
    private function _getYear() {
        
        $years = array();
        
        $year = date("Y", Mage::getModel('core/date')->timestamp(time()));
        
        for($i = 0; $i < 10; $i++){
            $years[$year] = $year;
            $year++;
        }
        
        return $years;
    }
}
