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
class Technooze_Stores_Helper_Data extends Mage_Core_Helper_Data
{
    /**
     * @var array
     */
    protected $_locations = array();

    /**
     * @param $id
     * @return mixed
     */
    public function getLocation($id)
    {
        if (!isset($this->_locations[$id])) {
            $location = Mage::getModel('stores/location')->load($id);
            $this->_locations[$id] = $location->getId() ? $location : false;
        }
        return $this->_locations[$id];
    }

    /**
     *
     * @return void
     */

    /**
     * Instantiate urlrewrite
     * @param string $module
     * @param string $file
     */
    function _initRegistry($module='', $file='')
    {
        $ag = Mage::app();

        // initialize urlrewrite, product and category models
        Mage::register('current_urlrewrite', Mage::getModel('core/url_rewrite')
            ->load($ag->getRequest()->getParam('rewrite_id', 0))
        );

        $myId  = $ag->getRequest()->getParam('rewrite_id', 0);

        if(Mage::registry('current_urlrewrite')->getRewriteId())
        {
            $myId = Mage::registry('current_urlrewrite')->getStoresId();
        }

        Mage::register('current_'.$module, Mage::getModel("{$module}/{$file}")->load($myId));
    }

    /**
     * @param $str
     * @param string $separator
     * @param bool $lowercase
     * @return string
     */
    function _urlTitle($str, $separator = 'dash', $lowercase = TRUE)
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
    function _saveUrlKey($module, $file = '', $params, $request_path=false)
    {
        if (!is_array($params) || count($params) < 1 || empty($request_path)) return FALSE;

        $request_path = $request_path . '.html';

        if (!Mage::registry('current_' . $module)) $this->_initRegistry($module, $file);

        try {
            // set basic urlrewrite data
            $model = Mage::registry('current_urlrewrite');
            $id_path = $module . '/' . $params['id'];
            $target_path = $module . '/index/selected/id/' . $params['id'] . '/';

            if ($module == 'brands') $target_path .= '?manufacturer[]=' . $params['manufacturer_id'];

            $options = '';
            $description = '';

            $model->setIdPath($id_path)
                ->setTargetPath($target_path)
                ->setOptions($options)
                ->setDescription($description)
                ->setRequestPath($request_path);

            if (!$model->getId()) {
                $model->setIsSystem(0);
            }
            if (!$model->getIsSystem()) {
                $store_id = Mage::app()->getRequest()->getParam('store_id', 0);
                if ($store_id < 1) $store_id = Mage::app()->getStore()->getStoreId();

                $model->setStoreId($store_id);
            }

            // save and redirect
            $model->save();

            return $request_path;
        }
        catch (Exception $e) {
            /*
                  Mage::getSingleton('core/session')
                      ->addError($e->getMessage())
                      //->setUrlrewriteData($data)
                  ;
                  */
            //Mage::unregister('url_key_accepted');

            //Mage::register('url_key_accepted', 'NO');
            // return intentionally omitted
        }
    }
}
