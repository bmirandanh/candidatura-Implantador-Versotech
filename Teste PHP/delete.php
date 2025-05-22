
<?php
require_once "connection.php";

$conn = (new Connection())->connect();

$id = $_GET['id'];
$conn->prepare("DELETE FROM user_colors WHERE user_id = ?")->execute([$id]);
$conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

header("Location: index.php?success=1");
exit;
