<?php

/**
 * Model Admin for FAQs search module.
 * Allows a content author to publish and edit questions and answers.
 *
 * @see FAQ for FAQ DataObject.
 */
class FAQAdmin extends ModelAdmin
{

    private static $url_segment = 'faq';

    private static $managed_models = array(
        'FAQ'
    );

    private static $menu_title = 'FAQs';

    private static $model_importers = array(
        'FAQ' => 'FAQCsvBulkLoader'
    );

    /**
     * Overload ModelAdmin->getExportFields() so that we can export keywords.
     *
     * @see ModelAdmin::getExportFields
     */
    public function getExportFields()
    {
        $fields = array(
            'Question' => 'Question',
            'Answer' => 'Answer',
            'Keywords' => 'Keywords',
            'Category' => function ($category) {
                return $category->Name;
            }
        );

        $this->extend('updateFAQExportFields', $fields);

        return $fields;
    }
}
