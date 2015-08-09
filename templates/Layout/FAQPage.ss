$Content

<form method="GET" >
    <input type="text" name="search" placeholder="Search for...">
    <button type="submit">Search</button>
</form>

<% loop $FAQs %>
    <% include FAQSearchResult %>
<% end_loop %>
