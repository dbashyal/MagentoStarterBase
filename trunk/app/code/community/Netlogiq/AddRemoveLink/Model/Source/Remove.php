<?php
class Netlogiq_AddRemoveLink_Model_Source_Remove {
	public function toOptionArray() {
		$array = array(
			array('label' => 'Account Dashboard','value' => 'account'),
			array('label' => 'Account Edit','value' => 'account_edit'),
			array('label' => 'Address Book','value' => 'address_book'),
			array('label' => 'My Orders','value' => 'orders'),
			array('label' => 'Billing Agreements','value' => 'billing_agreements'),
			array('label' => 'Recurring Profiles','value' => 'recurring_profiles'),
			array('label' => 'My Product Reviews','value' => 'reviews'),
			array('label' => 'My Wishlist','value' => 'wishlist'),
			array('label' => 'My Applications','value' => 'OAuth Customer Tokens'),
			array('label' => 'Newsletter Subscriptions','value' => 'newsletter'),
			array('label' => 'My Downloadable Products','value' => 'downloadable_products'),			
		);
		return $array;
	}

}
