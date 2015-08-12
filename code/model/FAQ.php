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

	/**
	 * Search boost defaults for fields.
	 *
	 * @var config
	 * @string 
	 */
	private static $question_boost = '3';
	private static $answer_boost = '1';
	private static $keywords_boost = '4';

	private static $summary_fields = array(
		'Question',
		'Answer' => 'Answer.Summary'
	);
	
	public function getLink() {
		//TODO not this
		$faqPage = FAQPage::get()->first();
		return $faqPage->Link()."view/".$this->ID;
	}

	/**
	 * Set required fields for model form submition.
	 */
	public function getCMSValidator() {
		return new RequiredFields('Question', 'Answer');
	}
}