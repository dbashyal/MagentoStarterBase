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

/**
 * Upload Store locations file and import data from it
 *
 * @param Varien_Object $object
 * @throws Mage_Core_Exception
 * @return Technooze_Stores_Model_Mysql4_Import
 */
class Technooze_Stores_Model_Mysql4_Import extends Mage_Core_Model_Mysql4_Abstract
{

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors        = array();

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows        = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash    = array();

    /**
     * Updated Headers on csv file
     *
     * @var array
     */
    protected $_headers    = array();

    /**
     * Original Headers on csv file
     *
     * @var array
     */
    protected $_headersOriginal    = array();

    /**
     * Headers missing on csv file
     *
     * @var array
     */
    protected $_headersMissing    = array();

    /**
     * Magento Stores (not locations)
     *
     * @var array
     */
    protected $_stores    = array();

    /**
     * Stores data helper
     *
     * @var object
     */
    protected $_helper    = '';

    public function _construct(){
        $this->_init('stores/location', 'stores_id');
    }

    public function uploadAndImport(Varien_Object $object)
    {
        $session = Mage::getSingleton('adminhtml/session');
        /* @var $session Mage_Adminhtml_Model_Session */

        if (empty($_FILES['groups']['tmp_name']['importexport']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['importexport']['fields']['import']['value'];

        $this->_importUniqueHash    = array();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;
        $this->_helper                = Mage::helper('stores');

        $io = new Varien_Io_File();
        $info = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();
        if ($headers === false || count($headers) < 5) {
            $io->streamClose();
            Mage::throwException(Mage::helper('stores')->__('Invalid Store Locations File Format'));
        }

        // lets store header columns
        $this->_headers = array_flip($headers);

        // also, save headers as original included on csv
        // this is required so we don't get array combine issue
        if(empty($this->_headersOriginal)){
            $this->_headersOriginal = $this->_headers;
        }

        $adapter = $this->_getWriteAdapter();
        $adapter->beginTransaction();

        try {
            $rowNumber  = 1;
            $importData = array();

            // delete old data by website and condition name
            /*$condition = array(
                'stores_id = ?'     => 0
            );
            $adapter->delete($this->getMainTable(), $condition);*/

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                $row = $this->_getImportRow($csvLine, $rowNumber);
                if ($row !== false) {
                    $importData[] = $row;
                }

                if (count($importData) == 5000) {
                    $this->_saveImportData($importData);
                    $importData = array();
                }
            }
            $this->_saveImportData($importData);
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $adapter->rollback();
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('stores')->__('An error occurred while importing Store Locations.'));
        }

        $adapter->commit();

        // update url keys to url rewrites
        $this->_reindex();

        if ($this->_importErrors) {
            $error = Mage::helper('stores')->__('%1$d records have been imported. See the following list of errors for each record that has not been imported: %2$s',
                $this->_importedRows, implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        } else {
            $success = Mage::helper('stores')->__('%1$d store locations imported successfully.', $this->_importedRows);
            $session->addSuccess(Mage::helper('stores')->__($success));
        }

        return $this;
    }

    /**
     * save url rewrites
     *
     * @return void
     */
    protected function _reindex()
    {
        $urlKeys = array();
        $model = Mage::getModel('stores/location');
        $collection = $model->getCollection();
        foreach($collection as $v){
            $model->saveUrlKey($v->getData('url_key'), $v->getData('stores_id'), $v->getData('stores'));
        }
    }

    /**
     * Validate row for import and return store locations array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) < 5) {
            $this->_importErrors[] = Mage::helper('stores')->__('Invalid store location data in the Row #%s', $rowNumber);
            return false;
        }

        // protect from duplicate
        /*
        $hash = md5($row[$this->_headers['stores_id']]);
        if (isset($this->_importUniqueHash[$hash])) {
            $this->_importErrors[] = Mage::helper('stores')->__('Duplicate Row');
            return false;
        }
        $this->_importUniqueHash[$hash] = true;
        */

        $row = array_combine(array_flip($this->_headersOriginal), $row);

        // if status is not set,
        // then set it as open '1'
        if(!isset($row['status']) || empty($row['status'])){
            $row['status'] = 1;

            // if status is not included in csv, add it
            if(!isset($this->_headers['status'])){
                $this->_headers['status'] = 'status';
            }
        } else {
            switch(strtolower($row['status'])){
                case 'open':
                case 'active':
                case 'online':
                case '1':
                    $row['status'] = 1;
                    break;
                case 'closed':
                case 'inactive':
                case 'offline':
                case '0':
                    $row['status'] = 0;
                    break;
                default:
                    $row['status'] = 1;
            }
        }

        $row['country'] = strtoupper($row['country']);

        // If store title is not defined,
        // return false with error
        if(!isset($row['title']) || empty($row['title'])){
            $this->_importErrors[] = Mage::helper('stores')->__('title (store name) field is required in the Row #%s', $rowNumber);
            return false;
        }

        // If store view code is not defined,
        // return false with error
        if(!isset($row['stores']) || empty($row['stores'])){
            $this->_importErrors[] = Mage::helper('stores')->__('stores (store view code) field is required in the Row #%s', $rowNumber);
            return false;
        } else {
            // if supplied value is store code instead of int value
            // fetch the correct id from the supplied code
            if((!$this->is_int($row['stores']))){
                $store = $this->getStoreByCode($row['stores']);

                // if store not found for supplied store code,
                // return false with error msg
                if(!$store){
                    $this->_importErrors[] = Mage::helper('stores')->__('stores (store view code) seems invalid in the Row #%s', $rowNumber);
                    return false;
                } else {
                    $row['stores'] = $store->getId();
                }
            }
        }

        // if address for geo is not set,
        // then use the display address to calculate geo
        if(!isset($row['address']) || empty($row['address'])){

            // But if display address is also not set,
            // then return false with error
            if(!isset($row['address_display']) || empty($row['address_display'])){
                $this->_importErrors[] = Mage::helper('stores')->__('address or address_display field must have address value in the Row #%s', $rowNumber);
                return false;
            }
            $row['address'] = $row['address_display'];

            // if address is not included in csv, add it
            if(!isset($this->_headers['address'])){
                $this->_headers['address'] = 'address';
            }
        }

        // If latitude or logitude is not set or are empty,
        // Get values from google geo code
        if(!isset($row['latitude']) || !isset($row['longitude']) || empty($row['latitude']) || empty($row['longitude'])){
            $model = Mage::getModel('stores/location');
            $model->setData('address', $row['address']);
            $geoCodes = $model->fetchCoordinates();
            $row['latitude'] = $geoCodes->getData('latitude');
            $row['longitude'] = $geoCodes->getData('longitude');

            // if latitude is not included in csv, add it
            if(!isset($this->_headers['latitude'])){
                $this->_headers['latitude'] = 'latitude';
            }

            // if longitude is not included in csv, add it
            if(!isset($this->_headers['longitude'])){
                $this->_headers['longitude'] = 'longitude';
            }
        }

        // If store url_key is not defined,
        // use title to generate url key
        if(!isset($row['url_key']) || empty($row['url_key'])){
            $row['url_key'] = $row['title'];

            // if url_key is not included in csv, add it
            if(!isset($this->_headers['url_key'])){
                $this->_headers['url_key'] = 'url_key';
            }
        }

        if(!isset($row['stores_id']) || empty($row['stores_id'])){

            // URL key
            $row['url_key'] = Mage::getModel('stores/location')->getUrlKey($row['url_key']);

            // insert as new store location
            return $row;
        } else {

            // URL key
            $row['url_key'] = Mage::getModel('stores/location')->getUrlKey($row['url_key'], $row['stores_id']);

            // update old record
            $this->_updateStoreLocations($row);
        }
        return false;
    }

    /**
     * Update existing store locations
     *
     * @param array $data
     * @return Void
     */
    protected function _updateStoreLocations(array $data)
    {
        if (!empty($data)) {
            $model = Mage::getModel('stores/location')->load($data['stores_id']);
            if($model->getId()){
                //$data = array_combine(array_flip($this->_headers), $data);
                $model->setData($data);
                $model->save();

                // save url rewrite key
                $model->saveUrlKey($data['url_key'], $model->getId(), $data['stores']);

                // increment total numbers of records updated
                $this->_importedRows++;
            } else {
                // no such store id found
                $this->_importErrors[] = Mage::helper('stores')->__('Store location not found with ID #%s', $data[$this->_headers['stores_id']]);
            }
        }
    }

    /**
     * Save import data batch
     *
     * @param array $data
     * @return Technooze_Stores_Model_Mysql4_Import
     */
    protected function _saveImportData(array $data)
    {
        if (!empty($data)) {
            $columns = array_flip($this->_headers);
            $this->_getWriteAdapter()->insertArray($this->getMainTable(), $columns, $data);
            $this->_importedRows += count($data);
        }

        return $this;
    }

    /**
     * Retrieve store object by code
     *
     * @param string $store
     * @return Mage_Core_Model_Store
     */
    public function getStoreByCode($store)
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
        }
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }


    /**
     * Check if supplied value is integer value
     *
     * @param integer $int
     * @return bool
     */
    public function is_int($int = 0)
    {
        // use PHP default function to check first
        if(is_int($int)){
            return true;
        }

        // else apply some hack and test
        $tmp = (int) $int;
        if($tmp == $int){
            return true;
        }

        // what can i do, if it still says string for "1"
        return false;
    }
}