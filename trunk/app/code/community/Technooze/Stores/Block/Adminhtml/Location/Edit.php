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
class Technooze_Stores_Block_Adminhtml_Location_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'stores';
        $this->_controller = 'adminhtml_location';

        $this->_updateButton('save', 'label', Mage::helper('stores')->__('Save Location'));
        $this->_updateButton('delete', 'label', Mage::helper('stores')->__('Delete Location'));

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

		if( $this->getRequest()->getParam($this->_objectId) ) {
            $model = Mage::getModel('stores/location')
                ->load($this->getRequest()->getParam($this->_objectId));
            Mage::register('location_data', $model);
        }

    }

    public function getHeaderText()
    {
        if( Mage::registry('location_data') && Mage::registry('location_data')->getId() ) {
            return Mage::helper('stores')->__("Edit Location", $this->htmlEscape(Mage::registry('location_data')->getTitle()));
        } else {
            return Mage::helper('stores')->__('New Location');
        }
    }
}
