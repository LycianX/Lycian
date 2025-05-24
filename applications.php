<?php 
include 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заявления</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">НарушениямНет</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-outline-danger">Выйти</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="container-form mb-5">
            <h2 class="text-white mb-4">Подать новое заявление</h2>
            <form method="POST" action="create_application.php">
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="car_number" 
                           placeholder="Номер автомобиля" 
                           required>
                </div>
                <div class="mb-3">
                    <textarea class="form-control" 
                              name="description" 
                              rows="3"
                              placeholder="Описание нарушения"
                              required></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100">Отправить</button>
            </form>
        </div>

        <div class="container-form">
            <h3 class="text-white mb-4">Мои заявления</h3>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM applications WHERE user_id = ? ORDER BY created_at DESC");
            $stmt->execute([$_SESSION['user_id']]);
            $applications = $stmt->fetchAll();
            
            if (count($applications) > 0): ?>
                <table class="table table-hover table-dark">
                    <thead>
                        <tr>
                            <th>Номер авто</th>
                            <th>Описание</th>
                            <th>Статус</th>
                            <th>Дата подачи</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $row): ?>
                        <tr>
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
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-secondary text-white">
                    У вас пока нет заявлений
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>