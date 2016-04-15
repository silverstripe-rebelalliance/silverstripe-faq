<?php
/**
 * Representing individual searches for the search log.
 */
class FAQSearch extends DataObject
{
    private static $singular_name = 'Search';

    private static $db = array(
        'Term' => 'Varchar(255)',
        'SessionID' => 'Varchar(255)',
        'TotalResults' => 'Int'
    );

    // TODO: Summary of result sets and views associated with this search
    private static $summary_fields = array(
        'Term' => 'Term',
        'Created.Nice' => 'Date',
        'TotalResults' => 'TotalResults'
    );

    private static $has_many = array(
        'Results' => 'FAQResults',
        'Articles' => 'FAQResults_Article'
    );
}

/**
 * Admin area for search log.
 */
class FAQSearch_Admin extends ModelAdmin
{
    private static $url_segment = 'faqsearch';

    private static $managed_models = array(
        'FAQSearch'
    );

    private static $menu_title = 'Search Log';
}
