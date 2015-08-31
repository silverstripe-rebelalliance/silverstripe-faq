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
		'Keywords' => 'Text',

		'RatingsLastReset' => 'Date',
		'Rating1' => 'Int',
		'Rating2' => 'Int',
		'Rating3' => 'Int',
		'Rating4' => 'Int',
		'Rating5' => 'Int'
	);

	private static $summary_fields = array(
		'Question' => 'Question',
		'Answer.Summary' => 'Answer',
		'Category.Name' => 'Category'
	);
	
	private static $has_one = array(
		'Category' => 'TaxonomyTerm'
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
	 * @config
	 */
	private static $taxonomy_name = 'FAQ Categories';

	/**
	 *
	 */
	public function populateDefaults() {
		$this->RatingsLastReset = date('Y-m-d');
		parent::populateDefaults();
	}

	/**
	 *
	 */
	public function getCMSFields() {
		$fields = parent::getCMSFields();
		
		// setup category dropdown field
		$taxonomyRoot = self::getRootCategory();
		$categoryField = new TreeDropdownField(
			'CategoryID', 
			'Category', 
			'TaxonomyTerm',
			'ID',
			'Name'
		);
		//change this to 0 if you want the root category to show
		$categoryField->setTreeBaseID($taxonomyRoot->ID);
		$categoryField->setDescription(sprintf(
					'Select one <a href="admin/taxonomy/TaxonomyTerm/EditForm/field/TaxonomyTerm/item/%d/#Root_Children">'
					. 'FAQ Category</a>',
					$categoryField->ID));
		$fields->addFieldToTab('Root.Main', $categoryField);

		$fields->removeByName('RatingsLastReset');
		$fields->removeByName('Rating1');
		$fields->removeByName('Rating2');
		$fields->removeByName('Rating3');
		$fields->removeByName('Rating4');
		$fields->removeByName('Rating5');

		$ratingsToggleField = ToggleCompositeField::create('Ratings', _t('SiteTree.FAQRatingsToggle', 'Ratings'),
			array(
				new LiteralField('RatingsDisplay', "
					<div class='ui-tabs-panel'>
					Ratings for this FAQ since {$this->RatingsLastReset}
						<p>
							<ul>
								<li><strong>1 star:</strong> <em>{$this->Rating1}</em></li>
								<li><strong>2 star:</strong> <em>{$this->Rating2}</em></li>
								<li><strong>3 star:</strong> <em>{$this->Rating3}</em></li>
								<li><strong>4 star:</strong> <em>{$this->Rating4}</em></li>
								<li><strong>5 star:</strong> <em>{$this->Rating5}</em></li>
							</ul>
						</p>
						
					</div>
				")
			)
		);

		$fields->addFieldToTab('Root.Main', $ratingsToggleField);

		return $fields;
	}
	
	/**
	 * Set required fields for model form submission.
	 */
	public function getCMSValidator() {
		return new RequiredFields('Question', 'Answer');
	}

	/**
	 * Filters items based on member permissions or other criteria,
	 * such as if a state is generally available for the current record.
	 * 
	 * @param Member
	 * @return Boolean
	 */
	public function canView($member = null) {
		return true;
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
	
	/**
	 * Gets all nested categories for FAQs
	 * TODO: this, if it's required by SUP-75 or SUP-76, if not, delete
	 */
	public static function getAllCategories() {
		$taxName = Config::inst()->get('FAQ', 'taxonomy_name');
		$root = FAQTaxonomyTermExtension::getOrCreate(array('Name'=> $taxName),
										  array('Name'=> $taxName, 'ParentID'=> 0));
		return $root->Children();
	}
	
	/**
	 * Gets the root category for the FAQs
	 * If it doesn't find it it creates it
	 */
	public static function getRootCategory() {
		$taxName = Config::inst()->get('FAQ', 'taxonomy_name');
		$root = FAQTaxonomyTermExtension::getOrCreate(array('Name'=> $taxName),
										  array('Name'=> $taxName, 'ParentID'=> 0));
		return $root;
	}
}