
<?php
require_once "connection.php";

$conn = (new Connection())->connect();

$id = $_POST['id'] ?? null;
$name = $_POST['name'];
$email = $_POST['email'];
$colors = $_POST['colors'] ?? [];

if ($id) {
    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $id]);

    $conn->prepare("DELETE FROM user_colors WHERE user_id = ?")->execute([$id]);
} else {
    $stmt = $conn->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
    $stmt->execute([$name, $email]);
    $id = $conn->lastInsertId();
}

foreach ($colors as $color_id) {
    $conn->prepare("INSERT INTO user_colors (user_id, color_id) VALUES (?, ?)")->execute([$id, $color_id]);
}

header("Location: index.php?success=1");
exit;
