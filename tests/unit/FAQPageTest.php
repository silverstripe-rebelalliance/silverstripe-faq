<?php
/**
 * Tests basic functionality of FAQPage
 */
class FAQPageTest extends FunctionalTest {
	public function setUp() {
		parent::setUp();

		// create faq page
		$page = new FAQPage(array('Title' => "FAQ Page 1"));
		$page->write();
		$page->publish('Stage', 'Live');

		$this->controller = Injector::inst()->create('FAQPage_Controller');
    }

	/**
	 * Basic load page test
	 */
	public function testIndex() {
		$page = $this->get('faq-page-1/');

		// faq page should load..
		$this->assertEquals(200, $page->getStatusCode());
	}
}