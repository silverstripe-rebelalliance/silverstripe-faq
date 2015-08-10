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
	private static $allowed_actions = array('search');
	public static $search_index_class = 'FAQSearchIndex';
	public static $classes_to_search = array(
		array(
			'class' => 'FAQ',
			'includeSubclasses' => true
		)
	);
	
	public function getFAQs() {
		return FAQ::get();
	}

	public function index() {
		return $this->renderWith(array('FAQPage', 'Page'));
	}
	
	
	public function search($request) {
		$query = new SearchQuery();
		$query->classes = self::$classes_to_search;
		$query->search($request->getVar('Search'));
		//$query->exclude('SiteTree_ShowInSearch', 0);

		$results = '';
		try {
			$result = singleton(self::$search_index_class)->search(
				$query,
				0,
				2,
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
		
		return $this->customise(new ArrayData(array(
			'SearchResults' => $results
		)))->renderWith(array('FAQPage', 'Page'));
	}
}
