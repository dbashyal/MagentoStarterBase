<?php
class Technooze_Brands_IndexController extends Mage_Core_Controller_Front_Action
{
    protected function _helper(){
        return Mage::helper('brands');
    }

    public function indexAction(){
        $this->loadLayout();
        $this->renderLayout();
    }

    public function selectedAction(){
        $this->loadLayout();
        /*
        * Load an object by id
        * Request looking like:
        * http://technooze.com/brands?id=15
        *  or
        * http://technooze.com/brands/id/15
        */
        $brands_id = $this->getRequest()->getParam('id');
        $query = $this->getRequest()->getParams();

        if (!empty($brands_id)) {
            $brands_selected = Mage::getModel('brands/brands')->load($brands_id)->getData();
            if(!isset($query['manufacturer'])){
                $query['manufacturer'] = $brands_id;
            }
        } else {
            $brands_selected = null;
        }
        Mage::register('brands_selected', $brands_selected);

        // i want manufacturer to be selected all the time
        // but not sure how as this is not working
        Mage::app()->getRequest()->setQuery($query);

        // for brands page, set meta title as set on brands manager
        if (!empty($brands_selected) && count($brands_selected) > 0) {
            $headBlock = $this->getLayout()->getBlock('head');
            $headBlock->setTitle($brands_selected['meta_title']);
            $headBlock->setKeywords($brands_selected['meta_keywords']);
            $headBlock->setDescription($brands_selected['meta_description']);
        }

        // display breadcrumbs
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label' => Mage::helper('catalogsearch')->__('Home'),
                'title' => Mage::helper('catalogsearch')->__('Go to Home Page'),
                'link' => Mage::getBaseUrl()
            ))->addCrumb('search', array(
                'label' => Mage::helper('catalogsearch')->__('Brands'),
                'link' => Mage::getUrl() . 'brands'
            ))->addCrumb('search_result', array(
                'label' => Mage::helper('catalogsearch')->__($brands_selected['manufacturer'])
            ));
        }

        $this->renderLayout();
    }
}
