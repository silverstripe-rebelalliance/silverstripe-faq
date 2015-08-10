<?php
/**
 * FAQ pagetype, displays Q & A related to the page.
 * Has a custom search index to add search capabilities to the page.
 * Can live in any part of the SiteTree
 */
class FAQPage extends Page {
	private static $singular_name = 'FAQ Page';

	private static $description = 'FAQ search page';
}

/**
 *
 */
class FAQPage_Controller extends Page_Controller {
	private static $allowed_actions = array('SearchForm', 'results', 'view');
	public static $search_index_class = 'FAQSearchIndex';
	public static $classes_to_search = array(
		array(
			'class' => 'FAQ',
			'includeSubclasses' => true
		)
	);

	public function index() {
		return $this->renderWith(array('FAQPage', 'Page'));
	}
	
	public function view() {
		// TODO slug
		$faq = FAQ::get()->filter('ID', $this->request->param('ID'))->first();
		
		if ($faq === null) {
			$this->httpError(404);
		}
		
		return array('FAQ' => $faq);
	}
	
	public function SearchForm() {
		$searchText =  _t('SearchForm.SEARCH', 'Search');

		if($this->owner->request && $this->owner->request->getVar('Search')) {
			$searchText = $this->owner->request->getVar('Search');
		}

		$fields = new FieldList(
			TextField::create('Search', false, $searchText)
		);
		$actions = new FieldList(
			new FormAction('results', _t('SearchForm.GO', 'Go'))
		);

		$form = new SearchForm($this->owner, 'SearchForm', $fields, $actions);
		$form->setFormAction($this->Link().'SearchForm');

		return $form;
	}
	
	
	public function results($data, $form, $request) {
		$start = isset($data['start']) ? $data['start'] : 0;
		$limit = self::$results_per_page;
		$results = new ArrayList();
		$suggestion = null;
		$keywords = empty($data['Search']) ? '' : $data['Search'];

		if($keywords) {
			$query = new SearchQuery();
			$query->classes = self::$classes_to_search;
			$query->search($keywords);

			// Artificially lower the amount of results to prevent too high resource usage.
			// on subsequent canView check loop.
			$query->limit(100);

			try {
				$result = singleton(self::$search_index_class)->search(
					$query,
					$start,
					$limit,
					array(
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
		}

		// Clean up the results.
		foreach($results as $result) {
			if(!$result->canView()) $results->remove($result);
		}

		// Generate links
		$searchURL = Director::absoluteURL(Controller::join_links(
			Director::baseURL(),
			'search/SearchForm?Search='.rawurlencode($keywords)
		));
		$rssUrl = Controller::join_links($searchURL, '?format=rss');
		RSSFeed::linkToFeed($rssUrl, 'Search results for "' . $keywords . '"');
		$atomUrl = Controller::join_links($searchURL, '?format=atom');
		CwpAtomFeed::linkToFeed($atomUrl, 'Search results for "' . $keywords . '"');

		$data = array(
			'PdfLink' => '',
			'SearchResults' => $results,
			'Suggestion' => DBField::create_field('Text', $suggestion),
			'Query' => DBField::create_field('Text', $keywords),
			'SearchLink' => DBField::create_field('Text', $searchURL),
			'Title' => _t('SearchForm.SearchResults', 'Search Results'),
			'RSSLink' => DBField::create_field('Text', $rssUrl),
			'AtomLink' => DBField::create_field('Text', $atomUrl)
		);

		$templates = array('FAQPage', 'Page');
		if ($request->getVar('format') == 'rss') {
			array_unshift($templates, 'Page_results_rss');
		}
		if ($request->getVar('format') == 'atom') {
			array_unshift($templates, 'Page_results_atom');
		}

		return $this->owner->customise($data)->renderWith($templates);
	}
}
