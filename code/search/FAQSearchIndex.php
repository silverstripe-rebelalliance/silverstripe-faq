<?php

class FAQSearchIndex extends SolrIndex {

	// don't build the default search index
	//private static $hide_ancestor = "SolrSearchIndex";

	public function init() {
		$this->addClass('SiteTree');
		$this->addAllFulltextFields();
		$this->addFilterField('ShowInSearch');
	}
}