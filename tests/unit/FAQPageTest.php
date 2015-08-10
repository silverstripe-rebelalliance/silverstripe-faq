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
		$this->assertTrue(strpos($response->getBody(), 'id="FAQSearchForm_FAQSearchForm_Search"') !== false);
		
		// TODO this when basic template is done
		// check that page with search term doesn't show form and search results
		//$response = Director::test('faq-page-1/search?Search="test"');
		//$this->assertTrue(strpos($response2->getBody(), 'id="FAQSearchForm_FAQSearchForm_Search"') !== false);
		//$this->assertTrue(strpos($response2->getBody(), 'id="FAQSearchForm_FAQSearchForm_Search"') !== false);
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