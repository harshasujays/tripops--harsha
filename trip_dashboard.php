<?php
session_start();
$conn = new mysqli("localhost","root","","tripops_db");
if ($conn->connect_error) die("DB Error");

$user_id = $_SESSION['user_id'] ?? 1;

/* =========================================
   AUTO RESTORE ACTIVE TRIP
========================================= */
if(!isset($_SESSION['created_trip_id'])) {
    $stmt = $conn->prepare("
        SELECT t.id, t.trip_type
        FROM trips t
        LEFT JOIN trip_members tm ON t.id = tm.trip_id
        WHERE 
            (t.host_id = ? OR tm.user_id = ?)
            AND t.status = 'active'
        LIMIT 1
    ");
    $stmt->bind_param("ii", $user_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows > 0){
        $trip = $result->fetch_assoc();
        $_SESSION['created_trip_id'] = $trip['id'];
        $_SESSION['trip_type'] = $trip['trip_type'];

        if($trip['trip_type'] === "group"){
            $checkHost = $conn->prepare("SELECT host_id FROM trips WHERE id=?");
            $checkHost->bind_param("i", $trip['id']);
            $checkHost->execute();
            $hostData = $checkHost->get_result()->fetch_assoc();
            $_SESSION['group_role'] = ($hostData['host_id'] == $user_id) ? "host" : "member";
        }
    }
}

/* =========================================
   BUDGET & PAYMENT LOGIC (NEW)
========================================= */
if (isset($_POST['update_budget'])) {
    $trip_id = $_SESSION['created_trip_id'];
    $new_cost = $_POST['total_estimated_cost'];
    $conn->query("INSERT INTO trip_budget (trip_id, total_cost) VALUES ($trip_id, $new_cost) 
                  ON DUPLICATE KEY UPDATE total_cost = $new_cost");
}

if (isset($_POST['process_fake_payment'])) {
    $trip_id = $_SESSION['created_trip_id'];
    $conn->query("INSERT INTO trip_payments (trip_id, user_id, status) VALUES ($trip_id, $user_id, 'verified') 
                  ON DUPLICATE KEY UPDATE status = 'verified'");
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* =========================================
   TERMINATE TRIP
========================================= */
if(isset($_POST['terminate_trip']) && isset($_SESSION['created_trip_id'])) {
    $trip_id = $_SESSION['created_trip_id'];
    $stmt = $conn->prepare("SELECT destination_slug FROM trips WHERE id=?");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $trip = $stmt->get_result()->fetch_assoc();
    $destination_slug = $trip['destination_slug'] ?? '';
    $stmt2 = $conn->prepare("UPDATE trips SET status='finished' WHERE id=?");
    $stmt2->bind_param("i", $trip_id);
    $stmt2->execute();
    unset($_SESSION['created_trip_id'], $_SESSION['trip_type'], $_SESSION['group_role'], $_SESSION['invite_code'], $_SESSION['selected_destination']);
    header("Location: destination_details.php?place=" . urlencode($destination_slug));
    exit();
}

/* =========================================
   CAPTURE DESTINATION
========================================= */
if(isset($_GET['place']) && !isset($_SESSION['created_trip_id'])){
    $_SESSION['selected_destination'] = $_GET['place'];
    header("Location: trip_dashboard.php");
    exit();
}

/* =========================================
   STEP HANDLERS
========================================= */
if(isset($_POST['select_trip_type'])) { $_SESSION['trip_type'] = $_POST['trip_type']; }
if(isset($_POST['select_group_role'])) { $_SESSION['group_role'] = $_POST['group_role']; }

/* =========================================
   CREATE TRIP
========================================= */
if(isset($_POST['create_trip'])) {
    $destination = $_POST['destination'];
    $trip_type = $_SESSION['trip_type'] ?? 'solo';
    $invite_code = ($trip_type === "group") ? strtoupper(substr(md5(time()),0,6)) : null;
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
    header("Location: trip_dashboard.php");
    exit();
}

/* =========================================
   JOIN TRIP
========================================= */
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
        if(!$check->get_result()->fetch_assoc()){
            $role = "member";
            $stmt2 = $conn->prepare("INSERT INTO trip_members (trip_id,user_id,role) VALUES (?,?,?)");
            $stmt2->bind_param("iis",$trip_id,$user_id,$role);
            $stmt2->execute();
        }
        $_SESSION['created_trip_id'] = $trip_id;
        $_SESSION['trip_type'] = "group";
        header("Location: trip_dashboard.php");
        exit();
    } else { $join_error = "Invalid or finished Invite Code"; }
}

/* =========================================
   SEND TRIP MESSAGE
========================================= */
if(isset($_POST['send_message']) && isset($_SESSION['created_trip_id'])) {
    $trip_id = $_SESSION['created_trip_id'];
    $message = $_POST['new_message'];
    $stmt = $conn->prepare("INSERT INTO trip_messages (trip_id,user_id,message) VALUES (?,?,?)");
    $stmt->bind_param("iis", $trip_id, $user_id, $message);
    $stmt->execute();
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Trip Dashboard</title>
    <style>
        .loader { border: 4px solid #f3f3f3; border-top: 4px solid #28a745; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; display: inline-block; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .budget-box { background: #fff; border: 1px solid #ddd; border-radius: 12px; padding: 20px; margin-top: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<h2>Trip Dashboard</h2>

<?php if(!isset($_SESSION['trip_type'])): ?>
<form method="POST">
    <h3>Select Trip Type</h3>
    <input type="radio" name="trip_type" value="solo" required> Solo Trip<br>
    <input type="radio" name="trip_type" value="group" required> Group Trip<br><br>
    <button type="submit" name="select_trip_type">Continue</button>
</form>

<?php elseif($_SESSION['trip_type']==="group" && !isset($_SESSION['group_role']) && !isset($_SESSION['created_trip_id'])): ?>
<form method="POST">
    <h3>Are You Host or Member?</h3>
    <input type="radio" name="group_role" value="host" required> Host<br>
    <input type="radio" name="group_role" value="member" required> Member<br><br>
    <button type="submit" name="select_group_role">Continue</button>
</form>

<?php elseif($_SESSION['trip_type']==="solo" || ($_SESSION['trip_type']==="group" && isset($_SESSION['group_role']) && $_SESSION['group_role']==="host")): 
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

<?php else: 
    $trip_id = $_SESSION['created_trip_id'];
    $stmt = $conn->prepare("SELECT * FROM trips WHERE id=?");
    $stmt->bind_param("i", $trip_id); $stmt->execute();
    $trip_row = $stmt->get_result()->fetch_assoc();

    $members = [];
    $stmt2 = $conn->prepare("SELECT u.id as uid, u.name, tm.role FROM trip_members tm JOIN users u ON tm.user_id = u.id WHERE tm.trip_id = ?");
    $stmt2->bind_param("i", $trip_id); $stmt2->execute();
    $res2 = $stmt2->get_result();
    while($row = $res2->fetch_assoc()){ $members[] = $row; }
?>
<h3>Trip Details</h3>
<p>Destination: <b><?= htmlspecialchars($trip_row['destination_slug']) ?></b></p>
<p>Trip Dates: <?= $trip_row['start_date'] ?> → <?= $trip_row['end_date'] ?></p>
<?php if($_SESSION['trip_type']==="group"): ?>
    <p><b>Invite Code:</b> <?= $trip_row['invite_code'] ?></p>
    
    <?php
        $b_res = $conn->query("SELECT total_cost FROM trip_budget WHERE trip_id = $trip_id");
        $total_val = ($b_res->num_rows > 0) ? $b_res->fetch_assoc()['total_cost'] : 0;
        $split_val = (count($members) > 0) ? $total_val / count($members) : 0;
        
        $p_check = $conn->query("SELECT status FROM trip_payments WHERE trip_id = $trip_id AND user_id = $user_id");
        $my_status = ($p_check->num_rows > 0) ? $p_check->fetch_assoc()['status'] : 'unpaid';
    ?>
    <div class="budget-box">
        <div style="flex: 1;">
            <h4 style="margin:0;">💰 Trip Budget</h4>
            <form method="POST" style="margin-top:10px;">
                <input type="number" name="total_estimated_cost" value="<?= $total_val ?>" style="width:100px;">
                <button type="submit" name="update_budget">Set Total</button>
            </form>
            <p>Your Share: <strong>$<?= number_format($split_val, 2) ?></strong></p>
        </div>

        <div id="payment-zone" style="flex: 1; text-align:right;">
            <div id="loader-display" style="display:none; text-align:center;">
                <div class="loader"></div>
                <p style="font-size:12px;">Processing...</p>
            </div>
            <div id="payment-ui">
                <?php if($my_status !== 'verified'): ?>
                    <button type="button" onclick="runPayment()" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Pay My Share</button>
                <?php else: ?>
                    <span style="color:#28a745; font-weight:bold;">✅ You have Paid</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <form id="hidden-pay-form" method="POST" style="display:none;"><input type="hidden" name="process_fake_payment" value="1"></form>
    <?php endif; ?>

<form method="POST" style="margin-top:20px;">
    <button type="submit" name="terminate_trip">Finish / Terminate Trip</button>
</form>

<?php if($_SESSION['trip_type'] === "group"): ?>
<div style="display:flex; gap:20px; margin-top:20px;">
    <div style="border:1px solid #ccc; padding:10px; width:220px; height:400px; overflow-y:auto;">
        <h4>Trip Members</h4>
        <ul style="list-style:none; padding:0;">
            <?php foreach($members as $m): 
                $pay_st = $conn->query("SELECT status FROM trip_payments WHERE trip_id=$trip_id AND user_id=".$m['uid']);
                $is_p = ($pay_st->num_rows > 0);
            ?>
                <li style="margin-bottom:5px;">
                    <?= $is_p ? '✅ ' : '⏳ ' ?><?= htmlspecialchars($m['name']) ?>
                    <?php if($m['role']==="host"): ?><span style="color:green; font-weight:bold;">(Host)</span><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div style="flex:1; border:1px solid #ccc; padding:10px; height:400px; display:flex; flex-direction:column;">
        <div id="chatBox" style="flex:1; overflow-y:auto; margin-bottom:10px;">
        <?php
            $messages = $conn->prepare("SELECT tm.message, u.name FROM trip_messages tm JOIN users u ON tm.user_id = u.id WHERE tm.trip_id=? ORDER BY tm.created_at ASC");
            $messages->bind_param("i", $trip_id); $messages->execute(); $res = $messages->get_result();
            while($msg = $res->fetch_assoc()):
        ?>
            <p><strong><?= htmlspecialchars($msg['name']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?></p>
        <?php endwhile; ?>
        </div>
        <form method="POST" style="display:flex; gap:5px;">
            <input type="text" name="new_message" placeholder="Type your message" required style="flex:1;">
            <button type="submit" name="send_message">Send</button>
        </form>
    </div>
</div>
<?php endif; ?>

<?php endif; 

elseif(isset($_SESSION['group_role']) && $_SESSION['group_role']==="member"): 
    if(!isset($_SESSION['created_trip_id'])):
?>
<form method="POST">
    <h3>Enter Invite Code</h3>
    <input type="text" name="invite_code" required><br><br>
    <button type="submit" name="join_trip">Join Trip</button>
</form>
<p style="color:red;"><?= htmlspecialchars($join_error ?? '') ?></p>

<?php else: 
    $trip_id = $_SESSION['created_trip_id'];
    $members = [];
    $stmt2 = $conn->prepare("SELECT u.id as uid, u.name, tm.role FROM trip_members tm JOIN users u ON tm.user_id = u.id WHERE tm.trip_id = ?");
    $stmt2->bind_param("i", $trip_id); $stmt2->execute();
    $res2 = $stmt2->get_result();
    while($row = $res2->fetch_assoc()){ $members[] = $row; }
?>
<h3>Successfully Joined Trip!</h3>

<?php if($_SESSION['trip_type'] === "group"): ?>
    <?php
        $b_res = $conn->query("SELECT total_cost FROM trip_budget WHERE trip_id = $trip_id");
        $total_val = ($b_res->num_rows > 0) ? $b_res->fetch_assoc()['total_cost'] : 0;
        $split_val = (count($members) > 0) ? $total_val / count($members) : 0;
        $p_check = $conn->query("SELECT status FROM trip_payments WHERE trip_id = $trip_id AND user_id = $user_id");
        $my_status = ($p_check->num_rows > 0) ? $p_check->fetch_assoc()['status'] : 'unpaid';
    ?>
    <div class="budget-box">
        <div>
            <h4 style="margin:0;">💰 Trip Budget</h4>
            <p>Total Group Cost: $<?= number_format($total_val, 2) ?></p>
            <p>Your Share: <strong>$<?= number_format($split_val, 2) ?></strong></p>
        </div>
        <div id="payment-zone-member">
            <div id="loader-display-mem" style="display:none; text-align:center;"><div class="loader"></div></div>
            <div id="payment-ui-mem">
                <?php if($my_status !== 'verified'): ?>
                    <button type="button" onclick="runPaymentMember()" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Pay Now</button>
                <?php else: ?>
                    <span style="color:#28a745; font-weight:bold;">✅ Paid</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <form id="hidden-pay-form-mem" method="POST" style="display:none;"><input type="hidden" name="process_fake_payment" value="1"></form>

    <div style="display:flex; gap:20px; margin-top:20px;">
        <div style="border:1px solid #ccc; padding:10px; width:220px; height:400px; overflow-y:auto;">
            <h4>Trip Members</h4>
            <ul style="list-style:none; padding:0;">
                <?php foreach($members as $m): 
                    $pay_st = $conn->query("SELECT status FROM trip_payments WHERE trip_id=$trip_id AND user_id=".$m['uid']);
                    $is_p = ($pay_st->num_rows > 0);
                ?>
                    <li style="margin-bottom:5px;"><?= $is_p ? '✅ ' : '⏳ ' ?><?= htmlspecialchars($m['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div style="flex:1; border:1px solid #ccc; padding:10px; height:400px; display:flex; flex-direction:column;">
            <div id="chatBox" style="flex:1; overflow-y:auto; margin-bottom:10px;">
                <?php
                    $messages = $conn->prepare("SELECT tm.message, u.name FROM trip_messages tm JOIN users u ON tm.user_id = u.id WHERE tm.trip_id=? ORDER BY tm.created_at ASC");
                    $messages->bind_param("i", $trip_id); $messages->execute(); $res = $messages->get_result();
                    while($msg = $res->fetch_assoc()):
                ?>
                    <p><strong><?= htmlspecialchars($msg['name']) ?>:</strong> <?= htmlspecialchars($msg['message']) ?></p>
                <?php endwhile; ?>
            </div>
            <form method="POST" style="display:flex; gap:5px;">
                <input type="text" name="new_message" placeholder="Type your message" required style="flex:1;">
                <button type="submit" name="send_message">Send</button>
            </form>
        </div>
    </div>
<?php endif; ?>
<?php endif; endif; ?>

<script>
function runPayment() {
    document.getElementById('payment-ui').style.display = 'none';
    document.getElementById('loader-display').style.display = 'block';
    setTimeout(() => { document.getElementById('hidden-pay-form').submit(); }, 3000);
}
function runPaymentMember() {
    document.getElementById('payment-ui-mem').style.display = 'none';
    document.getElementById('loader-display-mem').style.display = 'block';
    setTimeout(() => { document.getElementById('hidden-pay-form-mem').submit(); }, 3000);
}
</script>
</body>
</html>
<?php
$current_file = basename($_SERVER['PHP_SELF']);
if ($current_file !== 'landing.php') { include 'popup.php'; }
?>