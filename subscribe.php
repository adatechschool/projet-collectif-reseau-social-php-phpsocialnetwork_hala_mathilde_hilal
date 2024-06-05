<?php
include("config.php");

if (!isset($_SESSION['connected_id'])) {
    header("Location: login.php");
    exit();
}

$loggedInUserId = intval($_SESSION['connected_id']);
$subscribeToUserId = intval($_POST['subscribe_to_user_id']);

$subscribeQuery = "INSERT INTO followers (following_user_id, followed_user_id) VALUES ('$loggedInUserId', '$subscribeToUserId')";
if ($mysqli->query($subscribeQuery)) {
    header("Location: wall.php?user_id=$subscribeToUserId");
} else {
    echo "Erreur lors de l'abonnement : " . $mysqli->error;
}
?>
