<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please log in to add money.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle Add Money Form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];
    $transaction_id = uniqid("txn_");

    // Insert into the add_money table
    $sql = "INSERT INTO add_money (user_id, amount, payment_method, transaction_id, status) 
            VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idss", $user_id, $amount, $payment_method, $transaction_id);

    if ($stmt->execute()) {
        // Update user balance after adding money
        $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $user_id");
        echo "<p>Money added successfully! Your new balance is: $" . number_format($amount + $_SESSION['balance'], 2) . "</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
}

?>

<form method="post">
    <label for="amount">Amount to Add:</label>
    <input type="number" name="amount" required><br><br>

    <label for="payment_method">Payment Method:</label>
    <select name="payment_method" required>
        <option value="bank">Bank</option>
        <option value="card">Card</option>
    </select><br><br>

    <button type="submit">Add Money</button>

    
    <br>
    <a href="dashboard.php">Go Back to Dashboard</a>
</form>

