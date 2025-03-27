<?php
session_start();
include 'config/db.php';

$user_id = $_SESSION['user_id'];  // The sender's ID

// Fetch all users excluding the current user (sender)
$sql = "SELECT id, name, phone FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<h2>Send Money</h2>
<form action="send_money_process.php" method="post">
    <label for="receiver">Select Recipient:</label>
    <select name="receiver" required>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <option value="<?php echo $row['id']; ?>">
                <?php echo $row['name'] . " (" . $row['phone'] . ")"; ?>
            </option>
        <?php } ?>
    </select><br>
    <input type="number" name="amount" placeholder="Amount" required><br>
    <button type="submit">Send Money</button>
</form>
