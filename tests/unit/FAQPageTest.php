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
		// faq page should load..
		$page = $this->get('faq-page-1/');
		$this->assertEquals(200, $page->getStatusCode());
		
		// check that page without search term shows form
		$response = Director::test('faq-page-1');
		$this->assertTrue(strpos($response2->getBody(), 'FAQ Page 1') !== false);
		
		// check that page with search term doesn't show title or content, but form and search results
	}
	
	public function testView() {
		//TODO
		// test routing
		// test slug
		//test body stuff
	}
	
	public function testResults() {
		//test
	}
}