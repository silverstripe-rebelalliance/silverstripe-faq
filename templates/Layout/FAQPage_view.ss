<h1>$FAQ.Question</h1>

<p>$FAQ.Answer.RichLinks</p>

<% if $ShowRatingSuccessMessage %> THANKS YO <% end_if %>
<% if $FAQHasSubmittedRating %>
    SUBMITTED
<% else %>
    <h3>Rate this answer:</h3>
    <form method="POST">
        <button type="submit" name="$SearchRatingPostKey" value="1">1</button>
        <button type="submit" name="$SearchRatingPostKey" value="2">2</button>
        <button type="submit" name="$SearchRatingPostKey" value="3">3</button>
        <button type="submit" name="$SearchRatingPostKey" value="4">4</button>
        <button type="submit" name="$SearchRatingPostKey" value="5">5</button>
    </form>
<% end_if %>