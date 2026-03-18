<?php
// One-time password fix script — DELETE this file after running it
require_once 'includes/config.php';

$newHash = password_hash('admin123', PASSWORD_DEFAULT);

$conn->query("UPDATE users SET password = '$newHash' WHERE username IN ('admin', 'trainer_rahul', 'trainer_priya')");

$affected = $conn->affected_rows;
echo "<h2>Done!</h2>";
echo "<p>Updated <strong>$affected</strong> user(s) to password: <strong>admin123</strong></p>";
echo "<p><a href='index.php'>Go to Login</a></p>";
echo "<p style='color:red'><strong>Delete this file (fix_passwords.php) after use!</strong></p>";
