<?php
require_once 'config.php';
require_once 'functions.php';

if (!est_connecte() || !isset($_GET['message_id'])) {
    rediriger('index.php');
}

$message_id = (int)$_GET['message_id'];
$user_id = $_SESSION['user_id'];

$conn = connect_db();
$sql = "UPDATE messages SET est_lu = TRUE WHERE id = $message_id AND id_destinataire = $user_id";
mysqli_query($conn, $sql);
mysqli_close($conn);

rediriger('messages.php');
?>
