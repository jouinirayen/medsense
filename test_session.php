<?php
session_start();

if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 0;
    echo "Session started. Count initialized to 0.<br>";
} else {
    $_SESSION['count']++;
    echo "Session active. Count: " . $_SESSION['count'] . "<br>";
}

echo "Session ID: " . session_id() . "<br>";
echo '<a href="test_session.php">Reload</a>';
?>