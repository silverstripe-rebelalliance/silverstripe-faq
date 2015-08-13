<?php

/**
 * FAQ Module Unit Tests
 */
class FAQTest extends SapphireTest {
	
	/**
	 * Link() functionality, returns a link to view the detail page for FAQ
	 */
	public function testLink() {
		// no controller or object created, shouldn't get a link
		$faq = new FAQ();
		$this->assertEquals('', $faq->getLink());
		
		// object created, should get a link
		$faq1 = new FAQ(array('Question' => 'question 1',
							  'Answer' => 'Milkyway chocolate bar'));
		$faq1->write();
		$this->assertNotEquals('', $faq1->getLink());
	}
}
