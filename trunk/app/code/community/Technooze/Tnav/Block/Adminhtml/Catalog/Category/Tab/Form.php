<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Brands in category grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Technooze_Tnav_Block_Adminhtml_Catalog_Category_Tab_Form extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_category_children');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        /*if ($column->getId() == 'selected_brands') {
            $brandIds = $this->_getSelectedBrands();
            if (empty($brandIds)) {
                $brandIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('brands_id', array('in'=>$brandIds));
            }
            elseif(!empty($brandIds)) {
                $this->getCollection()->addFieldToFilter('brands_id', array('nin'=>$brandIds));
            }
        }
        else {*/
            parent::_addColumnFilterToCollection($column);
        //}
        return $this;
    }

    public function getMultipleRows($object)
    {
        return false;
    }

    protected function _prepareCollection()
    {
        /*if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('selected_brands'=>1));
        }*/
        $collection = Mage::getModel('catalog/category')
                        ->getCollection()
                        ->addFieldToFilter('is_active', array('eq'=>'1'))
                        ->addFieldToFilter('level', array('gt'=>'0'))
                        ->addAttributeToSelect('*');
        /*foreach($collection as $category){
            var_dump($category->getData());
            //break;
        }
        die;*/
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        //if (!$this->getCategory()->getBrandsReadonly()) {
            $this->addColumn('cat_entity_id', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'cat_entity_id',
                'field_name' => 'entity_ids[]',
                'values'    => $this->_getSelectedCategories(),
                'align'     => 'center',
                'index'     => 'entity_id'
            ));
        //}
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'entity_id'
        ));
        $this->addColumn('name', array(
            'header'    => Mage::helper('catalog')->__('Name'),
            'width'     => '320',
            'index'     => 'name'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('tnav/adminhtml_category/index', array('_current'=>true));
    }

    protected function _getSelectedCategories()
    {
        $selected_cats = array();
        $nav = Mage::getModel('tnav/tnav')->load(Mage::app()->getRequest()->getParam('id'));
        Mage::log($nav->getData());

        if($nav->getId())
        {
            $selected_cats = explode(',',$nav->getData('children_ids'));
        }

        return $selected_cats;
    }
}

