<?php

/**
 * Common fields and functionality for FAQ result sets and article views.
 */
class FAQResults_Extension extends Extension
{
    private static $singular_name = 'View';

    private static $db = array(
        'Useful' => "Enum('Y,N,U','U')", // Yes, No, Unset
        'Comment' => 'Varchar(255)'
    );

    private static $has_one = array(
        'Search' => 'FAQSearch'
    );

    public function canView($member = false)
    {
        return Permission::check('FAQ_VIEW_SEARCH_LOGS');
    }

    public function canEdit($member = false)
    {
        return Permission::check('FAQ_EDIT_SEARCH_LOGS');
    }

    public function canDelete($member = false)
    {
        return false;
    }

    public function canCreate($member = false)
    {
        return false;
    }
}

/**
 * Represents a result set resulting from a search.
 */
class FAQResults extends DataObject
{
    private static $singular_name = 'Result Set';

    private static $db = array(
        'ArticleSet' => 'Varchar(255)',
        'SetSize' => 'Int'
    );

    private static $has_many = array(
        'ArticlesViewed' => 'FAQResults_Article'
    );

    private static $summary_fields = array(
        'Created.Nice' => 'Date',
        'ArticleSet' => 'Articles',
        'SetSize' => 'Total'
    );

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('ArticleSet', 'SearchID', 'SetSize', 'Useful', 'Comment'));

        $articleIDs = json_decode($this->ArticleSet);
        // get FAQs listed, the 'FIELD(ID,{IDs})' ensures they appear in the order provided
        $articles = DataObject::get('FAQ', 'ID IN (' . implode(',', $articleIDs) . ')', 'FIELD(ID,' . implode(',', $articleIDs) .')');
        $articleSet = GridField::create('FAQ', 'Article Set', $articles);

        $config = $articleSet->getConfig();
        // Edit Button doesn't work due to being no relationship
        //$config->addComponent(new GridFieldEditButton());

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('SetSize', 'Size of this results set'),
            $articleSet
        ));

        $config = $fields->dataFieldByName('ArticlesViewed')->getConfig();
        $config->removeComponentsByType('GridFieldDeleteAction');
        $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
        $config->removeComponentsByType('GridFieldAddNewButton');

        return $fields;
    }
}

/**
 * Represents views of individual articles from a search result set.
 */
class FAQResults_Article extends DataObject
{
    private static $singular_name = 'Article';

    private static $has_one = array(
        'FAQ' => 'FAQ',
        'ResultSet' => 'FAQResults'
    );

    private static $summary_fields = array(
        'Created.Nice' => 'Date',
        'FAQ.ID' => 'Article ID',
        'FAQ.Question' => 'Article',
        'Useful' => 'Useful',
        'Comment' => 'Comment'
    );
}


