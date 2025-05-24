<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <div class="container-form">
            <h2 class="mb-4 text-white">Вход в систему</h2>
            
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($_POST['login'] === 'adm' && $_POST['password'] === 'adm') {
                    $_SESSION['admin'] = true;
                    header("Location: admin.php");
                    exit;
                }
                
                $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
                $stmt->execute([$_POST['login']]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($_POST['password'], $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    header("Location: applications.php");
                    exit;
                } else {
                    echo '<div class="alert alert-danger">Неверный логин или пароль</div>';
                }
            }
            ?>
            
            <form method="POST">
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="login" 
                           placeholder="Логин"
                           required>
                </div>
                <div class="mb-3">
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           placeholder="Пароль"
                           required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Войти</button>
            </form>
            
            <div class="mt-3 text-center">
                <a href="register.php" class="text-white">Создать новый аккаунт</a>
            </div>
        </div>
    </div>
</body>
</html>