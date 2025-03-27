<?php
session_start();
include 'config/db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view this page.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details from the database
$sql = "SELECT name, balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $user_name = htmlspecialchars($row['name']);
    $balance = number_format($row['balance'], 2);
} else {
    echo "Error: User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navbar -->
    <header class="navbar">
        <div class="logo">Takai Jibon</div>
        <nav>
            
            <a href="#">Services</a>
            <a href="#">Business</a>
            <a href="#">Help</a>
            <a href="#">About</a>
        </nav>
        <button class="logout-btn">Logout</button>
    </header>

    <!-- Main Content -->
    <div class="container">
        <div class="balance-card">
            <h3>Welcome, <?php echo $user_name; ?>!</h3>
            <p>Your current balance is:</p>
            <div class="amount">৳<?php echo $balance; ?></div>
        </div>

        <!-- Shortcut Grid -->
<div class="shortcuts-grid">
    <a href="send_money.php" class="shortcut-item">
        <img src="icons/send_money.png" alt="Send Money">
        <p>Send Money</p>
    </a>

    <a href="mobile_recharge.php" class="shortcut-item">
        <img src="icons/mobile_recharge.png" alt="Mobile Recharge">
        <p>Mobile Recharge</p>
    </a>

    <a href="add_money.php" class="shortcut-item">
        <img src="icons/add_money.png" alt="Add Money">
        <p>Add Money</p>
    </a>

    <a href="payment.php" class="shortcut-item">
        <img src="icons/payment.png" alt="Payment">
        <p>Payment</p>
    </a>

    <a href="pay_bill.php" class="shortcut-item">
        <img src="icons/pay_bill.png" alt="Pay Bill">
        <p>Pay Bill</p>
    </a>

    <a href="bkash_to_bank.php" class="shortcut-item">
        <img src="icons/bkash_to_bank.png" alt="bKash to Bank">
        <p>bKash to Bank</p>
    </a>

    <a href="cash_out.php" class="shortcut-item">
        <img src="icons/cash_out.png" alt="Cash Out">
        <p>Cash Out</p>
    </a>

    <a href="receive_money.php" class="shortcut-item">
        <img src="icons/receive_money.png" alt="Receive Money">
        <p>Receive Money</p>
    </a>
</div>
    </div>
</body>
</html>

