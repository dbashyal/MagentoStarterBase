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
                ->addFieldToFilter('stores', array('0', Mage::app()->getStore()->getStoreId()))
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
                ->addFieldToFilter('stores', array('0', Mage::app()->getStore()->getStoreId()))
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
    
     public function findStoreAction(){
         
         
         $result = array();
         
         $address = $this->getRequest()->getParam('address');
         $country_code = $this->getRequest()->getParam('country');
         $suburb = $this->getRequest()->getParam('suburb');
         $location_type = $this->getRequest()->getParam('location_type');
         $date = $this->getRequest()->getParam('month');
         
         $country = Mage::app()->getLocale()->getCountryTranslation($country_code);
         
         $model = Mage::getModel('stores/location');
         $model->setAddress($this->__('%s, %s', $address . $suburb, $country));
                  
         $model->fetchCoordinates();
         $lat = $model->getLatitude();
         $lng = $model->getLongitude();

        try {
            
            $num = (int)Mage::getStoreConfig('stores/general/num_results');
            $units = Mage::getStoreConfig('stores/general/distance_units');
            $collection = Mage::getModel('stores/location')->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', array('0', Mage::app()->getStore()->getStoreId()));
            
            if($location_type == Technooze_Stores_Model_LocationType::STOCKIST) {
                if($country_code == "AU") {
                    if($suburb){
                        $collection->addFieldToFilter('suburb', $suburb);
                    }
					
					$collection->addAreaFilter($lat, $lng, 50, $units);
					
                } else {
                    $collection->addAreaFilter($lat, $lng, 10000, $units);
                    $collection->addFieldToFilter('country', $country_code);
                }
            } elseif ($location_type == Technooze_Stores_Model_LocationType::MARKET || $location_type == Technooze_Stores_Model_LocationType::EVENT) {
                
                if($address)
                    $collection->addAreaFilter($lat, $lng, 50, $units);
                else
                    $collection->addAreaFilter($lat, $lng, 100000, $units);
                
                if($suburb){
                    $collection->addFieldToFilter('suburb', $suburb);
                }
                
                if($date){
                    $date = split(', ', $date);
                    $collection->addFieldToFilter('month', $date[0]);
                    $collection->addFieldToFilter('year', $date[1]);
                }
                
                $collection->addFieldToFilter('country', $country_code);
                
            }

            $collection->addFieldToFilter('location_type', $location_type);
            $collection->getSelect()->limit($num);
            
            foreach($collection as $k => $v){
                                
                $result[$k]['stores_id']        = $v->getStoresId();
                $result[$k]['stores']           = $v->getStores();
                $result[$k]['status']           = $v->getStatus();
                $result[$k]['title']            = $v->getTitle();
                $result[$k]['address']          = $v->getAddress();
                $result[$k]['suburb']           = $v->getSuburb();
                $result[$k]['country']          = $v->getCountry();
                $result[$k]['latitude']         = $v->getLatitude();
                $result[$k]['longitude']        = $v->getLongitude();
                $result[$k]['address_display']  = $v->getAddressDisplay();
                $result[$k]['notes']            = $v->getNotes();
                $result[$k]['hours']            = $v->getHours();
                $result[$k]['email']            = $v->getEmail();
                $result[$k]['website_url']      = $v->getWesiteUrl();
                $result[$k]['phone']            = $v->getPhone();
                $result[$k]['fax']              = $v->getFax();
                $result[$k]['product_types']    = $v->getProductTypes();
                $result[$k]['logo_small']       = $v->getLogoSmall();
                $result[$k]['logo_medium']      = $v->getLogoMedium();
                $result[$k]['logo_large']       = $v->getLogoLarge();
                $result[$k]['store_photo']      = $v->getStorePhoto();
                $result[$k]['store_pdf']        = $v->getStorePdf();
                $result[$k]['distance']         = $v->getDistance();
            }
            
        } catch (Exception $e) {
            
            $result['error'] = $this->__('There has been an error searching.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
    
    public function findAllInternationalStoreAction(){
         
         
        $result = array();
          
        try {
            $collection = Mage::getModel('stores/location')->getCollection()
                ->addFieldToFilter('status', 1)
                ->addFieldToFilter('stores', array('0', Mage::app()->getStore()->getStoreId()))
                ->addFieldToFilter('country', array('neq' => 'AU'))
                ->addFieldToFilter('location_type', Technooze_Stores_Model_LocationType::STOCKIST);
            
            foreach($collection as $k => $v){
                
                $country = Mage::app()->getLocale()->getCountryTranslation($v->getCountry());
                
                $result[$country][$k]['stores_id']        = $v->getStoresId();
                $result[$country][$k]['stores']           = $v->getStores();
                $result[$country][$k]['status']           = $v->getStatus();
                $result[$country][$k]['title']            = $v->getTitle();
                $result[$country][$k]['address']          = $v->getAddress();
                $result[$country][$k]['suburb']           = $v->getSuburb();
                $result[$country][$k]['latitude']         = $v->getLatitude();
                $result[$country][$k]['longitude']        = $v->getLongitude();
                $result[$country][$k]['address_display']  = $v->getAddressDisplay();
                $result[$country][$k]['notes']            = $v->getNotes();
                $result[$country][$k]['hours']            = $v->getHours();
                $result[$country][$k]['email']            = $v->getEmail();
                $result[$country][$k]['website_url']      = $v->getWesiteUrl();
                $result[$country][$k]['phone']            = $v->getPhone();
                $result[$country][$k]['fax']              = $v->getFax();
                $result[$country][$k]['product_types']    = $v->getProductTypes();
                $result[$country][$k]['logo_small']       = $v->getLogoSmall();
                $result[$country][$k]['logo_medium']      = $v->getLogoMedium();
                $result[$country][$k]['logo_large']       = $v->getLogoLarge();
                $result[$country][$k]['store_photo']      = $v->getStorePhoto();
                $result[$country][$k]['store_pdf']        = $v->getStorePdf();
                $result[$country][$k]['distance']         = $v->getDistance();
            }
            
        } catch (Exception $e) {
            
            $result['error'] = $this->__('There has been an error searching.');
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
}
