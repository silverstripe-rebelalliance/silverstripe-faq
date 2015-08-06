<?php

class FAQPage extends Page {
	private static $singular_name = 'FAQ Page';

	private static $description = 'FAQ search page with answers.';

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		return $fields;
	}
}

class FAQPage_Controller extends Page_Controller {

}