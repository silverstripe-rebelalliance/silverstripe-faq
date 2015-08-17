<?php
/**
 * FAQ pagetype, displays Q & A related to the page.
 * Has a custom search index to add search capabilities to the page.
 * Can live in any part of the SiteTree
 */
class FAQPage extends Page {

	private static $db = array(
		'SinglePageLimit' => 'Int',
		'SearchFieldPlaceholder' => 'Varchar(124)',
		'SearchResultsSummary' => 'Varchar(255)',
		'SearchResultsTitle' => 'Varchar(255)',
		'SearchButtonText' => 'Varchar(124)',
		'NoResultsMessage' => 'Varchar(255)',
		'SearchNotAvailable' => 'Varchar(255)',
		'MoreLinkText' => 'Varchar(124)'
	);

	private static $defaults = array(
		'SinglePageLimit' => 0,
		'SearchFieldPlaceholder' => 'Ask us a question',
		'SearchResultsSummary' => 'Displaying %CurrentPage% of %TotalPages% pages for "%Query%"',
		'SearchResultsTitle' => 'FAQ Results',
		'SearchButtonText' => 'Search',
		'NoResultsMessage' => 'We couldn\'t find an answer to your question. Maybe try asking it in a different way, or check your spelling.',
		'SearchNotAvailable' => 'We are currently unable to search the website for you. Please try again later.',
		'MoreLinkText' => 'Read more'
	);

	private static $many_many = array(
		'SelectedFAQs' => 'FAQ'
	);

	private static $many_many_extraFields = array(
		'SelectedFAQs' => array(
			'SortOrder' => 'Int'
		)
	);

	private static $singular_name = 'FAQ Page';

	private static $description = 'FAQ search page';

	public function SelectedFAQs() {
		return $this->getManyManyComponents('SelectedFAQs')->sort('SortOrder');
	}

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$settings = new Tab('Settings', 'Settings');
		$fields->insertBefore($settings, 'PublishingSchedule');
		$fields->addFieldsToTab('Root.Settings', array(
			TextField::create('SinglePageLimit')
					->setDescription('
						If set higher than 0, limits results to that many and removes pagination.
					'),
			TextField::create('SearchFieldPlaceholder')
					 ->setDescription('Text to appear in the search field before the user enters their question'),
			TextField::create('SearchButtonText')
					 ->setDescription('Text for the search button'),
			TextField::create('SearchResultsTitle')
					 ->setDescription('Title for the FAQ search results'),
			TextareaField::create('NoResultsMessage')
					 ->setDescription('Text to appear when no search results are found'),
			TextareaField::create('SearchNotAvailable')
					 ->setDescription('Text to appear when search functionality is not available'),
			TextField::create('MoreLinkText')
					 ->setDescription('Text for the "Read more" link below each search result'),
			TextareaField::create('SearchResultsSummary')
					->setDescription('
						Search summary string. Replacement keys: 
						<ul>
							<li>
								<strong>%CurrentPage%</strong>: Current page number
							</li>
							<li>
								<strong>%TotalPages%</strong>: Total page count
							</li>
							<li>
								<strong>%Query%</strong>: Current search query
							</li>
						</ul>
					')
		));

		// Selected FAQs tab
		$components = GridFieldConfig_RelationEditor::create();
		$components->removeComponentsByType('GridFieldAddNewButton');
		$components->removeComponentsByType('GridFieldEditButton');
		$components->removeComponentsByType('GridFieldFilterHeader');
		$components->addComponent(new GridFieldSortableRows('SortOrder'));

		$dataColumns = $components->getComponentByType('GridFieldDataColumns');
		$dataColumns->setDisplayFields(array(
			'Title' => _t('FAQPage.ColumnQuestion', 'Ref.'),
			'Question' => _t('FAQPage.ColumnQuestion', 'Question'),
			'Answer.Summary' => _t('FAQPage.ColumnPageType', 'Answer')
		));

		$components->getComponentByType('GridFieldAddExistingAutocompleter')
			->setResultsFormat('$Question');

		$SelectedFAQsTab = new Tab('SelectedFAQs', _t('FAQPage.SelectedFAQs','Selected FAQs'));
		$fields->insertBefore($SelectedFAQsTab, 'PublishingSchedule');

		// logic regarding whether or not more SelectedFAQs can be added
		$limitSelectedFAQs = $this->SelectedFAQs()->count() >= $this->SinglePageLimit && $this->SinglePageLimit;

		if ($limitSelectedFAQs) {
			// prevent users from adding more SelectedFAQs
			$components->removeComponentsByType('GridFieldAddExistingAutocompleter');
		}

		$SelectedFAQsLimitNoticeContents = sprintf(
			'<p class="message %s">Limited by the Single Page Limit in the Settings tab (currently %s)</p>',
			$limitSelectedFAQs ? 'bad' : '', //make limit message red if we have to prevent adding more SelectedFAQs
			$this->SinglePageLimit ? $this->SinglePageLimit : 'no limit' //show 'currently no limit' if SinglePageLimit is '0'
		);

		$fields->addFieldsToTab(
			'Root.SelectedFAQs',
			array(
				LiteralField::create(
					'SelectedFAQsLimitNotice',
					$SelectedFAQsLimitNoticeContents
				),
				GridField::create(
					'SelectedFAQs',
					_t('FAQPage.SelectedFAQs','Selected FAQs'),
					$this->SelectedFAQs(),
					$components
				)
			)
		);


		return $fields;
	}

}

/**
 *
 */
class FAQPage_Controller extends Page_Controller {
	private static $allowed_actions = array('view');

	// This is the string used for the url search term variable. E.g. "searchterm" in "http://mysite/faq?searchterm=this+is+a+search"
	public static $search_term_key = 'q';
	// We replace these keys with real data in the SearchResultsSummary before adding to the template.
	public static $search_results_summary_current_page_key = '%CurrentPage%';
	public static $search_results_summary_total_pages_key = '%TotalPages%';
	public static $search_results_summary_query_key = '%Query%';

	// solr configuration
	public static $search_index_class = 'FAQSearchIndex';
	public static $classes_to_search = array(
		array(
			'class' => 'FAQ',
			'includeSubclasses' => true
		)
	);

	/*
	 * Renders the base search page if no search term is present.
	 * Otherwise runs a search and renders the search results page.
	 * Search action taken from FAQPage.php and modified.
	 */
	public function index() {
		if($this->request->getVar(self::$search_term_key)) {
			return $this->renderSearch($this->search());
		}
		
		return $this->render();
	}
	
	/**
	 * Render individual view for FAQ
	 * @return FAQ|404 error if faq not found
	 */
	public function view() {
		$faq = FAQ::get()->filter('ID', $this->request->param('ID'))->first();
		
		if ($faq === null) {
			$this->httpError(404);
		}

		return array('FAQ' => $faq);
	}



	/**
	 * Search function. Called from index() if we have a search term.
	 * @return HTMLText search results template.
	 */
	public function search() {
		// limit if required by cms config
		$limit = self::$results_per_page;
		if($this->SinglePageLimit != '0') {
			$setlimit = intval($this->SinglePageLimit);
			If($setlimit != 0 && is_int($setlimit)) {
				$limit = $setlimit;
			}
		}

		$start = $this->request->getVar('start') or 0;
		$results = new ArrayList();
		$suggestionData = null;
		$keywords = $this->request->getVar(self::$search_term_key) or '';
		$renderData = array();

		// get search query
		$query = $this->getSearchQuery($keywords);
		try {
			$searchResult = $this->doSearch($query, $start, $limit);

			$results = $searchResult->Matches;
			$suggestionData = array(
				'Suggestion' => $searchResult->Suggestion,
				'SuggestionNice' => $searchResult->SuggestionNice,
				'SuggestionQueryString' => $this->makeQueryLink($searchResult->SuggestionQueryString)
			);
			$renderData = $this->parseSearchResults($results, $suggestionData, $keywords);
		} catch(Exception $e) {
			$renderData = array('SearchError' => true);
			SS_Log::log($e, SS_Log::WARN);
		}

		return $renderData;
	}

	/**
	 * Builds a search query from a give search term.
	 * @return SearchQuery
	 */
	protected function getSearchQuery($keywords) {
		// stop Solr breaking questions
		$searchKeywords = preg_replace('/\?$/', '\?', $keywords);
		
		$query = new SearchQuery();
		$query->classes = self::$classes_to_search;
		$query->search($searchKeywords);

		// Artificially lower the amount of results to prevent too high resource usage.
		// on subsequent canView check loop.
		$query->limit(100);

		return $query;
	}

	/**
	 * Performs a search against the configured Solr index from a given query, start and limit.
	 * Returns $result and $suggestionData - both of which are passed by reference.
	 */
	public function doSearch($query, $start, $limit) {
		$result = singleton(self::$search_index_class)->search(
			$query,
			$start,
			$limit,
			array(
				'defType' => 'edismax',
				'hl' => 'true',
				'spellcheck' => 'true',
				'spellcheck.collate' => 'true'
			)
		);

		return $result;
	}

	/**
	 * Renders the search template from a given Solr search result, suggestion and search term.
	 * @return HTMLText search results template.
	 */
	protected function parseSearchResults($results, $suggestion, $keywords) {
		$searchSummary = '';

		// Clean up the results.
		foreach($results as $result) {
			if(!$result->canView()) $results->remove($result);
		}

		// Generate links
		$searchURL = Director::absoluteURL($this->makeQueryLink(urlencode($keywords)));
		$rssUrl = Controller::join_links($searchURL, '?format=rss');
		RSSFeed::linkToFeed($rssUrl, 'Search results for "' . $keywords . '"');
		$atomUrl = Controller::join_links($searchURL, '?format=atom');
		CwpAtomFeed::linkToFeed($atomUrl, 'Search results for "' . $keywords . '"');

		/**
		 * generate the search summary using string replacement
		 * to support translation and max configurability
		 */
		if ($results->CurrentPage) {
			$searchSummary = _t('FAQPage.SearchResultsSummary', $this->SearchResultsSummary);
			$keys = array(
				self::$search_results_summary_current_page_key,
				self::$search_results_summary_total_pages_key,
				self::$search_results_summary_query_key
			);
			$values = array(
				$results->CurrentPage(),
				$results->TotalPages(),
				$keywords
			);
			$searchSummary = str_replace($keys, $values, $searchSummary);
		}

		$renderData = array(
			'SearchResults' => $results,
			'SearchSummary' => $searchSummary,
			'SearchSuggestion' => $suggestion,
			'Query' => DBField::create_field('Text', $keywords),
			'SearchLink' => DBField::create_field('Text', $searchURL),
			'RSSLink' => DBField::create_field('Text', $rssUrl),
			'AtomLink' => DBField::create_field('Text', $atomUrl)
		);
		
		// remove pagination if required by cms config
		if($this->SinglePageLimit != '0') {
			$setlimit = intval($this->SinglePageLimit);
			$renderData['SearchResults']->setTotalItems($setlimit);
		}

		return $renderData;
	}
	
	/**
	 * Sets a template and displays data
	 */
	protected function renderSearch($renderData) {
		$templates = array('FAQPage_results', 'Page');
		if ($this->request->getVar('format') == 'rss') {
			array_unshift($templates, 'Page_results_rss');
		}
		if ($this->request->getVar('format') == 'atom') {
			array_unshift($templates, 'Page_results_atom');
		}

		return $this->customise($renderData)->renderWith($templates);
	}
	
	
	/**
	 * Makes a query link for the current page from a search term
	 * Returns a URL with an empty search term if no query is passed
	 * @return String  The URL for this search query
	 */
	protected function makeQueryLink($query = null) {
		$query = gettype($query) === 'string' ? $query : '';
		return Controller::join_links(
			Director::baseURL(),
			$this->Link(),
			sprintf('?%s=', self::$search_term_key)
		) . $query;
	}

	/**
	 * Expose variables to the template - both statics and data objects, and make the translatable where relevant.
	 */
	public function SearchFieldPlaceholder() {
		return _t('FAQPage.SearchFieldPlaceholder', $this->SearchFieldPlaceholder);
	}
	public function SearchButtonText() {
		return _t('FAQPage.SearchButtonText', $this->SearchButtonText);
	}
	public function NoResultsMessage() {
		return _t('FAQPage.NoResultsMessage', $this->NoResultsMessage);
	}
	public function SearchTermKey() {
		return self::$search_term_key;
	}
	public function SearchResultsTitle() {
		return _t('FAQPage.SearchResultsTitle', $this->SearchResultsTitle);
	}
	public function SearchResultMoreLink() {
		return _t('FAQPage.SearchResultMoreLink', $this->MoreLinkText);
	}
}
