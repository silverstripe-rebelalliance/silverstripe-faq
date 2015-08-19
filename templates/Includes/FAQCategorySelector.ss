<select name="$SearchCategoryKey" style="width:200px">
    <% loop $SelectorCategories %>
        <option value="$ID">$Name</option>
    <% end_loop %>
</select>