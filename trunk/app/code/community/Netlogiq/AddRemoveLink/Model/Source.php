<?php
class Netlogiq_AddRemoveLink_Model_Source {
	public function toOptionArray() {
		$collection = Mage::getModel('cms/page') -> getCollection() -> getItems();
		$array = array();
		foreach ($collection as $key => $cms_page) {
			$array[] = array('value' => $cms_page -> getIdentifier(),'label' => $cms_page -> getTitle());
		}
		return $array;
	}

}
