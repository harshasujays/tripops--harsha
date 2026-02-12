<?php
session_start();
$conn = new mysqli("localhost","root","","tripops_db");

$trip_id = intval($_POST['trip_id']);
$message = $_POST['message'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO trip_messages (trip_id,user_id,message) VALUES (?,?,?)");
$stmt->bind_param("iis",$trip_id,$user_id,$message);
$stmt->execute();
