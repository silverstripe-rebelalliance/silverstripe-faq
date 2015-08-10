<% if not $SearchResults %>
    $Title
    $Content
<% end_if %>
    $SearchForm
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
