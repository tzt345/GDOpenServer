<?php
$topArtistsRedirectMainGD = 0; // Indicates if the response from the main GD servers are redirected, 0 for no and 1 for yes.

/* If it does not redirect, based on how many songs an artists has on the songs index of the server, artists get higher on the top song makers.
Note: This excludes "Reupload" account.
*/

$songsRedirectMainGD = 1; // Indicates if the songs fetched from this GDPS will be asked from main GD servers or asked from newgrounds directly; 0 for no and 1 for yes.

$timestampType = 0; // Indicates the format for timestamps shown in-game and in bot responses; 0 = Show the past time ("x seconds/minutes/... ago"), 1 = Show the exact date ("dd/mm/yyyy").
?>