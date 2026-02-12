<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: landing.php");
    exit();
}

$conn = new mysqli("localhost","root","","tripops_db");
if ($conn->connect_error) die("DB Error");

$message = "";

if($_SERVER["REQUEST_METHOD"] === "POST"){

    $invite_code = strtoupper(trim($_POST['invite_code']));
    $user_id = $_SESSION['user_id'];

    // Check if trip exists
    $stmt = $conn->prepare("SELECT id FROM trips WHERE invite_code=? AND trip_type='group'");
    $stmt->bind_param("s",$invite_code);
    $stmt->execute();
    $trip = $stmt->get_result()->fetch_assoc();

    if($trip){

        $trip_id = $trip['id'];

        // Check if already joined
        $check = $conn->prepare("SELECT id FROM trip_members WHERE trip_id=? AND user_id=?");
        $check->bind_param("ii",$trip_id,$user_id);
        $check->execute();
        $exists = $check->get_result()->fetch_assoc();

        if(!$exists){
            $role = "member";
            $insert = $conn->prepare("INSERT INTO trip_members (trip_id,user_id,role) VALUES (?,?,?)");
            $insert->bind_param("iis",$trip_id,$user_id,$role);
            $insert->execute();
        }

        header("Location: trip_dashboard.php?trip_id=".$trip_id);
        exit();

    } else {
        $message = "Invalid Invite Code";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Join Trip</title>
</head>
<body>

<h2>Join Group Trip</h2>

<form method="POST">
    <label>Enter Invite Code:</label>
    <input type="text" name="invite_code" required>
    <button type="submit">Join</button>
</form>

<p style="color:red;"><?= $message ?></p>

</body>
</html>
