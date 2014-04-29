<?php

class Netlogiq_AddRemoveLink_Block_Account_Navigation extends Mage_Customer_Block_Account_Navigation {
	/**
	 * Removes a link by name
	 *
	 * @param $name string
	 * @return Gravitywell_Customer_Block_Account_Navigation
	 */
	public function removeLinkByName($name) {
		foreach ($this->_links as $k => $v) {
			if ($v -> getName() == $name) {
				unset($this -> _links[$k]);
			}
		}

		return $this;
	}

	/*
	 *
	 * 	Netlogiq - custom getLinks to add the storeConfig pages - Netlogiq
	 *
	 * */

	public function getLinks() {
		$cms_pages = explode(',', Mage::getStoreConfig('addremovelink/addremovelink/lista_atribute'));
		$base_pages_to_delete = explode(',', Mage::getStoreConfig('addremovelink/addremovelink/lista_atribute_de_sters'));
		$path_array = array();
		
		foreach ($this ->_links as $key => $_link) {
			if(in_array($_link -> getName(),$base_pages_to_delete))
				unset($this -> _links[$key]);
			$path_array[] = $_link -> getPath();
		}
		foreach ($cms_pages as $cms_identifier) {
			$cms_page = Mage::getModel('cms/page') -> load($cms_identifier, 'identifier');
			if (!in_array($cms_page -> getIdentifier(), $path_array)) {
				$link = new Varien_Object();
				$link -> setName($cms_page -> getIdentifier());
				$link -> setPath($cms_page -> getIdentifier());
				$link -> setLabel($cms_page -> getTitle());
				$link -> setUrl(rtrim($cms_page -> getUrl(),'/') . '/');
				$this -> _links[] = $link;
			}
		}
		return $this -> _links;
	}

	/**
	 * Removes a link by label
	 *
	 * This is useful because sometimes, the links aren't named.
	 *
	 * @param $label string
	 * @return Gravitywell_Customer_Block_Account_Navigation
	 */
	public function removeLinkByLabel($label) {
		foreach ($this->_links as $k => $v) {
			if ($v -> getLabel() == $label) {
				unset($this -> _links[$k]);
			}
		}

		return $this;
	}

}
?>