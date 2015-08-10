$Content

<form action="{$Link}search" method="GET" >
    <input type="text" name="Search" placeholder="Search for...">
    <button type="submit">Search</button>
</form>

<% loop $FAQs %>
    <% include FAQSearchResult %>
<% end_loop %>

RESULTS
<% if $SearchResults %>
<% loop $SearchResults %>
    <div>
        <h1><a href="#">$Question</a></h1>
        <p>
            $Answer
        </p>
    </div>
<% end_loop %>
<% end_if %>
