<% if not $SearchResults %>
    $Title
    $Content
<% end_if %>
    $SearchForm
<% if $SearchResults %>
    <% loop $SearchResults %>
        <% include FAQSearchResult %>
    <% end_loop %>
<% end_if %>

