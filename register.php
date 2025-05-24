<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container mt-5" style="max-width: 600px;">
        <div class="container-form">
            <h2 class="text-white mb-4">Регистрация</h2>
            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $errors = [];
                
                // Получение и очистка данных
                $full_name = trim($_POST['full_name'] ?? '');
                $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
                $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
                $login = trim($_POST['login'] ?? '');
                $password = $_POST['password'] ?? '';

                // Валидация обязательных полей
                if (empty($login)) $errors[] = 'Логин обязателен для заполнения';
                if (empty($phone)) $errors[] = 'Телефон обязателен для заполнения';
                if (empty($email)) $errors[] = 'Email обязателен для заполнения';
                if (empty($password)) $errors[] = 'Пароль обязателен для заполнения';

                // Проверка форматов
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Некорректный формат email';
                }
                if (strlen($phone) < 5) {
                    $errors[] = 'Некорректный формат телефона';
                }

                // Проверка уникальности
                if (empty($errors)) {
                    try {
                        $pdo->beginTransaction();

                        // Проверка существующих записей
                        $stmt = $pdo->prepare("
                            SELECT 
                                SUM(login = ?) AS login_exists,
                                SUM(phone = ?) AS phone_exists,
                                SUM(email = ?) AS email_exists
                            FROM users
                        ");
                        $stmt->execute([$login, $phone, $email]);
                        $result = $stmt->fetch();

                        if ($result['login_exists']) {
                            $errors[] = 'Логин уже занят';
                        }
                        if ($result['phone_exists']) {
                            $errors[] = 'Телефон уже зарегистрирован';
                        }
                        if ($result['email_exists']) {
                            $errors[] = 'Email уже зарегистрирован';
                        }

                        if (empty($errors)) {
                            $hash = password_hash($password, PASSWORD_DEFAULT);
                            $stmt = $pdo->prepare("
                                INSERT INTO users 
                                    (full_name, phone, email, login, password) 
                                VALUES (?, ?, ?, ?, ?)
                            ");
                            $stmt->execute([
                                htmlspecialchars($full_name),
                                $phone,
                                $email,
                                htmlspecialchars($login),
                                $hash
                            ]);
                            $pdo->commit();
                            header("Location: login.php");
                            exit;
                        }
                    } catch (PDOException $e) {
                        $pdo->rollBack();
                        
                        // Обработка ошибки дубликата
                        if ($e->errorInfo[1] == 1062) {
                            $errors[] = 'Ошибка регистрации: данные уже существуют в системе';
                        } else {
                            $errors[] = 'Ошибка базы данных: ' . $e->getMessage();
                        }
                    }
                }
                
                // Вывод ошибок
                foreach ($errors as $error) {
                    echo '<div class="alert alert-danger mb-2">'.$error.'</div>';
                }
            }
            ?>
            <form method="POST">
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="full_name" 
                           placeholder="ФИО"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <input type="tel" 
                           class="form-control" 
                           name="phone" 
                           placeholder="Телефон"
                           value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <input type="email" 
                           class="form-control" 
                           name="email" 
                           placeholder="Email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <input type="text" 
                           class="form-control" 
                           name="login" 
                           placeholder="Логин"
                           value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                           required>
                </div>
                <div class="mb-3">
                    <input type="password" 
                           class="form-control" 
                           name="password" 
                           placeholder="Пароль"
                           required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
            </form>
            <p class="mt-3 text-center text-white-50">
                Уже есть аккаунт? 
                <a href="login.php" class="text-white">Войти</a>
            </p>
        </div>
    </div>
</body>
</html>