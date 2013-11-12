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
class Technooze_Stores_Block_Adminhtml_Location_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('locationsGrid');
        $this->setDefaultSort('stores_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('stores/location')->getCollection());
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCsv',
                 Mage::helper('stores')->__('CSV'));

        $this->addColumn('stores_id', array(
            'header'    => Mage::helper('stores')->__('ID'),
            'align'     => 'right',
            'width'     => '50px',
            'index'     => 'stores_id',
            'type'      => 'number',
        ));

        $this->addColumn('title', array(
            'header'    => Mage::helper('stores')->__('Title'),
            'align'     => 'left',
            'index'     => 'title',
        ));

        $this->addColumn('address', array(
            'header'    => Mage::helper('stores')->__('Address'),
            'align'     => 'left',
            'index'     => 'address',
        ));

        $this->addColumn('website_url', array(
            'header'    => Mage::helper('stores')->__('Website'),
            'index'     => 'website_url',
        ));

        $this->addColumn('phone', array(
            'header'    => Mage::helper('stores')->__('Phone'),
            'index'     => 'phone',
        ));

        $this->addColumn('fax', array(
            'header'    => Mage::helper('stores')->__('Fax'),
            'index'     => 'fax',
        ));

        $this->addColumn('longitude', array(
            'header'    => Mage::helper('stores')->__('Longitude'),
            'align'     => 'right',
            'index'     => 'longitude',
            'width'     => '50px',
            'type'      => 'number',
        ));

        $this->addColumn('latitude', array(
            'header'    => Mage::helper('stores')->__('Latitude'),
            'align'     => 'right',
            'index'     => 'latitude',
            'width'     => '50px',
            'type'      => 'number',
        ));

        Mage::dispatchEvent('stores_adminhtml_grid_prepare_columns', array('block'=>$this));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('stores_id');
        $this->getMassactionBlock()->setFormFieldName('stores');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('stores')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('stores')->__('Are you sure?')
        ));

        /*$statuses = Mage::getSingleton('stores/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('stores')->__('Change status'),
             'url'  => $this->getUrl('* / * /massStatus', array('_current'=>true)),|note there shouldn't be space at * /, had to keep space to comment this whole block
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('stores')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));*/
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}
