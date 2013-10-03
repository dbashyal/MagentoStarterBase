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
class Technooze_Brands_Block_Adminhtml_Catalog_Category_Tab_Brands extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('catalog_category_brands');
        $this->setDefaultSort('brands_id');
        $this->setUseAjax(true);
    }

    public function getCategory()
    {
        $category = Mage::registry('category');

        if(!is_object($category))
        {
            $id = $this->getRequest()->getParam('id', 0);
            $category = Mage::getModel('catalog/category')->load($id);

            Mage::register('category', $category);
        }

        return $category;
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in category flag
        if ($column->getId() == 'selected_brands') {
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
        else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(array('selected_brands'=>1));
        }
        $collection = Mage::getModel('brands/brands')->getCollection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        if (!$this->getCategory()->getBrandsReadonly()) {
            $this->addColumn('selected_brands', array(
                'header_css_class' => 'a-center',
                'type'      => 'checkbox',
                'name'      => 'selected_brands',
                'field_name' => 'brands_categories[]',
                'values'    => $this->_getSelectedBrands(),
                'align'     => 'center',
                'index'     => 'brands_id'
            ));
        }
        $this->addColumn('brands_id', array(
            'header'    => Mage::helper('catalog')->__('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'brands_id'
        ));
        $this->addColumn('title', array(
            'header'    => Mage::helper('catalog')->__('Title'),
            'width'     => '320',
            'index'     => 'title'
        ));
        $this->addColumn('manufacturer', array(
            'header'    => Mage::helper('catalog')->__('Brand'),
            'index'     => 'manufacturer'
        ));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('brands/adminhtml_category/index', array('_current'=>true));
    }

    protected function _getSelectedBrands()
    {
        $selected_brands = array();
        $category = $this->getCategory();
        $collection = Mage::getModel('brands/categories')->getCollection();
        $collection->addFieldToFilter('category_id', $category->getId());
        $collection->load();
        //Mage::log($collection->printLogQuery(true));

        foreach($collection as $v)
        {
            //Mage::log(print_r($v, true));
            $selected_brands[] = $v->getBrandId();
        }
        return $selected_brands;
    }
}

