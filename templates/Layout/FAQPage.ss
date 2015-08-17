<h1>$Title</h1>
$Content
<% include FAQSearchForm %>
<div>
    <% loop FeaturedFAQs.sort(SortOrder) %>
        <% include FAQSearchResult %>
    <% end_loop %>
</div>
