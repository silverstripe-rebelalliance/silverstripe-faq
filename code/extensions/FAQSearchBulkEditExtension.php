<?php

class FAQSearchBulkEditExtension extends DataExtension
{

    public function updateEditForm(&$form) {
        $fields = $form->Fields();
        $table = $fields->dataFieldByName('FAQSearch');

        $bulk = new GridFieldBulkManager(null, false);

        // config for the bulk actions
        $actionConfig = array(
            'isAjax' => true,
            'icon' => 'decline',
            'isDestructive' => true
        );

        // add Bulk Archive button
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
