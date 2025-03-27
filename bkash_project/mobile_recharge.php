<?php
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "Error: Unauthorized access!";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $phone_number = $_POST['phone_number'];
    $amount = floatval($_POST['amount']);
    $operator = $_POST['operator']; // Added this to capture the operator
    
    // Check if the user has enough balance to perform the recharge
    $userQuery = $conn->prepare("SELECT balance FROM users WHERE id = ?");
    $userQuery->bind_param("i", $user_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result();
    
    if ($userResult->num_rows === 0) {
        echo "Error: User not found!";
        exit();
    }
    
    $userData = $userResult->fetch_assoc();
    $user_balance = $userData['balance'];

    if ($user_balance < $amount) {
        echo "Error: Insufficient balance!";
        exit();
    }

    // Deduct the amount from the user's balance
    $new_balance = $user_balance - $amount;
    $updateBalanceQuery = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
    $updateBalanceQuery->bind_param("di", $new_balance, $user_id);
    $updateBalanceQuery->execute();
    
    // Insert the mobile recharge record into the database
    $insertRechargeQuery = $conn->prepare("INSERT INTO mobile_recharge (user_id, phone_number, amount, operator) VALUES (?, ?, ?, ?)");
    $insertRechargeQuery->bind_param("isss", $user_id, $phone_number, $amount, $operator);
    $insertRechargeQuery->execute();
    
    // Insert the transaction record for recharge (if you want to track it as a transaction)
    $insertTransactionQuery = $conn->prepare("INSERT INTO transactions (sender_id, receiver_id, amount, type) VALUES (?, ?, ?, 'recharge')");
    $insertTransactionQuery->bind_param("iid", $user_id, $user_id, $amount);
    $insertTransactionQuery->execute();

    echo "Mobile recharge successful! Your new balance is: " . $new_balance;
}
?>

<form method="post">
    <input type="text" name="phone_number" placeholder="Recipient Phone Number" required><br>
    <input type="number" name="amount" placeholder="Amount" required><br>
    <select name="operator" required>
    option value="Airtel">Airtel</option>
        <option value="Robi">Robi</option>
        <option value="Grameenphone">Grameenphone</option>
        <option value="Banglalink">Banglalink</option>
    </select><br>
    <button type="submit">Recharge</button>
    
    <br>
    <a href="dashboard.php">Go Back to Dashboard</a>
</form>
