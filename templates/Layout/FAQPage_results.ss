$FAQSearchForm
<% if $SearchResults %>
    <% loop $SearchResults %>
        <% include FAQSearchResult %>
    <% end_loop %>
<% end_if %>
