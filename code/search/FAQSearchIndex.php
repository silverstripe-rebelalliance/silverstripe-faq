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
		parent::init();
	}
	
	/**
	 * Overwrite extra paths function to only use the path defined on the yaml file
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
}
