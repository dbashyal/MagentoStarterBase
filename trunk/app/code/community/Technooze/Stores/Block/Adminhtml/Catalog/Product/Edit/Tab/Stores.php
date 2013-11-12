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
 * Crossell products admin grid
 *
 * @category   Technooze
 * @package    Technooze_Stores
 * @author     Technooze <info@technooze.com>
 */
class Technooze_Stores_Block_Adminhtml_Catalog_Product_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid params
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('retail_stores_product_grid');
        $this->setDefaultSort('stores_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('stores/location')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
      $this->addColumn('stores_select', array(
          'header_css_class'  => 'a-center',
          'type'              => 'checkbox',
          'name'              => 'stores_select[]',
          'field_name'        => 'stores_select[]',
          'values'            => $this->getSelectedStores(),
          'align'             => 'center',
          'index'             => 'stores_id'
      ));

      $this->addColumn('stores_id', array(
          'header'    => Mage::helper('stores')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'stores_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('stores')->__('Title'),
          'align'     =>'left',
          'index'     => 'title',
      ));

      return parent::_prepareColumns();
  }

    public function getSelectedStores()
        {
            $sizes = $this->getStores();
            if (!is_array($sizes)) {
                $sizes = $this->getSelectedStoresCollection();
            }
            return $sizes;
        }

        public function getSelectedStoresCollection()
        {
            $products = array();
            $collection = Mage::getModel('stores/products')
                ->getCollection()
                ->addFieldToFilter('products_id', array(
                    'in'=>array(
                        $this->getRequest()->getParam('id', 0)
                    )
                )
            );
            $collection->load();
            //$this->setCollection($collection);

            foreach($collection as $v)
            {
                $products[$v->getStoresId()] = $v->getStoresId();
            }
            return $products;
        }

    /**
     * Add filter
     *
     * @param object $column
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Crosssell
     */
    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'stores_select') {
            $productIds = $this->getSelectedStores();
            if (empty($productIds)) {
                $productIds = 0;
            }

            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('stores_id', array('in'=>$productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('stores_id', array('nin'=>$productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

  public function getRowUrl($row)
  {
      return '';//$this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
}
