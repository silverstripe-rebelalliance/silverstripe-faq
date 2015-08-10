<?php
/**
 * The saddest Solr index in history
 */
class FAQSearchIndex extends SolrIndex {
	public function init() {
		$this->addClass('FAQ');
		$this->addAllFulltextFields();
	}
}