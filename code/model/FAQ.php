<?php

/**
 * DataObject for a single FAQ related to the FAQ search module.
 * Provides db fields for a question and an answer.
 * @see FAQAdmin for FAQ ModelAdmin.
 */
class FAQ extends DataObject {
	private static $db = array(
		'Question' => 'Varchar(255)',
		'Answer' => 'HTMLText',
		'Keywords' => 'Text'
	);
	
	private static $summary_fields = array(
		'Question',
		'Answer' => 'Answer.Summary'
	);

	/**
	 * Search boost defaults for fields.
	 *
	 * @config
	 * @var config
	 * @string 
	 */
	private static $question_boost = '3';
	
	/**
	 * @config
	 */
	private static $answer_boost = '1';
	
	/**
	 * @config
	 */
	private static $keywords_boost = '4';
	
	/**
	 * Set required fields for model form submission.
	 */
	public function getCMSValidator() {
		return new RequiredFields('Question', 'Answer');
	}

	/**
	 * Gets a link to the view page for each FAQ
	 * @return string Link to view this particular FAQ on the current FAQPage.
	 */
	public function getLink() {
		$faqPage = Controller::curr();

		if ($faqPage->exists() && $this->ID != 0) {
			return Controller::join_links(
				$faqPage->Link(),
				"view/",
				$this->ID
			);
		}
		
		return '';
	}

	/**
	 * @return string "Read more" link text for the current FAQPage.
	 */
	public function getMoreLinkText() {
		$faqPage = Controller::curr();

		if ($faqPage->exists() && $faqPage->ClassName === 'FAQPage') {
			return $faqPage->MoreLinkText;
		}
		
		return '';
	}
}