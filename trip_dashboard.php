<?php
session_start();
$conn = new mysqli("localhost","root","","tripops_db");
if ($conn->connect_error) die("DB Error");

// Dummy user for testing
$user_id = $_SESSION['user_id'] ?? 1;

// ==============================
// TERMINATE TRIP
// ==============================
if(isset($_POST['terminate_trip']) && isset($_SESSION['created_trip_id'])) {
    $trip_id = $_SESSION['created_trip_id'];

    $stmt = $conn->prepare("UPDATE trips SET status='finished' WHERE id=?");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();

    unset($_SESSION['created_trip_id']);
    unset($_SESSION['trip_type']);
    unset($_SESSION['group_role']);
    unset($_SESSION['invite_code']);
    unset($_SESSION['selected_destination']);

    header("Location: trip_dashboard.php");
    exit();
}

// ==============================
// CAPTURE DESTINATION
// ==============================
if(isset($_GET['place']) && !isset($_SESSION['selected_destination'])) {
    $_SESSION['selected_destination'] = $_GET['place'];
}

// ==============================
// STEP HANDLERS
// ==============================
if(isset($_POST['select_trip_type'])) {
    $_SESSION['trip_type'] = $_POST['trip_type'];
}

if(isset($_POST['select_group_role'])) {
    $_SESSION['group_role'] = $_POST['group_role'];
}

if(isset($_POST['create_trip'])) {
    $destination = $_POST['destination'];
    $trip_type = $_SESSION['trip_type'] ?? 'solo';
    $invite_code = null;

    if($trip_type === "group") {
        $invite_code = strtoupper(substr(md5(time()),0,6));
    }

    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;

    $stmt = $conn->prepare("INSERT INTO trips (host_id,destination_slug,trip_type,invite_code,start_date,end_date,created_at,status) VALUES (?,?,?,?,?,?,NOW(),'active')");
    $stmt->bind_param("isssss",$user_id,$destination,$trip_type,$invite_code,$start_date,$end_date);
    $stmt->execute();
    $trip_id = $stmt->insert_id;

    if($trip_type === "group") {
        $role = "host";
        $stmt2 = $conn->prepare("INSERT INTO trip_members (trip_id,user_id,role) VALUES (?,?,?)");
        $stmt2->bind_param("iis",$trip_id,$user_id,$role);
        $stmt2->execute();
    }

    $_SESSION['created_trip_id'] = $trip_id;
    $_SESSION['invite_code'] = $invite_code;
    $_SESSION['selected_destination'] = $destination;
}

if(isset($_POST['join_trip'])) {
    $code = $_POST['invite_code'];
    $stmt = $conn->prepare("SELECT id FROM trips WHERE invite_code=? AND status='active'");
    $stmt->bind_param("s",$code);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0) {
        $trip = $result->fetch_assoc();
        $trip_id = $trip['id'];

        $check = $conn->prepare("SELECT id FROM trip_members WHERE trip_id=? AND user_id=?");
        $check->bind_param("ii",$trip_id,$user_id);
        $check->execute();
        $already = $check->get_result()->fetch_assoc();

        if(!$already){
            $role = "member";
            $stmt2 = $conn->prepare("INSERT INTO trip_members (trip_id,user_id,role) VALUES (?,?,?)");
            $stmt2->bind_param("iis",$trip_id,$user_id,$role);
            $stmt2->execute();
        }

        $_SESSION['created_trip_id'] = $trip_id;
        header("Location: trip_dashboard.php?trip_id=".$trip_id);
        exit();
    } else {
        $join_error = "Invalid or finished Invite Code";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trip Dashboard</title>
</head>
<body>
<h2>Trip Dashboard</h2>

<?php
// STEP 1: Select Solo or Group
if(!isset($_SESSION['trip_type'])):
?>
<form method="POST">
    <h3>Select Trip Type</h3>
    <input type="radio" name="trip_type" value="solo" required> Solo Trip<br>
    <input type="radio" name="trip_type" value="group" required> Group Trip<br><br>
    <button type="submit" name="select_trip_type">Continue</button>
</form>

<?php
// STEP 2: Group → Host or Member
elseif($_SESSION['trip_type']==="group" && !isset($_SESSION['group_role'])):
?>
<form method="POST">
    <h3>Are You Host or Member?</h3>
    <input type="radio" name="group_role" value="host" required> Host<br>
    <input type="radio" name="group_role" value="member" required> Member<br><br>
    <button type="submit" name="select_group_role">Continue</button>
</form>

<?php
// STEP 3A: Solo or Host → Create Trip
elseif($_SESSION['trip_type']==="solo" || ($_SESSION['trip_type']==="group" && $_SESSION['group_role']==="host")):
    if(!isset($_SESSION['created_trip_id'])):
        $destination = $_SESSION['selected_destination'] ?? '';
?>
<form method="POST">
    <h3>Create Trip</h3>
    <input type="hidden" name="destination" value="<?= htmlspecialchars($destination) ?>">
    <p>Destination: <b><?= htmlspecialchars($destination) ?></b></p>
    Start Date: <input type="date" name="start_date" required><br><br>
    End Date: <input type="date" name="end_date" required><br><br>
    <button type="submit" name="create_trip">Create Trip</button>
</form>

<?php
    else:
        echo "<h3>Trip Created Successfully!</h3>";

        $trip_id = $_SESSION['created_trip_id'];
        $stmt = $conn->prepare("SELECT start_date,end_date,destination_slug,invite_code FROM trips WHERE id=?");
        $stmt->bind_param("i", $trip_id);
        $stmt->execute();
        $trip_row = $stmt->get_result()->fetch_assoc();

        $start_date = $trip_row['start_date'] ?? '';
        $end_date = $trip_row['end_date'] ?? '';
        $destination = $trip_row['destination_slug'] ?? '';
        $invite_code = $trip_row['invite_code'] ?? '';

        echo "<p>Trip Dates: $start_date → $end_date</p>";
        if($_SESSION['trip_type']==="group"){
            echo "<p><b>Invite Code for Members:</b> $invite_code</p>";
        }
        ?>

        <form method="POST" style="margin-top:20px;">
            <button type="submit" name="terminate_trip" style="background:red;color:white;">
                Finish / Terminate Trip
            </button>
        </form>

<?php
    endif;

// STEP 3B: Member → Enter Invite Code
elseif($_SESSION['group_role']==="member"):
    if(!isset($_SESSION['created_trip_id'])):
        $join_error = $join_error ?? '';
?>
<form method="POST">
    <h3>Enter Invite Code</h3>
    <input type="text" name="invite_code" required><br><br>
    <button type="submit" name="join_trip">Join Trip</button>
</form>
<p style="color:red;"><?= htmlspecialchars($join_error) ?></p>
<?php
    else:
        echo "<h3>Successfully Joined Trip!</h3>";
    endif;
endif;

// GROUP CHAT
if(isset($_SESSION['trip_type']) && $_SESSION['trip_type']==="group" && isset($_SESSION['created_trip_id'])):
    $trip_id = $_SESSION['created_trip_id'];

    // Fetch messages
    $stmt = $conn->prepare("
        SELECT tm.message, u.name
        FROM trip_messages tm
        JOIN users u ON tm.user_id = u.id
        WHERE tm.trip_id = ?
        ORDER BY tm.created_at ASC
    ");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $messages = $stmt->get_result();

    echo "<h3>Group Chat</h3>";
    echo "<div style='border:1px solid #ccc;padding:10px;margin-bottom:10px;max-height:300px;overflow-y:auto;'>";
    while($row = $messages->fetch_assoc()){
        echo "<p><strong>" . htmlspecialchars($row['name']) . "</strong>: "
            . htmlspecialchars($row['message']) . "</p>";
    }
    echo "</div>";
endif;
?>
</body>
</html>
