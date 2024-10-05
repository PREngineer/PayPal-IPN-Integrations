<?php

/**
 *  Paypal Instant Payment Notification Integration - Sample Use File
 *  
 *  -------------------------------------------------------------------------------------------------
 *  This file demonstrates the usage of PayPal.class.php.
 * 
 *  This class was designed to aid in the interfacing between your website, paypal, and the instant
 *  payment notification (IPN) interface.
 * 
 *  This single file serves as 4 virtual pages depending on the "action" variable passed in the URL:
 *  -------------------------------------------------------------------------------------------------
 *  1. The submission page                         - Processes transaction data and submits it to PayPal.
 *  2. The complete page                           - The page PayPal returns a user to upon success.
 *  3. The cancel page                             - The page PayPal returns a user to upon canceling an order.
 *  4. The ipn (Instant Payment Notification) page - The page that handles the IPN validation with Paypal.
 */


/**
 *  Include the class file, if not already done by auto-loader script
 *  -------------------------------------------------------------------------------------------------
 */
require_once('PayPal.class.php');

/**
 * Create an instance of the PayPal class
 */
$pp = new PayPal( getenv('PAYPAL_INSTANCE'), getenv('PAYPAL_LOG_MODE'), getenv('PAYPAL_LOG_LEVEL') );

/**
 *  ------------------------------------------------------------------------------------------------- 
 *  We need to define an action variable to state what we are doing (we have 4 options).
 * 
 *  These are:
 *  
 *  1. process  - Start processing the order.
 *  2. success  - Transferred here when the order is successful.
 *  3. cancel   - Transferred here when the order is cancelled.
 *  4. ipn      - Confirms the order.
 *  ------------------------------------------------------------------------------------------------- 
 *  If not provided, default to 'process'
 */

if ( empty( $_GET['action'] ) )
  $_GET['action'] = 'process';

/**
 *  ------------------------------------------------------------------------------------------------- 
 *  Take action, depending on the variable received
 *  ------------------------------------------------------------------------------------------------- 
 */

switch ( $_GET['action'] )
{
   /**
    *  ------------------------------------------------------------------------------------------------- 
    *  Case 1: Process an order
    *  ------------------------------------------------------------------------------------------------- 
    *  Output: 
    *
    *  This action outputs nothing.
    *  
    *  What Happens Here: 
    *
    *  The data received from the POST of your form will be consumed.  The function named 
    *  'Submit_Transaction()' will output all of the HTML tags that will create an invisible form that 
    *  is then submitted to PayPal using the 'BODY onload' attribute.
    *
    *  Considerations: 
    *  
    *  Do not output anything to the page using print or echo when calling the function
    *  'Submit_Transaction()'
    *
    *  Suggestions:
    *
    *  This is where you would validate your form and make sure that all the information was properly
    *  transmitted.  Then take your $_POST variables and load them into the class like shown below, using
    *  only the $_POST values and not any constants or string expressions.
    *
    *  Example:
    *
    *  $pp->Add_Field( 'first_name', $_POST['first_name'] );
    *  $pp->Add_Field( 'last_name',  $_POST['last_name'] );
    */   
   case 'process':

    // Add all fields to PayPal transaction
    foreach ( $_POST as $key => $value ) {
      $pp->Add_Field( $key, $value );
    }
    
    // Submit to data to PayPal
    $pp->Submit_Transaction();
    
    break;
   
   /**
    *  ------------------------------------------------------------------------------------------------- 
    *  Case 2: Order was successfully completed
    *  ------------------------------------------------------------------------------------------------- 
    *  Output: 
    *
    *  This action outputs nothing but stores the information about the successful transaction in the 
    *  $_POST variables.
    *  
    *  What Happens Here: 
    *
    *  PayPal sends all the posted data back to you after the transaction has completed.
    *
    *  Suggestions:
    *
    *  This is where you should thank the user for their payment transaction and give them some sort of
    *  confirmation that it was in theory successful but just needs to be validated.  At this point, don't
    *  assume that it was a complete success until you get validation from the IPN (Instant Payment 
    *  Notification) system.
    *
    *  You could also simply redirect the user to another page, or your own order status page which
    *  presents the user with the status of their order based on a database (which can be updated later)
    *  with the IPN information.
    *  
    */
   case 'complete':
      
    $pp->Display_Completed( $_GET );
      
    break;
   
   /**
    *  ------------------------------------------------------------------------------------------------- 
    *  Case 3: Order was cancelled
    *  ------------------------------------------------------------------------------------------------- 
    *  Output: 
    *
    *  This action outputs nothing.
    *  
    *  What Happens Here:
    *
    *  The transaction was cancelled by the user and was redirected here.
    *
    *  Suggestions:
    *
    *  This is where you should let the user know that the transaction did not go through as it was.
    *  cancelled by them.
    *  
    */
   case 'cancel':

    $pp->Display_Cancelled();
    
    break;
   
   /**
    *  ------------------------------------------------------------------------------------------------- 
    *  Case 4: PayPal is reaching out to validate the transaction
    *  ------------------------------------------------------------------------------------------------- 
    *  Output: 
    *
    *  This action outputs nothing.
    *  
    *  What Happens Here:
    *
    *  PayPal is letting you know that the transaction was completed and what the result was.
    *
    *  Considerations: 
    *  
    *  Do not try to output anything to the page.  This should behave like a script running in the 'backend.'
    *  Calling this will log all the IPN data to a text file by default.
    *
    *  You can access a slew of information via the ipn_data() array.
    *  Check the paypal documentation for specifics on what information is available in the IPN $_POST
    *  variables.  Basically, all the $_POST variables which PayPal sends, we send back for validation,
    *  and now are stored in the ipn_data() array.
    *
    *  Suggestions:
    *
    *  This is where you validate the IPN data and, if it's valid, you update your database to reflect
    *  the payment of the user.  
    *  
    */
   case 'ipn':
      
    // Validated
    if ( $pp->Validate_IPN() ) {
      // Leave transaction as completed
    }
    // Failed to validate
    else{
      // Make sure to return that the transaction failed
      $_POST[ 'payment_status' ] = "Failed";
    }

      // Post the data back to processing script, using curl.
      $ch = curl_init( getenv('PROCESSING_URL') );
      // Use HTTP version 1.1
      curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
      // This is a POST
      curl_setopt( $ch, CURLOPT_POST, 1 );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
      // Contents of the POST
      curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST );
      // SSL version
      curl_setopt( $ch, CURLOPT_SSLVERSION, 6 );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 1 );
      curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
      curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1 );
      curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 30 );
      // Define the agent in the headers
      curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                      'User-Agent: PayPal-IPN-Integration',
                      'Connection: Close',
                  )
      );

      // Execute curl request
      curl_exec( $ch );

    break;
}

?>