<?php
session_start();
require_once "conn.php";

if(!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD']!='POST') exit;

if(isset($_POST['notif_id'])){
    $stmt = $pdo->prepare("UPDATE notifications SET is_read=1 WHERE notif_id=:id AND user_id=:uid");
    $stmt->execute(['id'=>$_POST['notif_id'],'uid'=>$_SESSION['user_id']]);
}
