<?php

/*******************************************************************************
 *                  Paypal Instant Payment Notification Integration
 *******************************************************************************
 *      Author:               Jorge Pabón
 *      Email:                pianistapr@hotmail.com
 *      GitHub:               http://www.github.com/PREngineer
 *      
 *******************************************************************************
 *  DESCRIPTION:
 *
 *      This file provides a simple way to interface with PayPal's Instant
 *      Payment Notification (IPN) interface.
 *
 *      This class can perform the following actions:
 *      1. Submission of an order to PayPal
 *      2. Validation of the order (processing an Instant Payment Notification)
 *  
 *      To submit an order to PayPal, have your order form POST to a file that has:
 *      ---------------------------------------------------------------------------
 *
 *          $PayPal = new PayPal( [ production | sandbox ], [ no | console | file | both ], [ low | medium | high | debug ] );
 *                                  ^ PayPal endpoint         ^ log mode                      ^ log level
 * 
 *          $PayPal->Add_Field( 'business',   'somebody@domain.com' );
 *          $PayPal->Add_Field( 'cmd', '_cart' );
 *            ... (add all other required fields)
 * 
 *          $PayPal->Submit_Transaction();
 *
 *      To process an IPN, have your IPN processing file contain:
 *      ---------------------------------------------------------
 *
 *          $PayPal = new PayPal( [ production | sandbox ], [ no | console | file | both ], [ low | medium | high | debug ] );
 *                                  ^ PayPal endpoint         ^ log mode                      ^ log level
 * 
 *          if( $PayPal->Validate_IPN() ) {
 *            ... ( IPN is verified.  Details are in the ipn_data() array )
 *          }
 *          else{
 *            ... validation failed in this case
 *          }
 *
 *******************************************************************************
 *  RESOURCES:
 * 
 *      In case you are new to PayPal, here is some information to help you:
 *
 *      1. Download and read the Merchant User Manual and Integration Guide from
 *         https://developer.paypal.com/api/nvp-soap/ipn/IPNIntro/.  This gives 
 *         you all the information you need including the fields you can pass to
 *         PayPal (using Add_Field() with this class) as well as all the fields
 *         that are returned in an IPN post (stored in the ipn_data() array in
 *         this class).  It also diagrams the entire transaction process.
 *
 *      2. Create a "sandbox" account for a buyer and a seller.  These are just
 *         test accounts that allow you to test your site from both the 
 *         seller and buyer perspective.  The instructions for this are available
 *         at https://developer.paypal.com/ as well as a great forum where you
 *         can ask all your PayPal integration questions.  Make sure you follow
 *         all the directions in setting up a sandbox test environment, including
 *         the addition of wallet funds, fake bank accounts, and/or credit cards.
 * 
 *******************************************************************************
*/

class PayPal {
   
  /************************************************** Attributes **************************************************/

  // Logging configurations
  private $log_mode;
  private $log_level;
  private $log_file;
  
  // Holds the IPN response from PayPal
  private $ipn_response;
  // Contains the IPN POST values
  private $ipn_data = array();
  // Holds the fields to submit to PayPal
  private $fields = array();
  // The PayPal endpoint to use
  private $paypal_url;
  
  /**************************************************** Methods ****************************************************/

  /** 
  * __construct - Constructor that is called when class is created.
  * @param string   $instance  - The PayPal endpoint to use
  *                              [ production | sandbox ]
  * @param boolean  $log_mode  - Whether to log and where
  *                              [ no - No logging (Discouraged) | console - Log to Console (Recommended) | file - Log to File | both - Log to Both ]
  * @param string   $log_level - How much data to log - (Errors are always logged)
  *                              [ low - IPN validations | medium - Adds submissions | high - Adds Tracing | debug - Adds input dumps ]
  */
  public function __construct( $instance, $log_mode, $log_level ) {
    
    // Start with no response
    $this->ipn_response = '';
    
    // Determine the proper PayPal Website to use
    if( $instance == "production" )
      $this->paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
    else
      $this->paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    // Set the logging options
    $this->log_mode  = $log_mode;
    $this->log_level = $log_level;
    $this->log_file  = 'PayPal-IPN-Integration-Logs.txt';
    
    // Populate the $fields array with a default value.
    // See the PayPal documentation for a list of fields and their data types.
    // This default value can be overwritten by the calling script.

    // Return method = POST
    $this->Add_Field( 'rm','2' );
      
  }
   
  /**
  * Add_Field - Adds a key=>value pair to the fields array. This array contains all the
  *             information that will be sent to PayPal in the POST.  Existing values
  *             are overwritten.
  * @param string field - The name of the field in the array.
  * @param string value - The value of the field in the array.
  */
  public function Add_Field( $field, $value ) {

    $this->fields[ "$field" ] = $value;

  }

  /**
   * Display_Cancelled - Displays a confirmation page when the checkout was completed.
   * @param array $data - The completed checkout data from PayPal.
   */
  public function Display_Cancelled() {

    // Tracing
    if ( $this->log_mode != 'no' && $this->log_level != 'low' )
      $this->Log_Entry( 'INFO', "The transaction was cancelled by the user." );

    echo '
      <center>
        <img src="PayPal_Cancelled.gif" width="600" height="360"/>
        <h3>The transaction was canceled.</h3>
      </center>
    
      <script>
        setTimeout(function() {
            window.location.href = "' . getenv('CART_URL') . '";
        }, ' . getenv('PAYPAL_LOAD_TIME') . '000);
      </script>
      ';

  }
  
  /**
   * Display_Completed - Displays a confirmation page when the checkout was completed.
   * @param array $data - The completed checkout data from PayPal.
   */
  public function Display_Completed( $data ) {

    // Tracing
    if ( $this->log_mode != 'no' && $this->log_level != 'low' )
      $this->Log_Entry( 'INFO', "The PayPal checkout has been completed." );

    echo '
    <center>
      <img src="PayPal_Complete.gif" width="600" height="350"/>
      <h3>Checkout complete!</h3>
    </center>';

    if( $this->log_level == 'debug' ) {
      $this->Log_Entry( 'DEBUG', "Dumping fields." );

      echo '
        <h3>Data Received:</h3>
          <table width="95%" border="1" cellpadding="2" cellspacing="0">
          <tr>
              <td bgcolor="lightgray"><b><font color="black">Field Name</font></b></td>
              <td bgcolor="lightgray"><b><font color="black">Value</font></b></td>
          </tr>';
      
      ksort( $data );
      foreach ( $data as $key => $value ) {
          echo "
          <tr>
            <td>$key</td>
            <td>" . urldecode( $value ) . '</td>
          </tr>';
      }

      echo '</table>';
    }

    echo '
      <script>
        setTimeout(function() {
            window.location.href = "' . getenv('ORDERS_URL') . '";
        }, ' . getenv('PAYPAL_LOAD_TIME') . '000);
      </script>';

  }

  /**
  * Dump_Fields - Helper function to debug issues.  This function will output all the field and value
  *               pairs that are currently defined in the instance of the class using the Add_Field() function.
  */
  public function Dump_Fields() {

    // Tracing
    if ( $this->log_mode != 'no' && $this->log_level == 'debug' )
      $this->Log_Entry( 'DEBUG', "Dumping Fields." );

    echo '
      <h3>Posted data:</h3>
        <table width="95%" border="1" cellpadding="2" cellspacing="0">
        <tr>
            <td bgcolor="lightgray"><b><font color="black">Field Name</font></b></td>
            <td bgcolor="lightgray"><b><font color="black">Value</font></b></td>
        </tr>';
    
    ksort( $this->fields );
    foreach ( $this->fields as $key => $value ) {
        echo "
        <tr>
          <td>$key</td>
          <td>" . urldecode( $value ) . '</td>
        </tr>';
    }

    echo '</table>';

  }

  /**
   * Log_Entry - Logs an entry to the appropriate place, based on class properties
   * @param string $type    - The type of log entry
   *                          [ INFO, ERROR, WARNING, TRACE ]
   * @param string $message - The message to log
   */
  private function Log_Entry( $type, $message ) {
    
    // Prepare the text to log. Format: [UserIP] - - [Timestamp] - [TYPE] - [Message]
    $text = "[" . $_SERVER[ 'REMOTE_ADDR' ] . "] - - [" . date('m/d/Y g:i A') . "] - [$type] - " . "$message\n";
    
    // Always log errors
    if( $type == "ERROR" ){
      file_put_contents( 'php://stderr', $text );
    }
    // Log other types only if the logging mode calls for it
    else if( $this->log_mode == "console" || $this->log_mode == "both" ){
      file_put_contents( 'php://stdout', $text );
    }

    // Log to file, appending
    if( $this->log_mode == "file" || $this->log_mode == "both" ){
      $this->Log_To_File( $text );
    }

  }
  
  /**
  * Log_IPN_Results - Write the results of the transaction
  * @param boolean $success - The result of the transaction validation [ true | false ]
  */
  private function Log_IPN_Results( $success ) {
    
    // If logging is turned off, do nothing
    if ( $this->log_mode == 'no' )
      return;
        
    // Prepare the log entry
    $text = ""; 
    
    // Success or failure being logged?
    if ( $success )
      $text .= 'TRANSACTION COMPLETED' . " - ";
    else
      $text .= 'TRANSACTION FAILED - IPN Validation Failed - ';

    // Log the response from the PayPal server
    $text .= "[Paypal IPN Response] - " . $this->ipn_response . " - ";
    
    // Add the POST variables
    $text .= "[Transaction Data] - ";
    ksort( $this->ipn_data );
    foreach ( $this->ipn_data as $key => $value ) {
      $text .= "$key=$value, ";
    }
    // Remove the ', ' after last one
    $text = rtrim( $text, ', ' );
    
    $this->Log_Entry( "INFO", $text );

  }

  /**
   * Log_Submitted_Transaction - Write the details about the transaction submitted to PayPal
   * @param array $data - The data that was posted to initiate the transaction
   */
  private function Log_Submitted_Transaction( $data ) {
    
    // If logging is turned off, do nothing
    if ( $this->log_mode == 'no' )
      return;

    $text = "TRANSACTION SUBMITTED - [Data] - ";
    foreach ( $data as $key => $value ) {
      $text .= "$key=$value, ";
    }
    // Remove the ', ' after last one
    $text = rtrim( $text, ', ' );

    $this->Log_Entry( "INFO", $text );

  }

  /**
   * Log_To_File - Appends text to the log file
   * @param string $text - The text to write
   */
  private function Log_To_File( $text ) {

    $file = fopen( $this->log_file, 'a' );
    fwrite( $file, $text );
    fclose( $file );

  }

  /**
  * Submit_Transaction - This function generates an entire HTML page with a hidden form.
  *                      It contains all the elements that are submitted to PayPal using the BODY's 'onLoad' attribute.
  *                      The user will briefly see an animation with a message that reads: "Loading Paypal..."
  *                      The user is then redirected to PayPal for checkout.
  */
  public function Submit_Transaction() {

    // Tracing
    if ( $this->log_mode != 'no' && ( $this->log_level == 'high' || $this->log_level == 'debug' ) )
      $this->Log_Entry( 'INFO', "Submitting transaction to PayPal" );

    echo '
    <html>
    <head>
      <title>Loading PayPal...</title>
    </head>
    
    <body onload="setTimeout(function() { document.form.submit(); }, ' . getenv('PAYPAL_LOAD_TIME') . '000);">
      <center>
        <img src="PayPal_Start.gif" width="600" height="350">
        <h3>Loading PayPal...</h3>
      </center>
      <form method="post" name="form" action="' . $this->paypal_url . '">';

    foreach ( $this->fields as $name => $value ) {
        echo '
        <input type="hidden" name="' . $name . '" value="' . $value .'">';
    }

    echo '
      </form>
    ';

    // For debugging, outputs a table of all the fields
    if( $this->log_level == 'debug' )
      $this->Dump_Fields();

    if( $this->log_level != "low" )  
      $this->Log_Submitted_Transaction( $this->fields );
    
    echo '    
    </body>
    </html>';    
  }
   
  /**
  * Validate_IPN - This function validates the transaction with PayPal.
  */
  public function Validate_IPN() {

    // If POST was empty
    if ( ! count( $_POST ) ) {
      $this->Log_Entry( "ERROR", "No data received in IPN POST." );
      throw new Exception( "Missing IPN POST Data" );
    }

    // Tracing
    if ( $this->log_mode != 'no' && ( $this->log_level == 'high' || $this->log_level == 'debug' ) )
      $this->Log_Entry( 'TRACE', "Processing PayPal IPN POST data." );

    // Capture the data sent by PayPal
    $raw_post_data = file_get_contents( 'php://input' );
    // Break it into an array
    $raw_post_array = explode( '&', $raw_post_data );

    // Build the post to send to PayPal as confirmation
    $post_back = array();
    foreach ( $raw_post_array as $keyval ) {
      $keyval = explode( '=', $keyval );
      // Make sure to only act on items that represent a key value pair
      if ( count( $keyval ) == 2 ) {
        // Check the payment date
        if ( $keyval[0] === 'payment_date' ) {
          // If it contains a '+', encode it safely
          if ( substr_count( $keyval[1], '+' ) === 1 ) {
            $keyval[1] = str_replace( '+', '%2B', $keyval[1] );
          }
        }
        // Store in the post back array
        $post_back[ $keyval[0] ] = urldecode( $keyval[1] );
        // Store in the ipn_data array
        $this->ipn_data[ $keyval[0] ] = urldecode( $keyval[1] );
      }
    }

    // Tracing
    if ( $this->log_mode != 'no' && ( $this->log_level == 'high' || $this->log_level == 'debug' ) )
      $this->Log_Entry( 'TRACE', "Building IPN Verification POST." );

    // Build the body of the verification POST request.  Needs to have the '_notify-validate' command.
    $req = 'cmd=_notify-validate';
    foreach ( $post_back as $key => $value ) {
      // Safely encode the value
      $value = urlencode( $value );
      $req .= "&$key=$value";
    }

    // Tracing
    if ( $this->log_mode != 'no' && ( $this->log_level == 'high' || $this->log_level == 'debug' ) )
      $this->Log_Entry( 'TRACE', "Submitting the IPN Verification POST." );

    // Post the data back to PayPal, using curl.
    $ch = curl_init( $this->paypal_url );
    // Use HTTP version 1.1
    curl_setopt( $ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
    // This is a POST
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    // Contents of the POST
    curl_setopt( $ch, CURLOPT_POSTFIELDS, $req );
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
    $res = curl_exec( $ch );
    // Store the response
    $this->ipn_response = $res;

    // Tracing
    if ( $this->log_mode != 'no' && ( $this->log_level == 'high' || $this->log_level == 'debug' ) )
      $this->Log_Entry( 'TRACE', "Processing the validation response." );

    // Check the result of the request
    if ( !$res ) {
      $errno  = curl_errno( $ch );
      $errstr = curl_error( $ch );
      curl_close( $ch );
      
      $this->Log_Entry( "ERROR", "An error occurred while submitting the IPN Validation POST." );

      throw new Exception( "cURL error: [$errno] $errstr" );
    }

    // Get the information from the request
    $info = curl_getinfo( $ch );

    // If the status code is not 200 OK
    $http_code = $info['http_code'];
    if ( $http_code != 200 ) {
      $this->Log_Entry( "ERROR", "PayPal replied with a " . $http_code . "code to the IPN Validation POST." );

      throw new Exception( "PayPal responded with http code $http_code" );
    }

    // Close the curl request
    curl_close( $ch );

    // Check if PayPal verifies the IPN data, and if so, return true.
    $this->Log_IPN_Results( $res == 'VERIFIED' );
    if ( $res == 'VERIFIED' ) {
      $this->Log_Entry( 'INFO', "The transaction was verified and completed." );

      return true;
    }
    else {
      $this->Log_Entry( 'INFO', "The transaction was not verified and could not be completed." );

      return false;
    }

  }

}

?>