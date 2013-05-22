<script>
  $(function() {
    $( "#tabs" ).tabs();
  });
  </script>



<div class="demo">

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Benutzername / Passwort</a></li>
    <li><a href="#tabs-2">RFID</a></li>
  </ul>
  <div id="tabs-1">

<table align="center">
<tr>
<td colspan="2" height="20"></td>
</tr>

<tr>
<td>Account:</td>
<td><select>
  <option>Terminkalender</option>
  <option>Postbank (Interner Browser)</option>
</select></td>
</tr>


<tr>
<td>Benutzer:</td>
<td><input name="username" type="text" size="30"></td>
</tr>
<tr>
<td>Passwort:</td>
<td><input name="password" type="password" size="30"></td>
</tr>

<tr>
<td></td>
<td><input name="buton" type="button" value="anmelden"></td>
</tr>


</table>

  </div>
  <div id="tabs-2">
    <p>Morbi tincidunt, dui sit amet facilisis feugiat, odio metus gravida ante, ut pharetra massa metus id nunc. Duis scelerisque molestie turpis. Sed fringilla, massa eget luctus malesuada, metus eros molestie lectus, ut tempus eros massa ut dolor. Aenean aliquet fringilla sem. Suspendisse sed ligula in ligula suscipit aliquam. Praesent in eros vestibulum mi adipiscing adipiscing. Morbi facilisis. Curabitur ornare consequat nunc. Aenean vel metus. Ut posuere viverra nulla. Aliquam erat volutpat. Pellentesque convallis. Maecenas feugiat, tellus pellentesque pretium posuere, felis lorem euismod felis, eu ornare leo nisi vel felis. Mauris consectetur tortor et purus.</p>
  </div>
</div>

</div><!-- End demo -->



<div style="display: none;" class="demo-description">
<p>Click tabs to swap between content that is broken into logical sections.</p>
</div><!-- End demo-description -->
