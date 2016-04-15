<?php
/**
 * Tests basic functionality of the FAQ search log.
 */
class FAQSearchLogTest extends FunctionalTest
{
    protected static $fixture_file = 'FAQSearchLogTest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->admin = $this->objFromFixture('Member', 'admin');
        $this->author = $this->objFromFixture('Member', 'contentAuthor');
        $this->noperms = $this->objFromFixture('Member', 'noPerms');
        $this->log = $this->objFromFixture('FAQSearch', 'one');
        $this->logResults = $this->objFromFixture('FAQResults', 'one');
        $this->logArticle = $this->objFromFixture('FAQResults_Article', 'one');
    }

    /**
     * Logs can be viewed by logged in members with correct permissions only.
     */
    public function testLogViewing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->log->canView());
        $this->assertTrue($this->logResults->canView());
        $this->assertTrue($this->logArticle->canView());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canView());
        $this->assertFalse($this->logResults->canView());
        $this->assertFalse($this->logArticle->canView());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->log->canView());
        $this->assertTrue($this->logResults->canView());
        $this->assertTrue($this->logArticle->canView());
        $this->logOut();

        $this->assertFalse($this->log->canView());
        $this->assertFalse($this->logResults->canView());
        $this->assertFalse($this->logArticle->canView());
    }

    /**
     * Logs can be edited by logged in members with correct permissions only.
     */
    public function testLogEditing()
    {
        $this->loginAs($this->admin);
        $this->assertTrue($this->log->canEdit());
        $this->assertTrue($this->logResults->canEdit());
        $this->assertTrue($this->logArticle->canEdit());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canEdit());
        $this->assertFalse($this->logResults->canEdit());
        $this->assertFalse($this->logArticle->canEdit());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertTrue($this->log->canEdit());
        $this->assertTrue($this->logResults->canEdit());
        $this->assertTrue($this->logArticle->canEdit());
        $this->logOut();

        $this->assertFalse($this->log->canEdit());
        $this->assertFalse($this->logResults->canEdit());
        $this->assertFalse($this->logArticle->canEdit());
    }

    /**
     * Logs cannot be deleted manually.
     */
    public function testLogDeleting()
    {
        $this->loginAs($this->admin);
        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
        $this->logOut();

        $this->assertFalse($this->log->canDelete());
        $this->assertFalse($this->logResults->canDelete());
        $this->assertFalse($this->logArticle->canDelete());
    }

    /**
     * Logs cannot be created manually.
     */
    public function testLogCreating()
    {
        $this->loginAs($this->admin);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->loginAs($this->noperms);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->loginAs($this->author);
        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
        $this->logOut();

        $this->assertFalse($this->log->canCreate());
        $this->assertFalse($this->logResults->canCreate());
        $this->assertFalse($this->logArticle->canCreate());
    }


    /**
     * Log current member out by clearing session
     */
    private function logOut()
    {
        $this->session()->clear('loggedInAs');
    }
}
