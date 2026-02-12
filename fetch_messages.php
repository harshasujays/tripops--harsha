<?php
session_start();
$conn = new mysqli("localhost","root","","tripops_db");
if ($conn->connect_error) die("DB Error");

$trip_id = intval($_GET['trip_id']);

// Secure prepared statement
$stmt = $conn->prepare("
    SELECT tm.message, u.name
    FROM trip_messages tm
    JOIN users u ON tm.user_id = u.id
    WHERE tm.trip_id = ?
    ORDER BY tm.created_at ASC
");

$stmt->bind_param("i", $trip_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()){
    echo "<p><strong>" . htmlspecialchars($row['name']) . "</strong>: "
        . htmlspecialchars($row['message']) . "</p>";
}
