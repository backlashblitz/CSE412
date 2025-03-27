<?php
session_start();
include 'config/db.php'; // Ensure the path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to cash out.";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user balance from the database
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists and has a balance
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $balance = $row['balance'];
} else {
    echo "User not found.";
    exit();
}

// Handle cash-out request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cash_out_amount = $_POST['amount'];
    $agent_id = $_POST['agent_id']; // ID of the agent receiving the money

    // Check if the cash out amount is valid
    if ($cash_out_amount > 0 && $cash_out_amount <= $balance) {
        // Update user balance (deduct from user's balance)
        $new_balance = $balance - $cash_out_amount;
        $sql = "UPDATE users SET balance = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $new_balance, $user_id);
        if ($stmt->execute()) {
            // Insert cash-out transaction
            $cash_out_sql = "INSERT INTO cash_out (user_id, agent_id, amount) VALUES (?, ?, ?)";
            $cash_out_stmt = $conn->prepare($cash_out_sql);
            $cash_out_stmt->bind_param("iid", $user_id, $agent_id, $cash_out_amount);
            $cash_out_stmt->execute();
            echo "Cash-out successful. Your new balance is: ৳" . number_format($new_balance, 2);
        } else {
            echo "Error updating balance.";
        }
    } else {
        echo "Insufficient balance or invalid amount.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cash Out</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Add your CSS file -->
</head>
<body>

<h2>Cash Out to Agent</h2>

<form method="POST" action="">
    <label for="amount">Amount to cash out (in ৳):</label>
    <input type="number" id="amount" name="amount" min="1" max="<?php echo $balance; ?>" required>
    
    <label for="agent_id">Select Agent:</label>
    <select id="agent_id" name="agent_id" required>
        <option value="">Select an agent</option>
        <?php
        // Fetch all active agents (merchants)
        $agent_sql = "SELECT id, name FROM merchants WHERE status = 'active'";
        $agent_result = $conn->query($agent_sql);
        
        if ($agent_result->num_rows > 0) {
            // Display agent options in the dropdown
            while ($agent = $agent_result->fetch_assoc()) {
                echo "<option value='" . $agent['id'] . "'>" . $agent['name'] . "</option>";
            }
        } else {
            echo "<option value=''>No agents available</option>";
        }
        ?>
    </select>
    
    <button type="submit">Cash Out</button>
</form>

<a href="dashboard.php">Back to Dashboard</a>

</body>
</html>
