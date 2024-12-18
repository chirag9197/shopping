<?php
session_start();
error_reporting(0);
include('includes/config.php');

// Check if the user is logged in
if (strlen($_SESSION['login']) == 0) {
	header('location:login.php');
} else {
	// Process the payment submission
	if (isset($_POST['submit'])) {
		if ($_POST['paymethod'] == 'Razorpay') {
			// Get Razorpay payment ID
			$razorpay_payment_id = $_POST['razorpay_payment_id'] ?: 'DEMO12345';
			// Update order with Razorpay payment details
			mysqli_query($con, "UPDATE orders SET paymentMethod='Razorpay', transactionId='$razorpay_payment_id' WHERE userId='" . $_SESSION['id'] . "' AND paymentMethod IS NULL");
		} else {
			// Update order with selected payment method
			mysqli_query($con, "UPDATE orders SET paymentMethod='" . $_POST['paymethod'] . "' WHERE userId='" . $_SESSION['id'] . "' AND paymentMethod IS NULL");
		}
		// Clear the cart session
		unset($_SESSION['cart']);
		// Redirect to order history
		header('location:order-history.php');
	}

	// Calculate the total cart amount
	$cartTotal = 0;
	if (!empty($_SESSION['cart'])) {
		foreach ($_SESSION['cart'] as $item) {
			$cartTotal += $item['price'] * $item['quantity']; // Total = price x quantity
		}
	}
?>
	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<title>Shopping Portal | Payment Method</title>
		<link rel="stylesheet" href="assets/css/bootstrap.min.css">
		<link rel="stylesheet" href="assets/css/main.css">
		<link rel="stylesheet" href="assets/css/green.css">
		<link rel="stylesheet" href="assets/css/font-awesome.min.css">
		<link href="http://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet" type="text/css">
		<link rel="shortcut icon" href="assets/images/favicon.ico">
		<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
	</head>

	<body class="cnt-home">
		<!-- Header -->
		<header class="header-style-1">
			<?php include('includes/top-header.php'); ?>
			<?php include('includes/main-header.php'); ?>
			<?php include('includes/menu-bar.php'); ?>
		</header>

		<!-- Breadcrumb -->
		<div class="breadcrumb">
			<div class="container">
				<div class="breadcrumb-inner">
					<ul class="list-inline list-unstyled">
						<li><a href="index.php">Home</a></li>
						<li class='active'>Payment Method</li>
					</ul>
				</div>
			</div>
		</div>

		<!-- Payment Method Selection -->
		<div class="body-content outer-top-bd">
			<div class="container">
				<div class="checkout-box faq-page inner-bottom-sm">
					<div class="row">
						<div class="col-md-12">
							<h2>Choose Payment Method</h2>
							<form name="payment" method="post" id="paymentForm">
								<input type="radio" name="paymethod" value="COD" checked="checked"> COD
								<input type="radio" name="paymethod" value="Razorpay" id="razorpayOption"> Razorpay <br /><br />
								<input type="hidden" name="razorpay_payment_id" id="razorpayPaymentId" value="">
								<input type="hidden" id="cartTotal" value="<?php echo $cartTotal * 100; ?>"> <!-- Pass total in paise -->
								<input type="submit" value="Submit" name="submit" class="btn btn-primary">
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Razorpay Integration Script -->
		<script>
			document.getElementById('paymentForm').addEventListener('submit', function(e) {
				if (document.getElementById('razorpayOption').checked) {
					e.preventDefault(); // Prevent default form submission

					// Get cart total amount
					var cartTotal = document.getElementById('cartTotal').value;

					// Razorpay options
					var options = {
						"key": "rzp_test_HeDwDwTRNuhxvu", // Razorpay API Key
						"amount": cartTotal, // Total amount in paise
						"currency": "INR",
						"name": "Demo Shop",
						"description": "Demo Payment",
						"image": "assets/images/logo.png",
						"handler": function(response) {
							// On successful payment
							document.getElementById('razorpayPaymentId').value = response.razorpay_payment_id;
							document.getElementById('paymentForm').submit(); // Submit the form
						},
						"prefill": {
							"name": "Test User", // Pre-filled user details
							"email": "test@example.com",
							"contact": "9999999999"
						},
						"theme": {
							"color": "#528FF0"
						}
					};

					var razorpay = new Razorpay(options);
					razorpay.open(); // Open Razorpay modal
				}
			});
		</script>

		<!-- Scripts -->
		<script src="assets/js/jquery-1.11.1.min.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
	</body>

	</html>
<?php } ?>