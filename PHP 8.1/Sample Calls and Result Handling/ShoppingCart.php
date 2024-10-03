<?php

/**
 * Example of how to submit a PayPal Shopping Cart transaction
 */

$this_script = 'https://' . $_SERVER['HTTP_HOST'] . '/index.php';

echo '
<form action="index.php" method="POST">

  <!-- PayPal Command -->
  <input type="hidden" name="cmd" value="_cart">
  <input type="hidden" name="upload" value="1">
  <input type="hidden" name="rm" value="2">

  <!-- URLs -->
  <input type="hidden" name="return" value="' . $this_script . '?action=complete">
  <input type="hidden" name="cancel_return" value="' . $this_script . '?action=cancel">
  <input type="hidden" name="notify_url" value="' . $this_script . '?action=ipn">
  <input type="hidden" name="custom" value="pay.site.com/cart">
  
  <!-- Taxes and Currency -->
  <input type="hidden" name="currency_code" value="USD">
  <input type="hidden" name="lc" value="US">
  <input type="hidden" name="tax_cart" value="300">
  <input type="hidden" name="handling_cart" value="15">
  
  <!-- Start Items -->
  <input type="hidden" name="item_name_1" value="TCL 55in LCD TV">
  <input type="hidden" name="item_number_1" value="100564">
  <input type="hidden" name="amount_1" value="300">
  <input type="hidden" name="quantity_1" value="1">

  <input type="hidden" name="item_name_2" value="Raspberry Pi 4B 4GB">
  <input type="hidden" name="item_number_2" value="200438">
  <input type="hidden" name="amount_2" value="50">
  <input type="hidden" name="quantity_2" value="1">

  <input type="hidden" name="item_name_3" value="1TB SSD Drive">
  <input type="hidden" name="item_number_3" value="3004179">
  <input type="hidden" name="amount_3" value="70">
  <input type="hidden" name="quantity_3" value="5">
  <!-- End Items -->

  <!-- Configurations -->
  <input type="hidden" name="charset" value="utf-8">
  <input type="hidden" name="no_note" value="0">
  <input type="hidden" name="no_shipping" value="0">

  <!-- Business e-mail -->
  <input type="hidden" name="business" value="donations@site.com">

  <!-- Special field -->

  <h3>Shopping Cart form data is hidden</h3>
  <input type="submit" value="Submit">

</form>
';

// echo '
// <pre>';
// print_r( $_SERVER );
// echo '
// </pre>
// ';

?>