<select name="$SearchCategoryKey" style="width:200px">
    <% loop $SelectorCategories %>
        <option value="$ID" <% if $Selected %>selected="selected"<% end_if %>>$Name</option>
    <% end_loop %>
</select>