<?php
/**
 * Tests basic functionality of the FAQ search log.
 */
class FAQSearchLoggingTest extends FunctionalTest
{
    protected static $fixture_file = 'FAQSearchLoggingTest.yml';

    public function setUp()
    {
        parent::setUp();

        $this->admin = $this->objFromFixture('Member', 'admin');

        $this->loginAs($this->admin);
        $this->faqPage = $this->objFromFixture('FAQPage', 'faq');
        $this->faqPage->doPublish();
        $this->logOut();
    }

    /**
     * Session is started when the FAQ landing page is hit
     */
    public function testSessionStart()
    {
        $this->assertNull(Session::get('FAQPage'));
        $this->get(Director::makeRelative($this->faqPage->Link()));
        $this->assertTrue(Session::get('FAQPage'));
    }

    /**
     * Tracking IDs are separated correctly.
     */
    public function testTrackingIDs()
    {
        $controller = FAQPage_Controller::create();

        $ids = $controller->getTrackingIDs('5_32');
        $this->assertEquals($ids, array(
            'trackingSearchID' => 5,
            'trackingResultsID' => 32
        ));

        $ids = $controller->getTrackingIDs('5_');
        $this->assertEquals($ids, array(
            'trackingSearchID' => 5,
            'trackingResultsID' => null
        ));

        $ids = $controller->getTrackingIDs('_32');
        $this->assertEquals($ids, array(
            'trackingSearchID' => null,
            'trackingResultsID' => 32
        ));
    }

    /**
     * Search and results set logs generated for session when search is performed.
     */
    public function testSearchTracking()
    {
        Phockito::include_hamcrest();

        // Nedd to set session ID explicitly for running tests in CLI
        $sessID = uniqid();
        session_id($sessID);

        $mockResponse = array(
            'Matches' => FAQSearchIndex_PaginatedList::create(ArrayList::create(array(
                $this->objFromFixture('FAQ', 'one')
            ))),
            'Suggestion' => 'suggestion text'
        );

        $spy = Phockito::spy('FAQPage_Controller');
        Phockito::when($spy)->getSearchQuery(anything())->return(new SearchQuery());
        Phockito::when($spy)->doSearch(anything(), anything(), anything())->return(new ArrayData($mockResponse));

        $response = $spy->search();

        $searches = FAQSearch::get();
        $this->assertEquals($searches->count(), 2);
        $this->assertEquals($searches->last()->SessionID, $sessID);

        $results = FAQResults::get();
        $this->assertEquals($results->count(), 2);
        $this->assertEquals($results->last()->SearchID, $searches->last()->ID);
        $this->assertEquals($searches->last()->SessionID, $sessID);
    }

    /**
     * New article view log is created and linked to the search and results set log items with the same session.
     */
    public function testViewTracking()
    {
        // Need to set session ID explicitly for running tests in CLI
        $search = FAQSearch::get()->first();
        session_id($search->SessionID);

        $faq = $this->objFromFixture('FAQ', 'one');
        $link = Director::makeRelative($this->faqPage->Link('view') . '/' . $faq->ID);

        $request = new SS_HTTPRequest('GET', $link, array(
            't' => '1_1'
        ));
        // Need to set params in the request explicitly apparently
        $request->setRouteParams(array(
            'ID' => 1
        ));
        $controller = new FAQPage_Controller();

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 0);

        $controller->view($request);

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 1);

        $log = $articles->last();
        $this->assertEquals($log->SessionID, $search->SessionID);
        $this->assertEquals($log->SearchID, 1);
        $this->assertEquals($log->ResultSetID, 1);
    }

    /**
     * Viewing articles more than once in the session for the same search and results set does not result in
     * multiple view rows for the article.
     */
    public function testSubsequentViewTracking()
    {
        // Need to set session ID explicitly for running tests in CLI
        $search = FAQSearch::get()->first();
        session_id($search->SessionID);

        $faq = $this->objFromFixture('FAQ', 'one');
        $link = Director::makeRelative($this->faqPage->Link('view') . '/' . $faq->ID);

        $request = new SS_HTTPRequest('GET', $link, array(
            't' => '1_1'
        ));
        // Need to set params in the request explicitly apparently
        $request->setRouteParams(array(
            'ID' => 1
        ));
        $controller = new FAQPage_Controller();

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 0);

        $controller->view($request);

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 1);

        $log = $articles->last();
        $this->assertEquals($log->SessionID, $search->SessionID);
        $this->assertEquals($log->SearchID, 1);
        $this->assertEquals($log->ResultSetID, 1);

        // View the article again
        $controller->view($request);

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 1);
    }

    /**
     * Viewing the tracking URL with a different session does not result in article log being generated.
     * Prevents sharing a link with tracking GET param and having logs generated/altered.
     */
    public function testViewTrackingDifferentSession()
    {
        // View with different session ID is not tracked
        $search = FAQSearch::get()->first();
        $sessionID = 6543219;
        $this->assertTrue($search->SessionID != $sessionID);
        session_id($sessionID);

        $faq = $this->objFromFixture('FAQ', 'one');
        $link = Director::makeRelative($this->faqPage->Link('view') . '/' . $faq->ID);

        $request = new SS_HTTPRequest('GET', $link, array(
            't' => '1_1'
        ));
        // Need to set params in the request explicitly apparently
        $request->setRouteParams(array(
            'ID' => 1
        ));
        $controller = new FAQPage_Controller();

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 0);

        $controller->view($request);

        $articles = FAQResults_Article::get();
        $this->assertEquals($articles->count(), 0);
    }

    /**
     * Pagination through results creates more results set views logs.
     */
    public function testPaginationTracking()
    {
        // Same session, different page of results
        $search = FAQSearch::get()->first();
        session_id($search->SessionID);

        $mockResponse = array(
            'Matches' => FAQSearchIndex_PaginatedList::create(ArrayList::create(array(
                $this->objFromFixture('FAQ', 'one')
            ))),
            'Suggestion' => 'suggestion text'
        );

        $spy = Phockito::spy('FAQPage_Controller');
        Phockito::when($spy)->getSearchQuery(anything())->return(new SearchQuery());
        Phockito::when($spy)->doSearch(anything(), anything(), anything())->return(new ArrayData($mockResponse));
        Phockito::when($spy)->getTrackingIDs(anything())->return(array(
            'trackingSearchID' => 1,
            'trackingResultsID' => null
        ));

        $request = new SS_HTTPRequest('GET', $this->faqPage->Link(), array(
            't' => '1_',
            'start' => 20
        ));
        $spy->setRequest($request); //Does this work? Have mocked getTrackingIDs() anyway

        $results = FAQResults::get();
        $this->assertEquals($results->count(), 1);

        // Search is the action that is eventually hit for pagination
        $spy->search();

        $results = FAQResults::get();
        $this->assertEquals($results->count(), 2);
        $this->assertEquals($results->last()->SearchID, $search->ID);
        $this->assertEquals($results->last()->SessionID, $search->SessionID);
    }

    /**
     * Pagination links for a different session do not trigger logs or update another sessions logs.
     * Prevents sharing a link with tracking GET param and having logs generated/altered.
     */
    public function testPaginationTrackingDifferentSession()
    {
        // Different session, page of results for logged search
        $search = FAQSearch::get()->first();
        $sessionID = 6543219;
        $this->assertTrue($search->SessionID != $sessionID);
        session_id($sessionID);

        $mockResponse = array(
            'Matches' => FAQSearchIndex_PaginatedList::create(ArrayList::create(array(
                $this->objFromFixture('FAQ', 'one')
            ))),
            'Suggestion' => 'suggestion text'
        );

        $spy = Phockito::spy('FAQPage_Controller');
        Phockito::when($spy)->getSearchQuery(anything())->return(new SearchQuery());
        Phockito::when($spy)->doSearch(anything(), anything(), anything())->return(new ArrayData($mockResponse));
        Phockito::when($spy)->getTrackingIDs(anything())->return(array(
            'trackingSearchID' => 1,
            'trackingResultsID' => null
        ));

        $request = new SS_HTTPRequest('GET', $this->faqPage->Link(), array(
            't' => '1_',
            'start' => 20
        ));
        $spy->setRequest($request); //Does this work? Have mocked getTrackingIDs() anyway

        $searches = FAQSearch::get();
        $this->assertEquals($searches->count(), 1);

        $results = FAQResults::get();
        $this->assertEquals($results->count(), 1);

        // Search is the action that is eventually hit for pagination
        $spy->search();

        $searches = FAQSearch::get();
        $this->assertEquals($searches->count(), 1);

        $results = FAQResults::get();
        $this->assertEquals($results->count(), 1);
    }

    /**
     * Log current member out by clearing session
     */
    private function logOut()
    {
        $this->session()->clear('loggedInAs');
    }
}
