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
		'SearchButtonValue' => 'Text',
		'NoResultsMessage' => 'Text',
		'MoreLinkText' => 'Text'
	);

	static $defaults = array(
		'SearchFieldPlaceholder' => 'Ask us a question',
		'SearchResultsSummary' => 'Displaying %CurrentPage% of %TotalPages% pages for "%Query%"',
		'SearchResultsTitle' => 'FAQ Results',
		'SearchButtonValue' => 'Search',
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

			TextField::create('SearchButtonValue')
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

	public static $search_term_key = 'q';
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
		$start = $this->request->getVar('start') or 0;
		$limit = self::$results_per_page;
		$results = new ArrayList();
		$suggestion = null;
		$keywords = $this->request->getVar(self::$search_term_key) or '';

		// render normally if no search term
		if(!$keywords) {
			return $this->render();
		// otherwise do search
		} else {
			$searchKeywords = preg_replace('/\?$/', '\?', $keywords); // stop Solr breaking questions
			$query = new SearchQuery();
			$query->classes = self::$classes_to_search;
			$query->search($searchKeywords);

			// Artificially lower the amount of results to prevent too high resource usage.
			// on subsequent canView check loop.
			$query->limit(100);

			try {
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

				$results = $result->Matches;
				$suggestion = $result->Suggestion;
			} catch(Exception $e) {
				SS_Log::log($e, SS_Log::WARN);
			}

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
	}

	public function SearchFieldPlaceholder() {
		return _t('FAQPage.SearchFieldPlaceholder', $this->SearchFieldPlaceholder);
	}
	public function SearchButtonValue() {
		return _t('FAQPage.SearchButtonValue', $this->SearchButtonValue);
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
