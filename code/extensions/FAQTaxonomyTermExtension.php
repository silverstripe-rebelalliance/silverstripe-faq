<?php
/**
 * Extends {@link TaxonomyTerm} with useful functionality
 */
class FAQTaxonomyTermExtension extends DataExtension {
	
	/**
	 *
	 */
	public static function getByName($name) {
		$taxonomy = TaxonomyTerm::get()->filter('Name', $name)->first();
		if($taxonomy && $taxonomy->exists()) {
			return $taxonomy;
		}
		
		return null;
	}
	
	/**
	 *
	 */
	public static function getOrCreate($find, $create) {
		$taxonomy = TaxonomyTerm::get()->filter($find)->first();
		if(!$taxonomy || !$taxonomy->exists()) {
			$taxonomy = new TaxonomyTerm($create);
			$taxonomy->write();
		}
		
		return $taxonomy;
	}
}