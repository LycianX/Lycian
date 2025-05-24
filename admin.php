<?php 
include 'config.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'], $_POST['app_id'])) {
    $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ? AND status = 'new'");
    $stmt->execute([$_POST['status'], $_POST['app_id']]);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Админ-панель</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="container-form">
            <h2 class="text-white mb-4">Все заявления</h2>
            <table class="table table-hover table-dark">
                <thead>
                    <tr>
                        <th>ФИО</th>
                        <th>Номер авто</th>
                        <th>Описание</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("
                        SELECT a.*, u.full_name 
                        FROM applications a 
                        JOIN users u ON a.user_id = u.id 
                        ORDER BY a.created_at DESC
                    ");
                    while ($row = $stmt->fetch()):
                    ?>
                    <tr>
                        <td class="align-middle"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="align-middle"><?= htmlspecialchars($row['car_number']) ?></td>
                        <td class="align-middle"><?= htmlspecialchars($row['description']) ?></td>
                        <td class="align-middle">
                            <span class="badge <?= 
                                $row['status'] === 'confirmed' ? 'bg-success' : 
                                ($row['status'] === 'rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                <?= match($row['status']) {
                                    'new' => 'Новая',
                                    'confirmed' => 'Подтверждена',
                                    'rejected' => 'Отклонена'
                                } ?>
                            </span>
                        </td>
                        <td class="align-middle"><?= date('d.m.Y H:i', strtotime($row['created_at'])) ?></td>
                        <td class="align-middle">
                            <?php if ($row['status'] === 'new'): ?>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="app_id" value="<?= $row['id'] ?>">
                                <button type="submit" 
                                        name="status" 
                                        value="confirmed" 
                                        class="btn btn-sm btn-success me-1">
                                    ✓ Подтвердить
                                </button>
                                <button type="submit" 
                                        name="status" 
                                        value="rejected" 
                                        class="btn btn-sm btn-danger">
                                    ✕ Отклонить
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>