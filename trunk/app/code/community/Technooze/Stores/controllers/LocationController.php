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
class Technooze_Stores_LocationController extends Mage_Core_Controller_Front_Action
{

    /**
     * Check if it needs to redirect to first result
     *
     * @var bool
     */
    protected $_redirectToFirstResult   = true; // true by default

    public function _construct()
    {
        parent::_construct();

        // check if it needs to redirect to first result
        $this->_redirectToFirstResult = Mage::getStoreConfig('stores/displaysettings/autoselectfirst');
    }

    public function mapAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function closebyAction()
    {
        $this->_redirectToFirstResult = false;
        $this->finderAction();
    }

    public function finderAction()
    {
        $this->loadLayout();
		$stores = array();

        try {
            $num = (int)Mage::getStoreConfig('stores/general/num_results');
            $units = Mage::getStoreConfig('stores/general/distance_units');
            $collection = Mage::getModel('stores/location')->getCollection()
                ->addAreaFilter(
                    $this->getRequest()->getParam('lat'),
                    $this->getRequest()->getParam('lng'),
                    $this->getRequest()->getParam('radius'),
                    $this->getRequest()->getParam('units', $units)
                )
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', Mage::app()->getStore()->getStoreId())
                //->addProductTypeFilter($this->getRequest()->getParam('type'))
            ;

            $privateFields = Mage::getConfig()->getNode('global/stores/private_fields');//see config.xml
            $i = 0;

            // delete registry 'selected_store_location' if exists
            Mage::unregister('selected_store_location');
            foreach ($collection as $loc){
                // if instead of showing search results,
                // it's set to select first result then,
                // register key to registry
                if($this->_redirectToFirstResult){
                    $data = $loc->getData();
                    Mage::register('selected_store_location', $data);
                    break;
                } else {
                    foreach ($loc->getData() as $k=>$v) {
                        if (!$privateFields->$k) {
                            $stores[$i][$k] = $v;
                        }
                    }
                }
				$i++;
            }
        } catch (Exception $e) {
            /*
			$node = $dom->createElement('error');
            $newnode = $parnode->appendChild($node);
            $newnode->setAttribute('message', $e->getMessage());
			*/
        }

        Mage::register('store_locations', $stores);

        $this->renderLayout();
    }

    public function findAction()
    {
		$this->loadLayout();

		if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', array(
                'label'=>Mage::helper('catalogsearch')->__('Home'),
                'title'=>Mage::helper('catalogsearch')->__('Go to Home Page'),
                'link'=>Mage::getBaseUrl()
            ));

            if(Mage::getStoreConfig('stores/displaysettings/showallstores')){
                $breadcrumbs->addCrumb('search', array(
                             'label'=>Mage::helper('catalogsearch')->__('Stores'),
                             'link'=>Mage::getUrl().'stores'
                         ));
            }

			$breadcrumbs->addCrumb('search_result', array(
                'label'=>Mage::helper('catalogsearch')->__('Search Results')
            ));
        }

		$this->renderLayout();
    }

    public function searchAction()
    {
        $dom = new DOMDocument("1.0");
        $node = $dom->createElement("markers");
        $parnode = $dom->appendChild($node);

        try {
            $num = (int)Mage::getStoreConfig('stores/general/num_results');
            $units = Mage::getStoreConfig('stores/general/distance_units');
            $collection = Mage::getModel('stores/location')->getCollection()
                ->addAreaFilter(
                    $this->getRequest()->getParam('lat'),
                    $this->getRequest()->getParam('lng'),
                    $this->getRequest()->getParam('radius'),
                    $this->getRequest()->getParam('units', $units)
                )
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', Mage::app()->getStore()->getStoreId())
                //->addProductTypeFilter($this->getRequest()->getParam('type'))
            ;

            $privateFields = Mage::getConfig()->getNode('global/stores/private_fields');
            $i = 0;
            foreach ($collection as $loc){
                $node = $dom->createElement("marker");
                $newnode = $parnode->appendChild($node);
                $newnode->setAttribute("units", $units);
                $newnode->setAttribute("marker_label", ++$i);
                foreach ($loc->getData() as $k=>$v) {
                    if (!$privateFields->$k) {
                        $newnode->setAttribute($k, $v);
                    }
                }
            }
        } catch (Exception $e) {
            $node = $dom->createElement('error');
            $newnode = $parnode->appendChild($node);
            $newnode->setAttribute('message', $e->getMessage());
        }

        $this->getResponse()->setHeader('Content-Type', 'text/xml', true)->setBody($dom->saveXml());
    }
}
