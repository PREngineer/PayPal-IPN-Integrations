<?php

/**
 * Example of how to submit a PayPal Donation transaction
 */

$this_script = 'https://' . $_SERVER['HTTP_HOST'] . '/index.php';

echo '
<form action="index.php" method="POST">

  <!-- PayPal Command -->
  <input type="hidden" name="cmd" value="_donations">
  <input type="hidden" name="rm" value="2">

  <!-- URLs -->
  <input type="hidden" name="return" value="' . $this_script . '?action=complete">
  <input type="hidden" name="cancel_return" value="' . $this_script . '?action=cancel">
  <input type="hidden" name="notify_url" value="' . $this_script . '?action=ipn">
  <input type="hidden" name="custom" value="pay.site.com/donations">

  <!-- Taxes and Currency -->
  <input type="hidden" name="currency_code" value="USD">
  <input type="hidden" name="lc" value="US">
  
  <!-- Item -->
  <input type="hidden" name="item_name" value="Donation">
  <input type="hidden" name="item_number" value="1">
  <input type="hidden" name="amount" value="5">

  <!-- Configurations -->
  <input type="hidden" name="charset" value="utf-8">
  <input type="hidden" name="no_note" value="1">
  <input type="hidden" name="no_shipping" value="1">

  <!-- Business e-mail -->
  <input type="hidden" name="business" value="donations@site.com">
  
  <h3>Donation form data is hidden</h3>
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