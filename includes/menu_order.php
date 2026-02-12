<?php
// Use the authenticated user's email instead of POST email
$email = $_SESSION['user_email']; // Assuming the user's email is stored in the session

// Redirect to myorder.php instead of index.php
header('Location: myorder.php');
exit();
?>