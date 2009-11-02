<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// THIS FILE IS CALLED WHEN ANY BUTTON ON THE CHECKOUT PAGE (PAYPAL CHECKOUT, UPDATE, OR EMPTY) IS CLICKED
// WE CAN ONLY DEFINE ONE FORM ACTION, SO THIS FILE ALLOWS US TO FORK THE FORM SUBMISSION DEPENDING ON WHICH BUTTON WAS CLICKED
// ALSO ALLOWS US TO VERIFY PRICES BEFORE SUBMITTING TO PAYPAL

// INCLUDE JCART BEFORE SESSION START
include_once 'jcart.php';

// START SESSION
session_start();

// INITIALIZE JCART AFTER SESSION START
$cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new jcart();

// WHEN JAVASCRIPT IS DISABLED THE UPDATE AND EMPTY BUTTONS ARE DISPLAYED
// RE-DISPLAY THE CART IF THE VISITOR CLICKS EITHER BUTTON
if ($_POST['jcart_update_cart']  || $_POST['jcart_empty'])
	{

	// UPDATE THE CART
	if ($_POST['jcart_update_cart'])
		{
		$cart_updated = $cart->update_cart();
		if ($cart_updated !== true)
			{
			$_SESSION['quantity_error'] = true;
			}
		}

	// EMPTY THE CART
	if ($_POST['jcart_empty'])
		{
		$cart->empty_cart();
		}

	// REDIRECT BACK TO THE CHECKOUT PAGE
	header('Location: ' . $_POST['jcart_checkout_page']);
	exit;
	}

// THE VISITOR HAS CLICKED THE PAYPAL CHECKOUT BUTTON
else
	{
	
	$domain = $_SERVER['HTTP_HOST'];
	$path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
	$path_parent = substr($path, 0, strrpos($path, '/'));

  if ($_POST['jcart_checkout_paypal'] && !$_POST['jcart-checkout2-submit']) {
  
    header( 'Location: http://' . $domain . $path_parent . '/checkout2.php');
  	exit;
  
  } else if ($_POST['jcart-checkout2-submit']) {
    
    $customer = array('first_name' => $_POST['customer_first_name'], 'last_name' => $_POST['customer_last_name'], 'address_one' => $_POST['customer_address_one'], 'address_two' => $_POST['customer_address_two'], 'city' => $_POST['customer_city'], 'state' => $_POST['customer_state'], 'zip' => $_POST['customer_zip'], 'phone' => $_POST['customer_phone'], 'email' => $_POST['customer_email']);
  
  	///////////////////////////////////////////////////////////////////////
  	///////////////////////////////////////////////////////////////////////
  	/*
  
  	A malicious visitor may try to change item prices before checking out,
  	either via javascript or by posting from an external script.
  
  	Here you can add PHP code that validates the submitted prices against
  	your database or validates against hard-coded prices.
  
  	The cart data has already been sanitized and is available thru the
  	$cart->get_contents() function. For example:
  
  	foreach ($cart->get_contents() as $item)
  		{
  		$item_id	= $item['id'];
  		$item_name	= $item['name'];
  		$item_price	= $item['price'];
  		$item_qty	= $item['qty'];
  		}
  
  	*/
  	///////////////////////////////////////////////////////////////////////
  	///////////////////////////////////////////////////////////////////////
  	$valid_prices = true;
  	///////////////////////////////////////////////////////////////////////
  	///////////////////////////////////////////////////////////////////////
  
  	// IF THE SUBMITTED PRICES ARE NOT VALID
  	if ($valid_prices !== true)
  		{
  		// KILL THE SCRIPT
  		die($jcart['text']['checkout_error']);
  		}
  
  	// PRICE VALIDATION IS COMPLETE
  	// SEND CART CONTENTS TO PAYPAL USING THEIR UPLOAD METHOD, FOR DETAILS SEE http://tinyurl.com/djoyoa
  	else if ($valid_prices === true)
  		{
  		$today = date('Y-m-d');
  		
//set up SQL connection
$host = 'localhost';
$username = 'USERNAME';
$password = 'PASSWORD';
$database = 'sativaon_delivery';
$connect = mysql_connect($host,$username,$password) or die('Unable to connect to the database.');
mysql_select_db($database,$connect) or die('Unable to connect to the database table.');

  		foreach ($cart->get_contents() as $store => $items) {
      		$cart_summary = '';
          $store_subtotal = 0;
          foreach ($items as $id=>$item) {
          	$cart_summary .= 'Product:'.$id.', Name:'.$item['name'].', Qty:'.$item['qty'];
          	//Outputs options selected
          	//Your store will probably not use this entire IF statement block,
            // unless you start using the product options form fields.
            // It's here, just in case you do.
          	if (empty($item['option']) !== true) {
              $cart_summary .= ", Option:(";
              foreach ($item['option'] as $value) {
                $cart_summary .= $value['name'].':'.$value['value'].';';
              }
              $cart_summary .= '); ';
            } else {
              $cart_summary .= '; ';
            }
          	$store_subtotal += $item['subtotal'];
          }
//SQL inject
$query = "INSERT INTO `sativaon_delivery`.`orders` (`ID`, `customer_first_name`, `customer_last_name`, `customer_address_one`, `customer_address_two`, `customer_city`, `customer_state`, `customer_zip`, `customer_phone`, `customer_email`, `invoice_ammount`, `purchase_date`, `last_update`, `invoice_summery`, `store_ID`)";
$query .= " VALUES (NULL, '".$customer['first_name']."', '".$customer['last_name']."', '".$customer['address_one']."', '".$customer['address_two']."', '".$customer['city']."', '".$customer['state']."', '".$customer['zip']."', '".$customer['phone']."', '".$customer['email']."', '".$store_subtotal."', '".$today."', NULL, '".$cart_summary."', '".$store."');";

$result = @mysql_query("$query") or die('Error inserting new row in table.');

if ($result !== true) {
  //OOPS, there was an error inserting the row.
}
  		}// foreach($cart->get_contents())
  			
  		// EMPTY THE CART
  		$cart->empty_cart();
  		
      header( 'Location: http://' . $domain. $path_parent . '/checkout3.php');
    	exit;
  		
  		} //valid_prices === true
		} else {
      header( 'Location: http://' . $domain . '/');
      exit;
    }//if 'jcart-checkout2-submit'
	}

?>
