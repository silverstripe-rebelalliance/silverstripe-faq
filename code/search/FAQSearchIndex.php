<?php
/**
 * The saddest Solr index in history
 */
class FAQSearchIndex extends CwpSearchIndex {
	/**
	 *
	 */
	public function init() {
		$this->addClass('FAQ');
		$this->addFulltextField('Question');
		$this->addFulltextField('Answer');
		$this->addFulltextField('Keywords');
		$this->setFieldBoosting('FAQ_Question', FAQ::config()->question_boost);
		$this->setFieldBoosting('FAQ_Answer', FAQ::config()->answer_boost);
		$this->setFieldBoosting('FAQ_Keywords', FAQ::config()->keywords_boost);
	}
	
	/**
	 * Overwrite extra and template paths functions to only use the path defined on the yaml file
	 * We can create/overwrite new .txt templates for only this index
	 */
	public function getExtrasPath() {
		// get options from configuration
		$options = Config::inst()->get('FAQSearchIndex', 'options');
		
		$globalOptions = Solr::solr_options();
		if (isset($options['extraspath']) && file_exists($options['extraspath'])) {
			$globalOptions['extraspath'] = $options['extraspath'];
		}
		return $this->extrasPath ? $this->extrasPath : $globalOptions['extraspath'];
	}
	function getTemplatesPath() {
		$options = Config::inst()->get('FAQSearchIndex', 'options');

		$globalOptions = Solr::solr_options();
		if (isset($options['templatespath']) && file_exists($options['templatespath'])) {
			$globalOptions['templatespath'] = $options['templatespath'];
		}
		return $this->templatesPath ? $this->templatesPath : $globalOptions['templatespath'];
	}


	/**
	 * Overloaded to remove compulsory matching on all words
	 */
	protected function getQueryComponent(SearchQuery $searchQuery, &$hlq = array()) {
		$q = array();
		foreach ($searchQuery->search as $search) {
			$text = $search['text'];
			preg_match_all('/"[^"]*"|\S+/', $text, $parts);

			$fuzzy = $search['fuzzy'] ? '~' : '';

			foreach ($parts[0] as $part) {
				$fields = (isset($search['fields'])) ? $search['fields'] : array();
				if(isset($search['boost'])) {
					$fields = array_merge($fields, array_keys($search['boost']));
				}
				if ($fields) {
					$searchq = array();
					foreach ($fields as $field) {
						$boost = (isset($search['boost'][$field])) ? '^' . $search['boost'][$field] : '';
						$searchq[] = "{$field}:".$part.$fuzzy.$boost;
					}
					$q[] = '+('.implode(' OR ', $searchq).')';
				}
				else {
					$q[] = $part.$fuzzy;
				}
				$hlq[] = $part;
			}
		}
		return $q;
	}

}
