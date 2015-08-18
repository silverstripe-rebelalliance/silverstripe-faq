<select style="width:200px">
    <% loop $Categories %>
        <option value="$ID">$Name</option>
    <% end_loop %>
</select>