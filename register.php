<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

                // Проверка ФИО (минимум 3 слова)
                $name_parts = preg_split('/\s+/', $full_name);
                $valid_name = count(array_filter($name_parts)) >= 3;
                
                if (!$valid_name) {
                    $errors[] = 'ФИО должно состоять из трёх слов (например: Иванов Иван Иванович)';
                }

                // Валидация обязательных полей
                if (empty($full_name)) $errors[] = 'ФИО обязательно для заполнения';
                if (empty($login)) $errors[] = 'Логин обязателен для заполнения';
                if (empty($phone)) $errors[] = 'Телефон обязателен для заполнения';
                if (empty($email)) $errors[] = 'Email обязателен для заполнения';
                if (empty($password)) $errors[] = 'Пароль обязателен для заполнения';

                // Проверка форматов
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'Некорректный формат email';
                }
                if (strlen($phone) < 11) {
                    $errors[] = 'Номер телефона должен содержать не менее 11 цифр';
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
                        $errors[] = 'Ошибка регистрации: ' . $e->getMessage();
                    }
                }
                
                // Вывод ошибок
                if (!empty($errors)) {
                    echo '<div class="alert alert-danger mb-4">';
                    echo '<strong>Ошибки при заполнении формы:</strong>';
                    echo '<ul class="mb-0 mt-2">';
                    foreach ($errors as $error) {
                        echo '<li>' . $error . '</li>';
                    }
                    echo '</ul>';
                    echo '</div>';
                }
            }
            ?>
            
            <form method="POST" id="registrationForm">
                <!-- Поле ФИО -->
                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               name="full_name" 
                               id="full_name"
                               placeholder="Фамилия Имя Отчество"
                               value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                               required>
                        <span class="input-group-text bg-transparent border-0 position-absolute end-0 top-0 h-100" id="nameIcon"></span>
                    </div>
                </div>
                
                <!-- Поле телефона -->
                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="tel" 
                               class="form-control" 
                               name="phone" 
                               id="phone"
                               placeholder="+7 (___) ___-__-__"
                               value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                               required>
                        <span class="input-group-text bg-transparent border-0 position-absolute end-0 top-0 h-100" id="phoneIcon"></span>
                    </div>
                </div>
                
                <!-- Поле email -->
                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="email" 
                               class="form-control" 
                               name="email" 
                               id="email"
                               placeholder="Email"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               required>
                        <span class="input-group-text bg-transparent border-0 position-absolute end-0 top-0 h-100" id="emailIcon"></span>
                    </div>
                </div>
                
                <!-- Поле логина -->
                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               name="login" 
                               id="login"
                               placeholder="Логин"
                               value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                               required>
                        <span class="input-group-text bg-transparent border-0 position-absolute end-0 top-0 h-100" id="loginIcon"></span>
                    </div>
                </div>
                
                <!-- Поле пароля -->
                <div class="mb-3 position-relative">
                    <div class="input-group">
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               id="password"
                               placeholder="Пароль"
                               required>
                        <span class="input-group-text bg-transparent border-0 position-absolute end-0 top-0 h-100" id="passwordIcon"></span>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
            </form>
            
            <p class="mt-3 text-center text-white-50">
                Уже есть аккаунт? 
                <a href="login.php" class="text-white">Войти</a>
            </p>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Удаление старых сообщений об ошибках перед добавлением новых
        function removeExistingAlerts() {
            $('.alert-danger').remove();
        }

        // Форматирование телефона
        function formatPhone(phone) {
            if (!phone) return '';
            const digits = phone.replace(/\D/g, '');
            let formatted = '+7';
            
            if (digits.length > 1) {
                formatted += ' (' + digits.substring(1, 4);
            }
            if (digits.length > 4) {
                formatted += ') ' + digits.substring(4, 7);
            }
            if (digits.length > 7) {
                formatted += '-' + digits.substring(7, 9);
            }
            if (digits.length > 9) {
                formatted += '-' + digits.substring(9, 11);
            }
            return formatted;
        }

        // Обработчик ввода телефона
        $('#phone').on('input', function() {
            const input = $(this);
            const cursorPos = input[0].selectionStart;
            const oldValue = input.val();
            const digits = oldValue.replace(/\D/g, '');
            
            // Сохраняем позицию курсора
            const isCursorAtEnd = cursorPos === oldValue.length;
            
            // Форматируем только если номер начинается с 7 или 8
            if (digits.length === 0 || digits[0] === '7' || digits[0] === '8') {
                input.val(formatPhone(digits));
            } else if (digits.length > 0) {
                input.val('+7' + digits.substring(digits[0] === '7' || digits[0] === '8' ? 1 : 0));
            }
            
            // Восстанавливаем позицию курсора
            if (!isCursorAtEnd) {
                input[0].setSelectionRange(cursorPos, cursorPos);
            }
            
            // Обновляем иконки
            updateValidationIcons();
        });

        // Функция для обновления иконок
        function updateValidationIcons() {
            // Валидация ФИО
            const name = $('#full_name').val().trim();
            const nameParts = name.split(/\s+/).filter(Boolean);
            const nameValid = nameParts.length >= 3;
            updateIcon('nameIcon', nameValid, name.length > 0 ? 'ФИО должно состоять из трёх слов' : 'Обязательное поле');

            // Валидация телефона
            const phone = $('#phone').val().replace(/\D/g, '');
            const phoneValid = phone.length >= 11;
            updateIcon('phoneIcon', phoneValid, phone.length > 0 ? 'Минимум 11 цифр' : 'Обязательное поле');

            // Валидация email
            const email = $('#email').val().trim();
            const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            updateIcon('emailIcon', emailValid, email.length > 0 ? 'Некорректный email' : 'Обязательное поле');

            // Валидация логина
            const login = $('#login').val().trim();
            const loginValid = login.length >= 3;
            updateIcon('loginIcon', loginValid, login.length > 0 ? 'Минимум 3 символа' : 'Обязательное поле');

            // Валидация пароля
            const password = $('#password').val();
            const passwordValid = password.length >= 6;
            updateIcon('passwordIcon', passwordValid, password.length > 0 ? 'Минимум 6 символов' : 'Обязательное поле');
        }

        // Обновление иконки и тултипа
        function updateIcon(iconId, isValid, errorMessage) {
            const $icon = $('#' + iconId);
            $icon.html('');
            
            if ($('#' + iconId.replace('Icon', '')).val().length === 0) {
                $icon.append('<i class="bi bi-circle text-secondary" data-bs-toggle="tooltip" title="Не заполнено"></i>');
            } else if (isValid) {
                $icon.append('<i class="bi bi-check-circle text-success" data-bs-toggle="tooltip" title="Верно"></i>');
            } else {
                $icon.append('<i class="bi bi-exclamation-circle text-danger" data-bs-toggle="tooltip" title="' + errorMessage + '"></i>');
            }
        }

        // Инициализация валидации при вводе
        $('input').on('input', function() {
            // Для телефона используем отдельный обработчик
            if (this.id !== 'phone') {
                updateValidationIcons();
            }
        });
        
        // Валидация формы перед отправкой
        $('#registrationForm').on('submit', function(e) {
            removeExistingAlerts(); // Удаляем старые сообщения
            
            const errors = [];
            const name = $('#full_name').val().trim();
            const nameParts = name.split(/\s+/).filter(Boolean);
            
            if (nameParts.length < 3) {
                errors.push('ФИО должно состоять из трёх слов (например: Иванов Иван Иванович)');
            }
            
            const phone = $('#phone').val().replace(/\D/g, '');
            if (phone.length < 11) {
                errors.push('Номер телефона должен содержать не менее 11 цифр');
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test($('#email').val().trim())) {
                errors.push('Некорректный формат email');
            }
            
            if ($('#login').val().trim().length < 3) {
                errors.push('Логин должен содержать не менее 3 символов');
            }
            
            if ($('#password').val().length < 6) {
                errors.push('Пароль должен содержать не менее 6 символов');
            }
            
            if (errors.length > 0) {
                e.preventDefault();
                
                // Форматируем ошибки для вывода
                let errorHtml = '<div class="alert alert-danger mb-4">';
                errorHtml += '<strong>Ошибки при заполнении формы:</strong>';
                errorHtml += '<ul class="mb-0 mt-2">';
                errors.forEach(error => {
                    errorHtml += '<li>' + error + '</li>';
                });
                errorHtml += '</ul>';
                errorHtml += '</div>';
                
                // Вставляем перед формой
                $(this).before(errorHtml);
                
                // Прокручиваем к ошибкам
                $('html, body').animate({
                    scrollTop: $('.alert-danger').offset().top - 100
                }, 500);
            }
        });
        
        // Инициализация тултипов
        $('[data-bs-toggle="tooltip"]').tooltip({
            trigger: 'hover focus'
        });
        
        // Первоначальная инициализация иконок
        updateValidationIcons();
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>