
<?php
require_once "connection.php";

$conn = (new Connection())->connect();
$id = $_GET['id'];

$user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);
$data = $user->fetch();

$colors = $conn->query("SELECT * FROM colors")->fetchAll(PDO::FETCH_ASSOC);
$user_colors = $conn->prepare("SELECT color_id FROM user_colors WHERE user_id = ?");
$user_colors->execute([$id]);
$selected = $user_colors->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Editar Usuário</h2>
    <form method="POST" action="save.php">
        <input type="hidden" name="id" value="<?= $data['id'] ?>">
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($data['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($data['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="colors" class="form-label">Cores</label>
            <select class="form-select" name="colors[]" multiple>
                <?php foreach ($colors as $color): ?>
                    <option value="<?= $color['id'] ?>" <?= in_array($color['id'], $selected) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($color['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Salvar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
