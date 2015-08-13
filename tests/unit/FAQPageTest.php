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
		
		$faq1 = new FAQ(array('Question' => 'question 1',
							  'Answer' => 'Milkyway chocolate bar'));
		$faq1->write();
		$faq2 = new FAQ(array('Question' => 'No imagination question',
							  'Answer' => '42'));
		$faq2->write();

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
		
		// check that page with search term shows form and search results (solr not available error)
		$response = Director::test(sprintf('faq-page-1/?%s=test', FAQPage_Controller::$search_term_key));
		$this->assertTrue(strpos($response->getBody(), 'id="FAQSearchForm_FAQSearchForm_Search"') !== false);
		
		// CHECK this isn't working
		//$this->assertTrue(strpos($response->getBody(), FAQPage::$defaults['SearchNotAvailable']) !== false);
	}
	
	/**
	 * Tests individual view  for FAQ
	 * TODO: change after slug change
	 */
	public function testView() {
		// test routing
		$page = $this->get('faq-page-1/view/1');
		$this->assertEquals(200, $page->getStatusCode());
		
		$page = $this->get('faq-page-1/view/665');
		$this->assertEquals(404, $page->getStatusCode());
		
		// test page body, we have to get the Q and the A
		$response = Director::test('faq-page-1/view/1');
		$this->assertTrue(strpos($response->getBody(), 'question 1') !== false);
		$this->assertTrue(strpos($response->getBody(), 'Milkyway chocolate bar') !== false);
		
		$response = Director::test('faq-page-1/view/2');
		$this->assertTrue(strpos($response->getBody(), 'No imagination question') !== false);
	}
	
	/**
	 *
	 */
	public function testSearch() {
		Phockito::include_hamcrest();
		$faq = new FAQ(array('Question' => 'question 1', 'Answer' => 'answer 1'));
		$result = new ArrayList(array($faq));
		$mockResponse = new PaginatedList($result);

		// setup the spy
		$spy = Phockito::spy('FAQPage_Controller');
		Phockito::when($spy)->doSearch($mockResponse, anything(), anything(), anything(), anything())->return($mockResponse);
		
		$response = Director::test(sprintf('faq-page-1/?%s=test', FAQPage_Controller::$search_term_key));
Debug::dump($response);
	}
}