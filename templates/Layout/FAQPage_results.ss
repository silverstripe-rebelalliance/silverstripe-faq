$FAQSearchForm
<% if $SearchResults %>
    <% loop $SearchResults %>
        <% include FAQSearchResult %>
    <% end_loop %>
<% end_if %>
<% with SearchResults %>
    <% include Pagination %>
<% end_with %>
