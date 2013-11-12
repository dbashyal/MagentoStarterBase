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
require_once(dirname(__File__) . '/LocationController.php');
class Technooze_Stores_IndexController extends Technooze_Stores_LocationController
{
	public function __call($method, $args)
    {
        if ('Action' == substr($method, -6)) {
            // If the action method was not found, forward to the
            // index action
            return $this->_forward('index');
        }

        // all other methods throw an exception
        throw new Exception('Invalid method "'
                            . $method
                            . '" called',
                            500);
    }

	public function selectedAction()
	{
		$this->loadLayout();

		//
		$store_location_id = $this->getRequest()->getParam('id');

		$collection = Mage::getModel('stores/location')->getCollection();
        $collection
            ->addFieldToFilter('stores_id', $store_location_id)
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('stores', Mage::app()->getStore()->getStoreId())
        ;

        // If no stores found, display error
        if(!$collection->count()){
            Mage::getSingleton('stores/session')->addError('That store no longer exists!');
            header('Location:'.Mage::getUrl('stores'));
            die();
        }

		$stores_all = array();
		$s = '';

		foreach($collection as $v)
		{
            $s = $v->getTitle();
            $stores_all = $v->getData();
		}

		Mage::register('storeTitle', $s);

		//foreach($collection as $loc)
		Mage::register('store_map', $stores_all);

		if(!empty($s))
		{
			$headBlock = $this->getLayout()->getBlock('head');
			$headBlock->setTitle($s);
			//$headBlock->setKeywords($brands_selected['meta_keywords']);
			//$headBlock->setDescription($brands_selected['meta_description']);
		}


		if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label'=>Mage::helper('catalogsearch')->__('Home'),
                'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            if(Mage::getStoreConfig('stores/displaysettings/showallstores')){
                $breadcrumbs->addCrumb('search', array(
                             'label'=>Mage::helper('stores')->__('Stores'),
                             'link'=>Mage::getUrl().'stores'
                         ));
            }

			$breadcrumbs->addCrumb('search_result', array(
                'label'=>Mage::helper('stores')->__($s)
            ))

			;
        }

        $this->renderLayout();
	}

    public function noRouteAction($coreRoute = null)
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $pageId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_NO_ROUTE_PAGE);
        if (!Mage::helper('cms/page')->renderPage($this, $pageId)) {
            $this->_forward('defaultNoRoute');
        }
    }

    /**
     * Default no route page action
     * Used if no route page don't configure or available
     *
     */
    public function defaultNoRouteAction()
    {
        $this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
        $this->getResponse()->setHeader('Status','404 File not found');

        $this->loadLayout();
        $this->renderLayout();
    }

	public function indexAction()
	{
        // first check if display all stores page is allowed
        $show = Mage::getStoreConfig('stores/displaysettings/showallstores');
        if(!$show){
            $this->_forward('noRoute');
        }

		// display all stores
        $this->loadLayout();

		if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label'=>Mage::helper('catalogsearch')->__('Home'),
                'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            if(Mage::getStoreConfig('stores/displaysettings/showallstores')){
                $breadcrumbs->addCrumb('search_result', array(
                             'label'=>Mage::helper('stores')->__('Stores'),
                             'link'=>Mage::getUrl().'stores'
                         ));
            }
        }

		//
		//$store_location_id = $this->getRequest()->getParam('id');

		$collection = Mage::getModel('stores/location')->getCollection();
        $collection
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('stores', array(
                  '0',
                  Mage::app()->getStore()->getStoreId()
              )
            )
        ;
        $collection->getSelect()->order('title ASC');//->where('stores_id=' . $store_location_id);

		$stores_all = array();
		$i = 0;
		foreach($collection as $v)
		{
            $stores_all[$i] = $v->getData();

            $stores_all[$i]['url'] = $v->getData('url_key'); // don't use getUrlKey()
            $stores_all[$i]['address'] = $v->getAddressDisplay();
			/*$stores_all[$i]['id'] = $v->getId();
			$stores_all[$i]['title'] = $v->getTitle();
			$stores_all[$i]['phone'] = $v->getPhone();
			$stores_all[$i]['fax'] = $v->getFax();
			$stores_all[$i]['email'] = $v->getEmail();
			$stores_all[$i]['hours'] = $v->getHours();
			$stores_all[$i]['notes'] = $v->getNotes();
			$stores_all[$i]['website_url'] = $v->getWebsiteUrl();*/

			$i++;
		}

		Mage::register('stores_list', $stores_all);

        $this->renderLayout();
	}

}
