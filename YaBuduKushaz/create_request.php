<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Получаем данные из формы
    $contact = $_POST['contact'];
    $date_time = $_POST['date_time'];
    $count_guests = intval($_POST['count_guests']);

    // Проверяем, что количество гостей от 1 до 10
    if ($count_guests < 1 || $count_guests > 10) {
        $error = 'Количество гостей должно быть от 1 до 10.';
    } else {
        // Подготавливаем запрос
        $stmt = $conn->prepare("INSERT INTO requests (user_id, contact, date_time, count_guests, status) VALUES (?, ?, ?, ?, 'Новая')");
        $stmt->bind_param("issi", $_SESSION['user_id'], $contact, $date_time, $count_guests);
        $stmt->execute();

        // Перенаправляем
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Создать заявку</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-card {
            max-width: 600px;
            margin: 40px auto;
        }
    </style>
</head>
<body>

<div class="container form-card">
    <div class="card shadow-sm">
        <div class="card-body p-4">

            <h4 class="card-title text-center mb-4">Заказать стол</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate id="bookingForm">

                <!-- Дата и время -->
                <div class="mb-3">
                    <label for="date_time" class="form-label">Дата и время</label>
                    <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
                    <div class="invalid-feedback">Выберите дату и время.</div>
                </div>

                <!-- Кол-во гостей -->
                <div class="mb-3">
                    <label for="count_guests" class="form-label">Количество гостей</label>
                    <input type="number" class="form-control" id="count_guests" name="count_guests"
                           min="1" max="10" placeholder="От 1 до 10" required>
                    <div class="invalid-feedback">Введите число от 1 до 10.</div>
                </div>

                <!-- Телефон -->
                <div class="mb-3">
                    <label for="contact" class="form-label">Контактный телефон</label>
                    <input type="tel" class="form-control" id="contact" name="contact"
                           placeholder="+7(XXX)-XXX-XX-XX" pattern="\+7$[0-9]{3}$-[0-9]{3}-[0-9]{2}-[0-9]{2}"
                           required>
                    <div class="invalid-feedback">Пожалуйста, укажите корректный номер телефона.</div>
                </div>

                <!-- Кнопка отправки -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Забронировать</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>