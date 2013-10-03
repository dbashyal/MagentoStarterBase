<?php
/**
 * Fontis Australia Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com and you will be sent a copy immediately.
 *
 * @category   Fontis
 * @package    Fontis_Australia
 * @author     Tom Greenaway
 * @copyright  Copyright (c) 2008 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fontis_Australia_Model_GetPrice_Cron extends Fontis_Australia_Model_FeedCronBase {

    public $config_path = 'getpricefeed';
    public $generate_categories = true;
    protected $generate_product_category = true;
    protected $product_doc;
    protected $category_doc;
    protected $root_node;
    protected $required_fields = array(
        "product_num" => "product_num",
        "sku"   =>  "upc",
        "name" => "attribute1",
        "description" => "description",
        "category" => "category_name",
        "image_url" => "image",
        "final_price" => "price",
        "currency" => "currency",
        "link" => "product_url"
    );

    public static function update() {
        // Static launcher fopr Magento's cron logic
        $obj = new self();
        $obj->generateFeed();
    }

    protected function setupStoreData() {
        $this->product_doc = new SimpleXMLElement('<store url="' . $this->info("store_url") . '" date="'. $this->info("date") .'" time="'. $this->info("time") .'" name="' . $this->info("shop_name") . '"></store>');
    }

    protected function populateFeedWithBatchData($batch_data) {
        $fields = $this->collectAttributeMapping();
        $this->root_node = $this->product_doc->addChild('products');
        foreach($batch_data as $product) {
            $product_node = $this->root_node->addChild('product');
            foreach($fields as $key => $feed_tag) {
                // remove carriage return/HTML tags
                $safe_string = strip_tags($product[$key]);
                $safe_string = preg_replace("/\s*\n\s*/", " ", $safe_string);
                // ...we also need to make it XML safe
                $safe_string = htmlspecialchars($safe_string);
                $product_node->addChild($feed_tag, $safe_string);
            }
        }
    }

    protected function getCategoriesXml() {
        $result = array();
        $categories = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($this->store)
                ->addAttributeToFilter('is_active', 1);
        $categories->load()->getItems();

        $full_categories = array();

        foreach($categories as $category) {
            $id = $category->getId();
            $category = Mage::getModel('catalog/category')->load($id);

            $children = $category->getAllChildren(true);
            if (count($children) <= 1) {
                $full_categories[] = $category;
            }
        }

        $this->category_doc = new SimpleXMLElement('<store url="' . $this->info("store_url") . '" date="' . $this->info("date") . '" time="' . $this->info("time") . '" name="' . $this->info("shop_name") . '"></store>');

        foreach($full_categories as $category) {
            $category_node = $this->category_doc->addChild('cat');

            $title_node = $category_node->addChild('name');
            $title_node[0] = htmlspecialchars($category->getName());

            $link_node = $category_node->addChild('link');
            $link_node[0] = Mage::getStoreConfig('web/unsecure/base_url', $this->store) .
                    Mage::getStoreConfig('fontis_feeds/'. $this->config_path . '/output', $this->store) . $this->info('clean_store_name') ."-products-" . $category->getId() . '.xml';

            $result['link_ids'][] = $category->getId();
        }       
        // create categories XML feed
        $this->createXml(array(
                                            'name' => "categories",
                                            'doc' => $this->category_doc
                                        ));
        return $result;
    }

    protected  function createXml($data = array()) {
        // Use DOM to nicely format XML
        $dom = new DOMDocument('1.0');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom_sxml = dom_import_simplexml($data['doc']);
        $dom_sxml = $dom->importNode($dom_sxml, true);
        $dom->appendChild($dom_sxml);
        // $this->log("Generated XML:\n".$dom->saveXML());

        // Write dom to file
        $filename = $this->info("clean_store_name") . '-' . $data['name'] . '.xml';
        $io = new Varien_Io_File();
        $io->setAllowCreateFolders(true);
        $io->open(array('path' => $this->getPath()));
        $io->write($filename, $dom->saveXML());
        $io->close();

    }

    protected function finaliseStoreData($cat_id = null) {
        $this->createXml(array(
                                            'name' => $cat_id ? "products-".$cat_id : "products",
                                            'doc' => $this->product_doc
                                            ));
    }
}
