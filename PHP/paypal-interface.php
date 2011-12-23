<?php
// Connect to database ------------------------------------------------------------------------------------------------------
//require_once 'connect_to_mysql.php';
require_once('../../php_inc/xxx_connect.php');
$db = mysql_connect(xxx_DB_HOST, xxx_DB_USER, xxx_DB_PASS);
mysql_select_db("backend_production", $db);//interfaces with Ruby on Rails backend_DB's

if ($_SERVER['REQUEST_METHOD'] != "POST") die ("No Post Variables");

// Initialize the $req variable and add CMD key value pair
$req = 'cmd=_notify-validate';

// Read the post from PayPal
foreach ($_POST as $key => $value) {
    $value = urlencode(stripslashes($value));
    $req .= "&$key=$value";
}

// Now Post all of that back to PayPal's server using curl, and validate everything with PayPal
// Use CURL instead of PHP for this for a more universally operable script (fsockopen has issues on some environments)
$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";//for set-up and debugging
//$url = "https://www.paypal.com/cgi-bin/webscr";//for live transactions
$curl_result=$curl_err='';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,$url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Content-Length: " . strlen($req)));
curl_setopt($ch, CURLOPT_HEADER , 0);   
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
$curl_result = @curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

$req = str_replace("&", "\n", $req); //make it pretty

// Check that the result verifies
if (strpos($curl_result, "VERIFIED") !== false) {
    $req .= "\n\nPaypal Verified OK";
} else {
	$req .= "\n\nData NOT verified from Paypal!";
	sendmail("somebody@gmail.com", "IPN interaction not verified", "$req", "From: paypal@somecompany.com" );
	exit();
}

/* CHECK THESE 4 THINGS BEFORE PROCESSING THE TRANSACTION
1. Make sure that business email returned is your business email
2. Make sure that the transaction's payment status is 'completed'
3. Make sure there are no duplicate txn_id
4. Make sure the payment amount matches what you charge for items. (Defeat Price-Jacking) */
 
// Check Number 1 ------------------------------------------------------------------------------------------------------------

$receiver_email = $_POST['receiver_email'];
if ($receiver_email != "somebody@gmail.com") {
	$message = "Investigate why and how receiver email is wrong. Email = " . $_POST['receiver_email'] . "\n\n\n$req";
    sendmail("somebody@gmail.com", "Receiver Email is incorrect", $message, "From: paypal@somecompany.com" );
    exit(); 
}


// Check number 2 ------------------------------------------------------------------------------------------------------------

if ($_POST['payment_status'] != "Completed") {
	// Handle if a payment is not complete yet, a few scenarios can cause a transaction to be incomplete
	$message = "Investigate why transaction was not marked Completed in the paypal ipn.  payment_status = " . $_POST['payment_status'] . "\n\n\n$req";
    sendmail("somebody@gmail.com", "PayPal Transaction not Completed - IPN", $message, "From: paypal@somecompany.com" );
    exit();
}

// Check number 3 ------------------------------------------------------------------------------------------------------------

$this_txn = $_POST['txn_id'];
$sql = mysql_query("SELECT transaction_id FROM purchases WHERE transaction_id='$this_txn' LIMIT 1");
$numRows = mysql_num_rows($sql);
if ($numRows > 0) {
    $message = "Duplicate transaction ID occured so we killed the IPN script. \n\n\n$req";
    sendmail("somebody@gmail.com", "Duplicate txn_id in the IPN system", $message, "From: paypal@somecompany.com" );
    exit();
} 

// Check number 4 - adds up the orders ---------------------------------------------------------

$product_id_string = $_POST['custom'];
$product_id_string = rtrim($product_id_string, ","); // remove last comma
// Explode the string, make it an array, then query all the prices out, add them up, and make sure they match the payment_gross amount
$id_str_array = explode(",", $product_id_string); // Uses Comma(,) as delimiter(break point)
$fullAmount = 0;
foreach ($id_str_array as $key => $value) {
    
	$id_quantity_pair = explode("-", $value); // Uses Hyphen(-) as delimiter to separate product ID from its quantity
	$product_id = $id_quantity_pair[0]; // Get the product ID
	$product_quantity = $id_quantity_pair[1]; // Get the quantity
	$sql = mysql_query("SELECT price FROM products WHERE id='$product_id' LIMIT 1");
    while($row = mysql_fetch_array($sql)){
		$product_price = $row["price"];
	}
	$product_price = $product_price * $product_quantity;
	$fullAmount = $fullAmount + $product_price;
}
$fullAmount = number_format($fullAmount, 2);
$grossAmount = $_POST['mc_gross']; 
if ($fullAmount != $grossAmount) {
        $message = "Possible Price Jack: " . $_POST['payment_gross'] . " != $fullAmount \n\n\n$req";
        sendmail("somebody@gmail.com", "Price Jack or Bad Programming", $message, "From: paypal@somecompany.com" );
        exit(); 
} 
// End checking

$txn_id = $_POST['txn_id'];
$payer_email = $_POST['payer_email'];
$custom = $_POST['custom'];

// Place the transaction into the database
$sql = mysql_query("INSERT INTO transactions (product_id_array, payer_email, first_name, last_name, payment_date, mc_gross, payment_currency, txn_id, receiver_email, payment_type, payment_status, txn_type, payer_status, address_street, address_city, address_state, address_zip, address_country, address_status, notify_version, verify_sign, payer_id, mc_currency, mc_fee) 
   VALUES('$custom','$payer_email','$first_name','$last_name','$payment_date','$mc_gross','$payment_currency','$txn_id','$receiver_email','$payment_type','$payment_status','$txn_type','$payer_status','$address_street','$address_city','$address_state','$address_zip','$address_country','$address_status','$notify_version','$verify_sign','$payer_id','$mc_currency','$mc_fee')") or die ("unable to execute the query");

//Interface with Purchases and Authorize Orders
$txn_id = $_POST['txn_id'];
$invoice = $_POST['invoice'];
$custom = $_POST['custom'];
$status = $_POST['payment_status'];
$paid = $_POST['mc_gross'];
$time = date("Y-m-d H:i:s");

$sql = mysql_query("SELECT * FROM purchases WHERE id='".$invoice."' LIMIT 1");
    if($row = mysql_fetch_array($sql)){
		$amount = $row["amount"];
		$balance = $row["balance"];
		if($amount == $paid){
			$balance = 0.00;
			$sql = "update backend_production.purchases set  status = '".$status."', paypal_ipn = '".$req."', transaction_id = '".$txn_id."',  updated_at = '".$time."',  balance = '".$balance."', paid = '".$paid."' where id = '".$invoice."'";
				mysql_query($sql, $db);	
			$sql = "update backend_production.orders set  transaction_id = '".$txn_id."',  updated_at = '".$time."', status = 'Authorized' where pid = '".$invoice."'";
				mysql_query($sql, $db);
			$sql_o = mysql_query("SELECT * FROM backend_production.orders WHERE pid = '".$invoice."'");
				while($order = mysql_fetch_array($sql_o)){
					$cid = $order["cid"];
					$order_id = $order["id"];
					$sql_i = mysql_query("SELECT * FROM backend_production.order_items WHERE item = '".$order["item"]."' LIMIT 1");
						 if($order_item = mysql_fetch_array($sql_i)){
							 	$units = $order_item["units"];
								$item_type = $order_item["item_type"];
								if($order_item["item"] == "1007"){//1007 = 1 flyer & 20 pack
									$sql4 = "INSERT INTO paid_orders
									(cid, order_id, item_type, status, created_at, updated_at)
									VALUES
									('$cid','$order_id','Flyer','Open','$time','$time')";
									mysql_query($sql4, $db);
									for($i=0; $i<20; $i++){
										$sql4 = "INSERT INTO paid_orders
										(cid, order_id, item_type, status, created_at, updated_at)
										VALUES
										('$cid','$order_id','Product','Open','$time','$time')";
										mysql_query($sql4, $db);
									}
								}
								for($i=0; $i<$units; $i++){
									$sql4 = "INSERT INTO paid_orders
									(cid, order_id, item_type, status, created_at, updated_at)
									VALUES
									('$cid','$order_id','$item_type','Open','$time','$time')";
									
									mysql_query($sql4, $db);
								}
						 }
				}
		  }
	}

mysql_close($db);

// Mail the details
sendmail("somebody@gmail.com", "NORMAL IPN RESULT YAY MONEY!", $req, "From: paypal@somecompany.com");
?>