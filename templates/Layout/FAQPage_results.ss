<h1>$Title</h1>
<% include FAQSearchForm %>
<% if $SearchResults %>
    <h1>
        $SearchResultsTitle
        <div><small>$SearchSummary</small></div>
    </h1>

    <% loop $SearchResults %>
        <% include FAQSearchResult Out=$Up %>
    <% end_loop %>
    <% with SearchResults %>
        <% include Pagination %>
    <% end_with %>
<% else %>
    <h1>
        $SearchTitle
    </h1>
    $NoResultsMessage
<% end_if %>
