
<?php
require_once "connection.php";
$conn = (new Connection())->connect();

// Paginação
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

// Ordenação
$allowed_sort = ['name', 'email'];
$sort = in_array($_GET['sort'] ?? '', $allowed_sort) ? $_GET['sort'] : 'name';
$order = ($_GET['order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

// Busca e Filtro
$search = $_GET['search'] ?? '';
$color_filter = $_GET['color'] ?? '';

$base_query = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $base_query .= " AND (name LIKE :search OR email LIKE :search)";
    $params[':search'] = "%$search%";
}

$count_stmt = $conn->prepare(str_replace("SELECT *", "SELECT COUNT(*)", $base_query));
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$query = "$base_query ORDER BY $sort $order LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cores relacionadas
$user_colors = [];
foreach ($users as $user) {
    $stmt_colors = $conn->prepare("SELECT colors.name FROM user_colors 
        JOIN colors ON colors.id = user_colors.color_id 
        WHERE user_colors.user_id = ?");
    $stmt_colors->execute([$user['id']]);
    $user_colors[$user['id']] = $stmt_colors->fetchAll(PDO::FETCH_COLUMN);
}

// Lista de cores
$colors_all = $conn->query("SELECT * FROM colors")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <style>
        th a {
            color: white !important;
            text-decoration: none;
        }

        th a:hover {
            text-decoration: underline;
        }

        th a i {
            margin-left: 5px;
            font-size: 0.85em;
        }
    </style>
    <meta charset="UTF-8">
    <title>Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">Lista de Usuários</h2>

    <form method="get" class="row g-2 mb-4">
        <div class="col-md-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Buscar por nome ou e-mail">
        </div>
        <div class="col-md-4">
            <select name="color" class="form-select">
                <option value="">Filtrar por Cor</option>
                <?php foreach ($colors_all as $color): ?>
                    <option value="<?= $color['name'] ?>" <?= $color_filter == $color['name'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($color['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-secondary"><i class="fas fa-filter"></i> Filtrar</button>
            <a href="index.php" class="btn btn-light">Limpar</a>
            <a href="create.php" class="btn btn-primary float-end"><i class="fas fa-plus"></i> Novo Usuário</a>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
        <tr>
            <th>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'name', 'order' => ($sort === 'name' && $order === 'asc') ? 'desc' : 'asc'])) ?>">
                    Nome
                    <?php if ($sort === 'name'): ?>
                        <i class="fas fa-sort-<?= $order === 'asc' ? 'up' : 'down' ?>"></i>
                    <?php endif; ?>
                </a>
            </th>
            <th>
                <a href="?<?= http_build_query(array_merge($_GET, ['sort' => 'email', 'order' => ($sort === 'email' && $order === 'asc') ? 'desc' : 'asc'])) ?>">
                    Email
                    <?php if ($sort === 'email'): ?>
                        <i class="fas fa-sort-<?= $order === 'asc' ? 'up' : 'down' ?>"></i>
                    <?php endif; ?>
                </a>
            </th>
            <th>Cores</th>
            <th>Ação</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <?php
                $has_color = $color_filter === '' || in_array($color_filter, $user_colors[$user['id']]);
                if (!$has_color) continue;
            ?>
            <tr>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                    <?php foreach ($user_colors[$user['id']] as $color): ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($color) ?></span>
                    <?php endforeach; ?>
                </td>
                <td>
                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="delete.php?id=<?= $user['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
</div>
</body>
</html>
