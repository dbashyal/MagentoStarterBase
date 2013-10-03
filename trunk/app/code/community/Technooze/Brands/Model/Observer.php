<?php

class Technooze_Brands_Model_Observer
{
    /**
     * Flag to stop observer executing more than once
     *
     * @var static bool
     */
    static protected $_singletonFlag = false;

    /**
     * This method will run when the category is saved from the Magento Admin
     * Use this function to update the category model, process the
     * data or anything you like
     *
     * @param Varien_Event_Observer $observer
     */
    public function saveCategoryTabData(Varien_Event_Observer $observer)
    {
        if (!self::$_singletonFlag) {
            self::$_singletonFlag = true;

            $category = $observer->getEvent()->getCategory();
            $category_id = $category->getId();
            $selected_brands = $this->_getRequest()->getParam('brands_categories');

            try {
                /**
                 * Delete old associated brands to this category
                 */
                $collection = Mage::getModel('brands/categories')->getCollection();
                $collection->addFieldToFilter('category_id', $category_id);
                $collection->load();

                foreach($collection as $old_brand)
                {
                    $old_brand->delete();
                }

                /*
                 * Now added new brands related to this category.
                 */
                if(is_array($selected_brands))
                {
                    foreach($selected_brands as $v)
                    {
                        if(empty($v) || $v == 'on')
                        {
                            continue;
                        }
                        $data = array('brand_id' => "{$v}", 'category_id' => "{$category_id}");
                        $model = Mage::getModel('brands/categories')
                            ->setData($data)
                        ;

                        try {
                            $insertId = $model->save()->getId();
                        } catch (Exception $e){
                            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                        }
                    }
                }
            }
            catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    /**
     * Retrieve the category model
     *
     * @return Mage_Catalog_Model_Category $category
     */
    public function getCategory()
    {
        return Mage::registry('category');
    }

    /**
     * Shortcut to getRequest
     *
     */
    protected function _getRequest()
    {
        return Mage::app()->getRequest();
    }
}