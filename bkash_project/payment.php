<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Please log in to make a payment.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch list of merchants
$sql = "SELECT id, name FROM merchants";
$result = $conn->query($sql);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $merchant_id = $_POST['merchant_id'];
    $amount = $_POST['amount'];

    // Check user balance
    $balanceQuery = "SELECT balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($balanceQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($balance);
    $stmt->fetch();
    
    if ($balance >= $amount) {
        // Deduct balance from user
        $updateBalance = "UPDATE users SET balance = balance - ? WHERE id = ?";
        $stmt = $conn->prepare($updateBalance);
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();

        // Record the payment
        $paymentQuery = "INSERT INTO payments (user_id, merchant_id, amount, status) VALUES (?, ?, ?, 'completed')";
        $stmt = $conn->prepare($paymentQuery);
        $stmt->bind_param("iid", $user_id, $merchant_id, $amount);
        $stmt->execute();
        
        // ✅ Insert into transactions table
        $transactionQuery = "INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES (?, ?, ?, 'payment')";
        $stmt = $conn->prepare($transactionQuery);
        $stmt->bind_param("iid", $user_id, $merchant_id, $amount);
        $stmt->execute();

        echo "✅ Payment of $" . number_format($amount, 2) . " to the merchant was successful!";
    } else {
        echo "❌ Insufficient balance!";
    }
}
?>

<h2>Make a Payment</h2>
<form method="post">
    <label for="merchant_id">Select Merchant:</label>
    <select name="merchant_id" id="merchant_id" required>
        <option value="">-- Select Merchant --</option>
        <?php
        // Display merchants from the database
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
            }
        } else {
            echo "<option value=''>No merchants available</option>";
        }
        ?>
    </select><br><br>

    <label for="amount">Amount to Pay:</label>
    <input type="number" name="amount" id="amount" min="1" required><br><br>

    <button type="submit">Pay</button>
</form>
