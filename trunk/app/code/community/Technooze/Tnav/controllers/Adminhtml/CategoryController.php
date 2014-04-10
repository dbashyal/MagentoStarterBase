<?php

class Technooze_Tnav_Adminhtml_CategoryController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Category Manager'), Mage::helper('adminhtml')->__('Category Manager'));

		return $this;
	}

	public function indexAction() {
		$this->_initAction()->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('tnav/tnav')->load($id);

        echo $model->getId();
	}
}