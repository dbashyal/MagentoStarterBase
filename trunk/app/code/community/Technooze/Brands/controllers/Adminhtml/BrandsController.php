<?php

class Technooze_Brands_Adminhtml_BrandsController extends Mage_Adminhtml_Controller_action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('brands/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    /**
     * Instantiate urlrewrite, product and category
     *
     * @return Mage_Adminhtml_UrlrewriteController
     */
    protected function _initRegistry()
    {
        //$this->_title($this->__('Brands Page Manager'));

        // initialize urlrewrite, product and category models
        Mage::register('current_urlrewrite', Mage::getModel('core/url_rewrite')
                ->load($this->getRequest()->getParam('rewrite_id', 0))
        );

        $brandsId = $this->getRequest()->getParam('rewrite_id', 0);

        if (Mage::registry('current_urlrewrite')->getRewriteId()) {
            $brandsId = Mage::registry('current_urlrewrite')->getBrandsId();
        }

        Mage::register('current_brand', Mage::getModel('brands/brands')->load($brandsId));


        //return $this;
    }

    public function _urlTitle($str, $separator = 'dash', $lowercase = TRUE)
    {
        if ($separator == 'dash') {
            $search = '_';
            $replace = '-';
        }
        else
        {
            $search = '-';
            $replace = '_';
        }

        $trans = array(
            '&\#\d+?;' => '',
            '&\S+?;' => '',
            '\s+' => $replace,
            '[^a-z0-9\-\._]' => '',
            $replace . '+' => $replace,
            $replace . '$' => $replace,
            '^' . $replace => $replace,
            '\.+$' => ''
        );

        $str = strip_tags($str);

        foreach ($trans as $key => $val)
        {
            $str = preg_replace("#" . $key . "#i", $val, $str);
        }

        if ($lowercase === TRUE) {
            $str = strtolower($str);
        }

        return trim(stripslashes($str));
    }

    /**
     * Urlrewrite save action
     *
     */
    public function _saveUrlKey($params, $request_path)
    {
        if (!is_array($params) || count($params) < 1 || empty($request_path)) return FALSE;

        if (!Mage::registry('current_brand')) $this->_initRegistry();

        try {
            // set basic urlrewrite data
            $model = Mage::registry('current_urlrewrite');
            $id_path = 'brands/' . $params['id'];
            $target_path = 'brands/index/selected/id/' . $params['id'] . '/?manufacturer=' . $params['manufacturer_id'];
            $options = '';
            $description = '';
            //$request_path = $params['url_key'];

            $model->setIdPath($id_path)
                ->setTargetPath($target_path)
                ->setOptions($options)
                ->setDescription($description)
                ->setRequestPath($request_path);

            if (!$model->getId()) {
                $model->setIsSystem(0);
            }
            if (!$model->getIsSystem()) {
                $store_id = $this->getRequest()->getParam('store_id', 0);
                if ($store_id < 1) $store_id = Mage::app()->getStore()->getStoreId();

                $model->setStoreId($store_id);
            }

            // save and redirect
            $model->save();

            return $request_path;
        }
        catch (Exception $e) {
            /*
               Mage::getSingleton('adminhtml/session')
                   ->addError($e->getMessage())
                   //->setUrlrewriteData($data)
               ;
               */
            //Mage::unregister('url_key_accepted');

            //Mage::register('url_key_accepted', 'NO');
            // return intentionally omitted
        }
    }

    public function indexAction()
    {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('brands/brands')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('brands_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('brands/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('brands/adminhtml_brands_edit'))
                ->_addLeft($this->getLayout()->createBlock('brands/adminhtml_brands_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('brands')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function saveAction()
    {

        if ($data = $this->getRequest()->getPost()) {
            $delete = array();
            $id = $this->getRequest()->getParam('id');

            if (isset($data['logo_small']['delete'])) {
                $delete[] = 'logo_small';
                unset($data['logo_small']);
            }
            if (isset($data['logo_small']['value'])) {
                $data['logo_small'] = $data['logo_small']['value'];
            }
            if (isset($_FILES['logo_small']['name']) && $_FILES['logo_small']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('logo_small');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(true);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'brands' . DS;
                    $result = $uploader->save($path, $_FILES['logo_small']['name']);

                    //this way the name is saved in DB
                    $data['logo_small'] = 'brands' . $result['file']; //$_FILES['logo_small']['name'];

                } catch (Exception $e) {}
            }

            if (isset($data['logo_medium']['delete'])) {
                $delete[] = 'logo_medium';
                unset($data['logo_medium']);
            }
            if (isset($data['logo_medium']['value'])) {
                $data['logo_medium'] = $data['logo_medium']['value'];
            }
            if (isset($_FILES['logo_medium']['name']) && $_FILES['logo_medium']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('logo_medium');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(true);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'brands' . DS;
                    $result = $uploader->save($path, $_FILES['logo_medium']['name']);

                    //this way the name is saved in DB
                    $data['logo_medium'] = 'brands' . $result['file']; //$_FILES['logo_medium']['name'];

                } catch (Exception $e) {}
            }

            if (isset($data['logo_large']['delete'])) {
                $delete[] = 'logo_large';
                unset($data['logo_large']);
            }
            if (isset($data['logo_large']['value'])) {
                $data['logo_large'] = $data['logo_large']['value'];
            }
            if (isset($_FILES['logo_large']['name']) && $_FILES['logo_large']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('logo_large');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(true);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'brands' . DS;
                    $result = $uploader->save($path, $_FILES['logo_large']['name']);

                    //this way the name is saved in DB
                    $data['logo_large'] = 'brands' . $result['file']; //$_FILES['logo_large']['name'];

                } catch (Exception $e) {}
            }

            if (isset($data['body_image']['delete'])) {
                $delete[] = 'body_image';
                unset($data['body_image']);
            }
            if (isset($data['body_image']['value'])) {
                $data['body_image'] = $data['body_image']['value'];
            }
            if (isset($_FILES['body_image']['name']) && $_FILES['body_image']['name'] != '') {
                try {
                    /* Starting upload */
                    $uploader = new Varien_File_Uploader('body_image');

                    // Any extention would work
                    $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
                    $uploader->setAllowRenameFiles(false);

                    // Set the file upload mode
                    // false -> get the file directly in the specified folder
                    // true -> get the file in the product like folders
                    //	(file.jpg will go in something like /media/f/i/file.jpg)
                    $uploader->setFilesDispersion(true);

                    // We set media as the upload dir
                    $path = Mage::getBaseDir('media') . DS . 'brands' . DS;
                    $result = $uploader->save($path, $_FILES['body_image']['name']);

                    //this way the name is saved in DB
                    $data['body_image'] = 'brands' . $result['file']; //$_FILES['logo_large']['name'];

                } catch (Exception $e) {}
            }

            /*Remove previous data, if delete is selected*/
            $model = Mage::getModel('brands/brands');
            $model->load($id);
            foreach ($delete as $f)
            {
                $model->setData($f, '');
            }
            $model->save();

            /*Save new data*/
            $model = Mage::getModel('brands/brands');
            $model->setData($data)
                ->setId($this->getRequest()->getParam('id'));

            try {
                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }


                $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                if (isset($data['manufacturer_id'])) {
                    $eav_value = $connection->fetchOne("SELECT value FROM eav_attribute_option_value WHERE option_id = '" . $data['manufacturer_id'] . "'");

                    $model->setManufacturer($eav_value); //
                    $data['manufacturer'] = $eav_value;
                }

                if (empty($data['url_key'])) $data['url_key'] = $model->getManufacturer();

                //URL key
                $url_key = $this->_urlTitle($data['url_key']);

                if (!$model->getId()) {
                    $model->save();
                }

                $data['id'] = $model->getId(); //$this->getRequest()->getParam('id');
                $data['rewrite_id'] = 0;

                $rewrite_id = $connection->fetchOne("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/" . $model->getId() . "'");
                if ($rewrite_id) $data['rewrite_id'] = $rewrite_id;

                $data['rewrite_id'] = $_POST['rewrite_id'] = $data['rewrite_id'];

                $this->_saveUrlKey($data, $url_key);

                $i = 1;
                $url_key_new = $url_key;

                //$url_key_proceed = Mage::getSingleton('core/session', array('name'=>'frontend'))->getUrlKeyAccepted();
                $key_updated = $connection->fetchOne("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/" . $model->getId() . "' and request_path = '" . $url_key . "'");
                //Mage::log("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/".$model->getId()."' and request_path = '".$url_key."': " . print_r($key_updated, true));

                while ($key_updated < 1) //!$key_updated
                {
                    $rewrite_id = $connection->fetchOne("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/" . $model->getId() . "'");
                    if ($rewrite_id) $data['rewrite_id'] = $rewrite_id;

                    $data['id'] = $model->getId();

                    $url_key_new = $url_key . '-' . $i++;

                    //Mage::log(print_r($data, true) . ": $url_key_new");

                    $this->_saveUrlKey($data, $url_key_new);

                    $key_updated = $connection->fetchOne("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/" . $model->getId() . "' and request_path = '" . $url_key_new . "'");

                    //Mage::log("SELECT url_rewrite_id FROM core_url_rewrite WHERE id_path = 'brands/".$model->getId()."' and request_path = '".$url_key."': " . print_r($key_updated, true));
                    //
                }

                $data['url_key'] = $url_key_new;

                $model->setUrlKey($url_key_new);

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('brands')->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('brands')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $model = Mage::getModel('brands/brands');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
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
        $brandsIds = $this->getRequest()->getParam('brands');
        if (!is_array($brandsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($brandsIds as $brandsId) {
                    $brands = Mage::getModel('brands/brands')->load($brandsId);
                    $brands->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($brandsIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $brandsIds = $this->getRequest()->getParam('brands');
        if (!is_array($brandsIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($brandsIds as $brandsId) {
                    $brands = Mage::getSingleton('brands/brands')
                        ->load($brandsId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($brandsIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName = 'brands.csv';
        $content = $this->getLayout()->createBlock('brands/adminhtml_brands_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName = 'brands.xml';
        $content = $this->getLayout()->createBlock('brands/adminhtml_brands_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType = 'application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}