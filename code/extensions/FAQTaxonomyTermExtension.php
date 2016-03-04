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
	
	
	/**
	 * 
	 */
	public function getChildDeep(array $filter) {
		// check if this matches filter
		$match = true;
		foreach($filter as $key => $value) {
			if (isset($this->owner->$key) && $this->owner->$key != $value) {
				$match = false;
			}
		}

		if ($match) {
			return $this->owner;
		}
		
		// if not, loop over children and run this method
		foreach($this->owner->Children() as $child) {
			$response = $child->getChildDeep($filter);
			if ($response != null) {
				return $response;
			}
		}
				
		return null;
	}
}