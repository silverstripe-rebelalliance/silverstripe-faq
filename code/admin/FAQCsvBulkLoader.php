<?php
/**
 * Extends Csv loader to handle Categories (Taxonomy DataObject) better.
 * 
 */
class FAQCsvBulkLoader extends CsvBulkLoader {
   
	public $columnMap = array(
		'Question' => 'Question', 
		'Answer' => 'Answer', 
		'Keywords' => 'Keywords', 
		'Category' => '->getCategoryByName'
	);
   
	public $duplicateChecks = array(
		'Question' => 'Question'
	);
   
	/**
	 * We don't want to create categories we don't find on the root taxonomy
	 */
	public static function getCategoryByName(&$obj, $val, $record) {
		$val = trim($val);
		
		$root = FAQ::getRootCategory();
		if(!$root || !$root->exists()) {
			return null;
		}

		$category = $root->getChildDeep(array('Name' => $val));
		
		if($category && $category->exists()) {
			$obj->CategoryID = $category->ID;
			$obj->write();
		}
	}
}
?>