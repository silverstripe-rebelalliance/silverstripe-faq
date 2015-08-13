<?php
/**
 * FAQ pagetype, displays Q & A related to the page.
 * Has a custom search index to add search capabilities to the page.
 * Can live in any part of the SiteTree
 */
class FAQPage extends Page {

	private static $db = array(
		'SearchFieldPlaceholder' => 'Text',
		'SearchResultsSummary' => 'Text',
		'SearchResultsTitle' => 'Text',
		'SearchButtonText' => 'Text',
		'NoResultsMessage' => 'Text',
		'MoreLinkText' => 'Text'
	);

	static $defaults = array(
		'SearchFieldPlaceholder' => 'Ask us a question',
		'SearchResultsSummary' => 'Displaying %CurrentPage% of %TotalPages% pages for "%Query%"',
		'SearchResultsTitle' => 'FAQ Results',
		'SearchButtonText' => 'Search',
		'NoResultsMessage' => 'We couldn\'t find an answer to your question. Maybe try asking it in a different way, or check your spelling.',
		'MoreLinkText' => 'Read more'
	);

	private static $singular_name = 'FAQ Page';

	private static $description = 'FAQ search page';

	public function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->addFieldsToTab('Root.Main', array(

			TextField::create('SearchFieldPlaceholder')
				->setDescription('Text to appear in the search field before the user enters their question'),

			TextField::create('SearchButtonText')
				->setDescription('Text for the search button'),

			TextField::create('SearchResultsTitle')
				->setDescription('Title for the FAQ search results'),

			TextareaField::create('NoResultsMessage')
				->setDescription('Text to appear when no search results are found'),

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

		), 'Metadata');
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

	public static $search_index_class = 'FAQSearchIndex';
	public static $classes_to_search = array(
		array(
			'class' => 'FAQ',
			'includeSubclasses' => true
		)
	);

	public function view() {
		$faq = FAQ::get()->filter('ID', $this->request->param('ID'))->first();
		
		if ($faq === null) {
			$this->httpError(404);
		}

		return array('FAQ' => $faq);
	}

	/*
	 * Renders the base search page if no search term is present.
	 * Otherwise runs a search and renders the search results page.
	 * Search action taken from BasePage.php and modified.
	 */
	public function index() {
		// render normally if no search term
		if(!$this->request->getVar(self::$search_term_key)) {
			return $this->render();
		// otherwise do search
		} else {
			return $this->search();
		}
	}

	/**
	 * Search function. Called from index() if we have a search term.
	 * @return HTMLText search results template.
	 */
	public function search() {
		$start = $this->request->getVar('start') or 0;
		$limit = self::$results_per_page;
		$results = new ArrayList();
		$suggestion = null;
		$keywords = $this->request->getVar(self::$search_term_key) or '';

		// get search query
		$query = $this->getSearchQuery($keywords);

		try {
			$this->doSearch($results, $suggestion, $query, $start, $limit, $keywords);
		} catch(Exception $e) {
			SS_Log::log($e, SS_Log::WARN);
		}

		return $this->renderSearch($results, $suggestion, $keywords);

	}

	/**
	 * Builds a search query from a give search term.
	 * @return SearchQuery
	 */
	public function getSearchQuery($keywords) {
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
	 * Returns $result and $suggestion - both of with are passed by reference.
	 */
	public function doSearch(&$results, &$suggestion, $query, $start, $limit) {
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

		// these are both passed by reference and are returned.
		$results = $result->Matches;
		$suggestion = $result->Suggestion;
	}

	/**
	 * Renders the search template from a given Solr search result, suggestion and search term.
	 * @return HTMLText search results template.
	 */
	public function renderSearch($results, $suggestion, $keywords) {
		// Clean up the results.
		foreach($results as $result) {
			if(!$result->canView()) $results->remove($result);
		}

		// Generate links
		$searchURL = Director::absoluteURL(Controller::join_links(
			Director::baseURL(),
			$this->Link(),
			sprintf('?%s=', self::$search_term_key).rawurlencode($keywords)
		));
		$rssUrl = Controller::join_links($searchURL, '?format=rss');
		RSSFeed::linkToFeed($rssUrl, 'Search results for "' . $keywords . '"');
		$atomUrl = Controller::join_links($searchURL, '?format=atom');
		CwpAtomFeed::linkToFeed($atomUrl, 'Search results for "' . $keywords . '"');

		/**
		 * generate the search summary using string replacement
		 * to support translation and max configurability
		 */
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

		$renderData = array(
			'SearchResults' => $results,
			'SearchSummary' => $searchSummary,
			'Suggestion' => DBField::create_field('Text', $suggestion),
			'Query' => DBField::create_field('Text', $keywords),
			'SearchLink' => DBField::create_field('Text', $searchURL),
			'RSSLink' => DBField::create_field('Text', $rssUrl),
			'AtomLink' => DBField::create_field('Text', $atomUrl)
		);

		$templates = array('FAQPage_results', 'Page');
		if ($this->request->getVar('format') == 'rss') {
			array_unshift($templates, 'Page_results_rss');
		}
		if ($this->request->getVar('format') == 'atom') {
			array_unshift($templates, 'Page_results_atom');
		}

		return $this->owner->customise($renderData)->renderWith($templates);
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
