<?php
/**
 * Representing individual searches for the search log.
 */
class FAQSearch extends DataObject implements PermissionProvider
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

    public function getCMSFields() {
        $fields = parent::getCMSFields();

        $fields->removeByName(array('Term', 'SessionID', 'TotalResults'));

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('Term', 'Search term'),
            ReadonlyField::create('SessionID', 'Session ID'),
            ReadonlyField::create('TotalResults', 'Total results given')
        ));

        $config = $fields->dataFieldByName('Articles')->getConfig();
        $config->removeComponentsByType('GridFieldDeleteAction');
        $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
        $config->removeComponentsByType('GridFieldAddNewButton');

        $config = $fields->dataFieldByName('Results')->getConfig();
        $config->removeComponentsByType('GridFieldDeleteAction');
        $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
        $config->removeComponentsByType('GridFieldAddNewButton');

        return $fields;
    }

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

    public function providePermissions()
    {
        return array(
            'FAQ_VIEW_SEARCH_LOGS' => 'View FAQ search logs',
            'FAQ_EDIT_SEARCH_LOGS' => 'Edit FAQ search logs'
        );
    }
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

    public function getList() {
        $list = parent::getList();
        $params = $this->getRequest()->requestVar('q');

        // TODO: move this to SearchContext
        if (isset($params['Useful']) && $params['Useful']) {
            $useful = Convert::raw2sql($params['Useful']);

            $list = $list->filter('Results.Useful:ExactMatch', $useful);
        }

        if (isset($params['HasResults']) && $params['HasResults']) {
            $filter = 'TotalResults' . (($params['HasResults'] == 'results') ? ':GreaterThanOrEqual' : ':LessThan');
            $list = $list->filter($filter, 1);
        }

        return $list;
    }
    public function getSearchContext() {
        $context = parent::getSearchContext();
        $fields = $context->getFields();

        $fields->removeByName('q[Created]');
        $context->removeFilterByName('Created');
        $fields->removeByName('q[TotalResults]');
        $context->removeFilterByName('TotalResults');

        // add before filter
        $date = new DateField(sprintf('q[%s]', 'CreatedBefore'), 'Created before (inclusive)');
        $date->setRightTitle(date('Y-m-d'));
        $date->setAttribute('placeholder', 'yyyy-mm-dd');

        $dateFilter = new LessThanOrEqualFilter('CreatedBefore');
        $dateFilter->setName('Created');

        $context->addField($date);
        $context->addFilter($dateFilter);

        // add after filter
        $date = new DateField(sprintf('q[%s]', 'CreatedAfter'), 'Created after');
        $date->setRightTitle(date('Y-m-d'));
        $date->setAttribute('placeholder', 'yyyy-mm-dd');

        $dateFilter = new GreaterThanFilter('CreatedAfter');
        $dateFilter->setName('Created');

        $context->addField($date);
        $context->addFilter($dateFilter);

        // what articles were rated
        $usefulObject = singleton('FAQResults_Article')->dbObject('Useful');
        $useful = new DropdownField(sprintf('q[%s]', 'Useful'), 'Usefulness', $usefulObject->enumValues());
        $useful->setEmptyString('Any');

        $context->addField($useful);

        // check if any results were returned
        $results = new DropdownField(sprintf('q[%s]', 'HasResults'), 'Total results filter', array('results' => 'With results', 'noresults' => 'Without results'));
        $results->setEmptyString('Any');

        $context->addField($results);

        return $context;
    }
}
