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

}