<?php
include("config.php");


if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

$loggedInUserId = intval($_SESSION['connected_id']);
$unsubscribeToUserId = intval($_POST['unsubscribe_to_user_id']);

$unsubscribeQuery = "DELETE FROM followers WHERE following_user_id='$loggedInUserId' AND followed_user_id='$unsubscribeToUserId'";
if ($mysqli->query($unsubscribeQuery)) {
    header("Location: wall.php?user_id=$unsubscribeToUserId");
} else {
    echo "Erreur lors de la désinscription : " . $mysqli->error;
}
?>
