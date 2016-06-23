<?php

/**
 * Adds Archiving and Deleting for bulk actions, makes it much easier to archive or delete a long list of FAQ Search
 * results
 */
class FAQSearchBulkEditExtension extends DataExtension
{

    public function updateEditForm(&$form) {
        $fields = $form->Fields();
        $table = $fields->dataFieldByName('FAQSearch');

        // create the bulk manager container
        $bulk = new GridFieldBulkManager(null, false);

        // config for the bulk actions, since they're both the same
        $actionConfig = array(
            'isAjax' => true,
            'icon' => 'decline',
            'isDestructive' => true
        );

        // add Bulk Archive and Bulk Delete buttons
        $bulk
            ->addBulkAction(
                'archive',
                _t('GRIDFIELD_BULK_MANAGER.ARCHIVE_SELECT_LABEL', 'Archive'),
                null,
                $actionConfig
            )
            ->addBulkAction(
                'delete',
                _t('GRIDFIELD_BULK_MANAGER.DELETE_SELECT_LABEL', 'Delete'),
                null,
                $actionConfig
            );

        $table->getConfig()->addComponent($bulk);
    }
}
