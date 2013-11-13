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
class Technooze_Stores_Model_Location extends Mage_Core_Model_Abstract
{
    /**
     * Stores data helper
     *
     * @var object
     */
    protected $_helper    = '';

    /**
     * Key Unfier
     *
     * @var array
     */
    protected $_keys = array();

    protected function _construct()
    {
        $this->_init('stores/location');

        $this->_helper = Mage::helper('stores');
    }

    /**
     * Get url key and look if it already exists in url rewrite table
     * @param string $key
     * @param int $id
     * @return string
     */
    public function getUrlKey($key='', $id=0)
    {
        if(empty($key)){
            Mage::throwException(Mage::helper('stores')->__('store url_key can\'t be empty'));
            return false;
        }

        //Prepare URL key
        $key = $this->_helper->_urlTitle($key);

        // get existing key
        if($id){
            $collection = Mage::getModel('core/url_rewrite')->getCollection();
            $collection->addFieldToFilter('id_path', 'stores/'.$id);

            //check if record exists
            if($collection->count()){
                foreach($collection as $data){
                    // if supplied key and existing key is same,
                    // then return same key
                    if($data->getData('request_path') == "{$key}.html"){
                        return $key;
                    } else {
                        //if different delete existing key, to generate and save new one
                        $data->delete();
                    }
                }
            }
        }
        // generate new unique key based on the provided one
        return $this->generateUniqueUrlKey($key);
    }

    public function generateUniqueUrlKey($key='')
    {
        if(!isset($this->_keys[$key])){
            $this->_keys[$key] = 1;
        }
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->addFieldToFilter('request_path', "{$key}.html");

        // check to see if such url rewrite already exits,
        // if it exists, then try to create new one
        if($collection->count()){
            return $this->generateUniqueUrlKey($key . '-' . ($this->_keys[$key]++));
        }

        return $key;
    }

    /**
     * Urlrewrite save action
     *
     */
    public function saveUrlKey($key = '', $id=0, $store_id=0)
    {
        if(empty($key) || empty($id)){
            Mage::throwException(Mage::helper('stores')->__('Can not save url key as either key or id was empty!'));
            return false;
        }

        $id_path = 'stores/' . $id;
        $target_path = 'stores/index/selected/id/' . $id . '/';
        $options = '';
        $description = '';

        // delete previous record
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->addFieldToFilter('id_path', $id_path);
        //$collection->addFieldToFilter('store_id', $store_id);
        foreach($collection as $old){
            $old->delete();
        }

        $key = $this->getUrlKey($key, $id);

        $request_path = $key . '.html';

        $rewriteStores = array();
        if(!empty($store_id)){
            $rewriteStores[$store_id] = $store_id;
        }else{
            $allStores = Mage::app()->getStores();
            foreach($allStores as $_store){
                $rewriteStores[$_store->getData('store_id')] = $_store->getData('store_id');
            }
        }

        try {
            foreach($rewriteStores as $store_id){
                // insert new record
                $model = Mage::getModel('core/url_rewrite')->load(0);
                $model->setIdPath($id_path)
                    ->setTargetPath($target_path)
                    ->setOptions($options)
                    ->setDescription($description)
                    ->setRequestPath($request_path);

                if (!$model->getId()) {
                    $model->setIsSystem(0);
                }
                if (empty($store_id)) {
                    $store_id = 0;//Mage::app()->getStore()->getStoreId(); // shouldn't use this in multi-store
                }
                $model->setStoreId($store_id);

                // save and redirect
                $model->save();
            }
        }
        catch (Exception $e) {
            Mage::throwException(Mage::helper('stores')->__($e->getMessage()));
            return false;
        }
        return $request_path;
    }

    public function fetchCoordinates()
    {
        $url = Mage::getStoreConfig('stores/general/google_geo_url');
        if (!$url) {
            $url = "http://maps.google.com.au/maps/geo";
        }

        $url .= strpos($url, '?')!==false ? '&' : '?';
        $url .= 'q='.urlencode(preg_replace('#\r|\n#', ' ', $this->getAddress()))."&output=csv";

        $json_url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode(preg_replace('#\r|\n#', ' ', $this->getAddress())).'&sensor=false';

        try {
            $response = file_get_contents($json_url);
            if(empty($response)){
                $cinit = curl_init();
                curl_setopt($cinit, CURLOPT_URL, $json_url);
                curl_setopt($cinit, CURLOPT_HEADER,0);
                curl_setopt($cinit, CURLOPT_USERAGENT, $_SERVER["HTTP_USER_AGENT"]);
                curl_setopt($cinit, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($cinit);
                if (!is_string($response) || empty($response)) {
                    Mage::throwException(Mage::helper('stores')->__('Failed to fetch geo codes from google maps!'));
                    return $this;
                }
            }
        }catch (Exception $e){
            Mage::throwException(Mage::helper('stores')->__($e->getMessage()));
            return $this;
        }
		
        $result = json_decode($response);
        $this->setLatitude(0)->setLongitude(0);
        foreach($result->results as $v){
            $this->setLatitude($v->geometry->location->lat)->setLongitude($v->geometry->location->lng);
        }
        return $this;
    }

    public function reset()
    {
        foreach ($this->_data as $data){
            if (is_object($data) && method_exists($data, 'reset')){
                $data->reset();
            }
        }
        return $this;
    }

    protected function _beforeSave()
    {
        if (!$this->getAddress()) {
            $this->setAddress($this->getAddressDisplay());
        }

        $this->setAddress(str_replace(array("\n", "\r"), " ", $this->getAddress()));

        if (!(float)$this->getLongitude() || !(float)$this->getLatitude() || $this->getRecalculate()) {
            $this->fetchCoordinates();
        }

        parent::_beforeSave();
    }
}
