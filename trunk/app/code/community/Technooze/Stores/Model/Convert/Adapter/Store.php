<?php
class Technooze_Stores_Model_Convert_Adapter_Store extends Mage_Dataflow_Model_Convert_Adapter_Abstract
{
    protected $_storeModel;
    protected $_step = 1;
    protected $_address = array();

    public function load() {
      // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function save() {
      // you have to create this method, enforced by Mage_Dataflow_Model_Convert_Adapter_Interface
    }

    public function getStoreModel()
    {
        if (is_null($this->_storeModel)) {
            $storeModel = Mage::getModel('stores/location');
            $this->_storeModel = Mage::objects()->save($storeModel);
        }
        return Mage::objects()->load($this->_storeModel);
    }

    public function getAddress(array $importData)
    {
        $this->_address = array();
        if(isset($importData['address_line1'])){
            $this->_address[] = $importData['address_line1'];
        }
        if(isset($importData['address_line2'])){
            $this->_address[] = $importData['address_line2'];
        }
        if(isset($importData['address_line3'])){
            $this->_address[] = $importData['address_line3'];
        }
        if(isset($importData['suburb'])){
            $this->_address[] = $importData['suburb'];
        }
        if(isset($importData['state'])){
            $this->_address[] = $importData['state'];
        }
        if(isset($importData['postcode'])){
            $this->_address[] = $importData['postcode'];
        }
        if(isset($importData['country'])){
            $this->_address[] = $importData['country'];
        }

        $this->_address = array_filter($this->_address, 'trim');

        return implode(',', $this->_address);
    }

    public function saveImage($img=false)
    {
        if(empty($img)){
            //$message = Mage::helper('stores')->__('"%s" is not defined', $img);
            //Mage::throwException($message);
            return '';
        }
        $folder_path = 'stores'.DS.substr($img,0,1).DS.substr($img,1,1).DS;
        $file_name = $folder_path . $img;
        $destination = Mage::getBaseDir('media').DS.$folder_path;
        $image = Mage::getBaseDir('var').DS.'import'.DS.$img;
        if(!file_exists($image)){
            //$message = Mage::helper('stores')->__('"%s" does not exists!', $image);
            //Mage::throwException($message);
            return '';
        }
        try{
            @mkdir($destination, 0777, true);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
        $file_name = str_replace('\\', '/', $file_name);
        if(copy($image, $destination.$img)){
            return $file_name;
        }
        return '';
    }

    public function saveRow(array $importData)
    {
        $store = $this->getStoreModel();

        if (empty($importData['title'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'title');
            Mage::throwException($message);
        }
        if (empty($importData['country'])) {
            $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'country');
            Mage::throwException($message);
        }
        $importData['country'] = Mage::getModel('directory/country')->load($importData['country'])->getData('iso2_code');
        if (empty($importData['country'])) {
            $message = Mage::helper('catalog')->__('Skip import row, invalid value supplied for "%s"', 'country');
            Mage::throwException($message);
        }

        if (empty($importData['address'])) {
            $importData['address'] = $this->getAddress($importData);
            if(empty($importData['address'])){
                $message = Mage::helper('catalog')->__('Skip import row, required field "%s" not defined', 'address');
                Mage::throwException($message);
            }
        }

        if (!isset($importData['stores_id']) || empty($importData['stores_id'])) {$importData['stores_id'] = 0;}
        if (!isset($importData['stores']) || empty($importData['stores'])) {$importData['stores'] = 0;}
        if (!isset($importData['status']) || empty($importData['status'])) {$importData['status'] = 1;}
        if (!isset($importData['address_display']) || empty($importData['address_display'])) {$importData['address_display'] = $importData['address'];}
        if (!isset($importData['url_key']) || empty($importData['url_key'])) {$importData['url_key'] = $importData['title'];}

        try{
            $store = $store->load($importData['stores_id']);
            $store->setId($importData['stores_id']);
            if (!$importData['stores_id']){
                unset($importData['stores_id']);
            }
            $importData['logo_small'] = $this->saveImage($importData['logo_small']);
            if(empty($importData['logo_small'])){
                unset($importData['logo_small']);
            }
            $store->setData($importData);
            $store->save();

            $importData['id'] = $store->getId();

            // get unique url key based on the one client provided.
            $importData['url_key'] = Mage::getModel('stores/location')->getUrlKey($importData['url_key'], $store->getId());

            // save this url key to url_rewrite table
            Mage::getModel('stores/location')->saveUrlKey($importData['url_key'], $store->getId(), $importData['stores']);

            // update store with clean url key
            $store->setUrlKey($importData['url_key']);

            // save model to commit updates
            $store->save();
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return true;
    }
}