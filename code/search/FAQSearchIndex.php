<?php

class FAQSearchIndex extends SolrIndex {
	public function init() {
		$this->addClass('FAQ');
		$this->addAllFulltextFields();
		$this->addFilterField('ShowInSearch');
	}
}