<?php

echo '<h3>Data Received:</h3><br>';

echo '<pre>';
print_r( $_POST );
echo '</pre>';

// Write to file to validate
file_put_contents( "Processed-Data.txt", print_r( $_POST, true ) );

?>