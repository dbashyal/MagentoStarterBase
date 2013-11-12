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
class Technooze_Stores_Adminhtml_LocationController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();

        $this->_setActiveMenu('cms/stores');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Locations'), Mage::helper('adminhtml')->__('Store Locations'));
        $this->_addContent($this->getLayout()->createBlock('stores/adminhtml_location'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->_title($this->__('stores'))->_title($this->__('Store Locations'));

        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('stores/location');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('stores')->__('This store no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getTitle() : $this->__('New Store'));

        // 3. Set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
        if (! empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        Mage::register('stores_location', $model);


        $this->loadLayout();

        $this->_setActiveMenu('cms/stores');
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Store Locations'), Mage::helper('adminhtml')->__('Store Locations'));

        $this->_addContent($this->getLayout()->createBlock('stores/adminhtml_location_edit'))
            ->_addLeft($this->getLayout()->createBlock('stores/adminhtml_location_edit_tabs'));
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->editAction();
    }

    public function saveAction()
    {
        $data = array();
		$ag = Mage::helper('stores');
		$connection = Mage::getSingleton('core/resource')->getConnection('core_write');

        if ($data = $this->getRequest()->getPost())
        {
            if(isset($_FILES) && count($_FILES) > 0)
            {
                $field_name = false;
                foreach($_FILES as $field_name => $field_value)
                {
                    if(empty($_FILES[$field_name]['name']))
                    {
                        continue;
                    }
                    try {
                        /* Starting upload */
                        $uploader = new Varien_File_Uploader($field_name);

                        // Any extention would work
                        $uploader->setAllowedExtensions(array('pdf','jpg','jpeg','gif','png'));
                        $uploader->setAllowRenameFiles(false);

                        // Set the file upload mode
                        // false -> get the file directly in the specified folder
                        // true -> get the file in the product like folders
                        //	(file.jpg will go in something like /media/f/i/file.jpg)
                        $uploader->setFilesDispersion(true);

                        // We set media as the upload dir
                        $path = Mage::getBaseDir('media') . DS . 'stores' . DS ;
                        $result = $uploader->save($path, $_FILES[$field_name]['name'] );

                    } catch (Exception $e) {
                        Mage::getSingleton('adminhtml/session')->addError('error:'.$e);
                    }

                    if($field_name)
                    {
                        //this way the name is saved in DB
                        $data[$field_name] = 'stores'.$result['file']; //$_FILES[$field_name]['name'];
                        $field_name = false;
                    }
                }
            }
            //Mage::getSingleton('adminhtml/session')->addError('error:'.print_r($data, true));

            try {

                //$data['url_key'] = $this->getRequest()->getParam('url_key');

                if(empty($data['url_key']))
                {
                    $data['url_key'] = $data['title'];
                }

                $model = Mage::getModel('stores/location')
                    ->addData($data)
                    ->setId($this->getRequest()->getParam('id'))
                ;
                $model->save();

				// get updated or newly inserted store location ID
                $data['id'] = $model->getId();

                // get clean and valid url_key
                $data['url_key'] = Mage::getModel('stores/location')->getUrlKey($data['url_key'], $data['id']);

				// update store with clean url key
                $model->setUrlKey($data['url_key']);

                // save model to commit updates
				$model->save();

                // now save url key to core url rewrite table
                if(Mage::getModel('stores/location')->saveUrlKey($data['url_key'], $data['id'], $data['stores']))
                {
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store location was successfully saved'));
                }

                // if save and continue then reload the edit page
                if ($this->getRequest()->getParam('back'))
				{
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
                // else redirect to manage stores page
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteUrlRewrite($id=0)
    {
        if($id){
            $collection = Mage::getModel('core/url_rewrite')->getCollection();
            $collection->addFieldToFilter('id_path', 'stores/'.$id);

            //check if record exists
            if($collection->count()){
                foreach($collection as $data){
                    $data->delete();
                }
            }
        }
        return 1;
    }

    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('stores/location');
                /* @var $model Mage_Rating_Model_Rating */
                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();
                $this->deleteUrlRewrite($this->getRequest()->getParam('id'));
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Store location was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $storesIds = $this->getRequest()->getParam('stores');
        if (!is_array($storesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select store location(s)'));
        } else {
            try {
                foreach ($storesIds as $storesId) {
                    $stores = Mage::getModel('stores/location')->load($storesId);
                    $stores->delete();
                    $this->deleteUrlRewrite($storesId);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($storesIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    protected function _isAllowed()
    {
	    return Mage::getSingleton('admin/session')->isAllowed('cms/stores');
    }

    protected function _validateSecretKey()
    {
        if ($this->getRequest()->getActionName()=='updateEmptyGeoLocations') {
            return true;
        }
        return parent::_validateSecretKey();
    }

    public function updateEmptyGeoLocationsAction()
    {
        set_time_limit(0);
        ob_implicit_flush();
        $collection = Mage::getModel('stores/location')->getCollection();
        $collection->getSelect()->where('latitude=0');
        foreach ($collection as $loc) {
            echo $loc->getTitle()."<br/>";
            $loc->save();
        }
        exit;
    }

    public function exportCsvAction() {
        $fileName = 'store-locations.csv';
        $collection = Mage::getModel('stores/location')->getCollection();
        $csv = '';

        $i = 0;
        $rows = array();
        $data = array();
        foreach($collection as $v){
            if(!$i++){
                foreach (array_keys($v->getData()) as $column) {
                    $data[] = '"'.$column.'"';
                }
                $csv.= implode(',', $data)."\n";
            }

            $csv.= $this->arrayToCsvString($v->getData())."\n";
        }
        $this->_prepareDownloadResponse($fileName, $csv);
    }

    public function arrayToCsvString($fields = array(), $delimiter = ',', $enclosure = '"') {
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value) {
            if (strpos($value, $delimiter) !== false ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0;$i<$len;$i++) {
                    if ($value[$i] == $escape_char) {
                        $escaped = 1;
                    } else if (!$escaped && $value[$i] == $enclosure) {
                        $str2 .= $enclosure;
                    } else {
                        $escaped = 0;
                    }
                        $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2.$delimiter;
            } else {
                $str .= $enclosure.$value.$enclosure.$delimiter;
            }
        }
        return substr($str,0,-1);
    }
}