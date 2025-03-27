<?php
session_start();
include 'config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender = $_SESSION['user_id'];  // Sender's ID
    $receiver_id = $_POST['receiver'];  // Receiver's ID
    $amount = $_POST['amount'];  // Amount to send

    // Get the receiver's phone number using their ID
    $sql = "SELECT phone, balance FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $receiver_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($receiver_phone, $receiver_balance);
    $stmt->fetch();

    // Check if receiver exists
    if (!$receiver_phone) {
        echo "Receiver not found!";
        exit();
    }

    // Check if the sender has enough balance
    $sender_balance = $_SESSION['balance'];
    if ($sender_balance >= $amount) {
        // Deduct from sender and add to receiver
        $conn->query("UPDATE users SET balance = balance - $amount WHERE id = $sender");
        $conn->query("UPDATE users SET balance = balance + $amount WHERE id = $receiver_id");

        // Insert the transaction record
        $conn->query("INSERT INTO transactions (sender_id, receiver_id, amount, type, date) 
                      VALUES ($sender, $receiver_id, $amount, 'send_money', NOW())");

        // Update the session balance
        $_SESSION['balance'] -= $amount;

        echo "Money sent successfully to " . $receiver_phone;
    } else {
        echo "Insufficient balance!";
    }
}
?>
