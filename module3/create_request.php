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
    $address = $_POST['address'];
    $contact = $_POST['contact'];
    $date_time = $_POST['date_time'];
    $service_type = $_POST['service_type'] ?? '';
    $other_service = $_POST['other_service'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';

    // Если выбран "Другая услуга", используем значение из поля
    if ($service_type === 'Другая услуга') {
        $service_type = $other_service;
    }

    // Подготавливаем запрос
    $stmt = $conn->prepare("
        INSERT INTO requests 
        (user_id, address, contact, date_time, service_type, other_service, payment_method, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Новая')
    ");

    // Привязываем параметры
    $stmt->bind_param(
        "issssss",
        $_SESSION['user_id'],
        $address,
        $contact,
        $date_time,
        $service_type,
        $other_service,
        $payment_method
    );

    // Выполняем
    $stmt->execute();

    // Перенаправляем
    header("Location: dashboard.php");
    exit;
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

            <h4 class="card-title text-center mb-4">Создать заявку</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" novalidate>

                <!-- Адрес -->
                <div class="mb-3">
                    <label for="address" class="form-label">Адрес</label>
                    <input type="text" class="form-control" id="address" name="address"
                           placeholder="Введите адрес" required>
                    <div class="invalid-feedback">Пожалуйста, укажите адрес.</div>
                </div>

                <!-- Телефон -->
                <div class="mb-3">
                    <label for="contact" class="form-label">Контактный телефон</label>
                    <input type="tel" class="form-control" id="contact" name="contact"
                           placeholder="+7(XXX)-XXX-XX-XX" pattern="\+7$[0-9]{3}$-[0-9]{3}-[0-9]{2}-[0-9]{2}"
                           required>
                    <div class="invalid-feedback">Пожалуйста, укажите корректный номер телефона.</div>
                </div>

                <!-- Дата и время -->
                <div class="mb-3">
                    <label for="date_time" class="form-label">Дата и время</label>
                    <input type="datetime-local" class="form-control" id="date_time" name="date_time" required>
                    <div class="invalid-feedback">Выберите дату и время.</div>
                </div>

                <!-- Тип услуги -->
                <div class="mb-3">
                    <label for="serviceTypeSelect" class="form-label">Тип услуги</label>
                    <select class="form-select" name="service_type" id="serviceTypeSelect" required>
                        <option value="">-- Выберите тип услуги --</option>
                        <option value="Общий клининг">Общий клининг</option>
                        <option value="Генеральная уборка">Генеральная уборка</option>
                        <option value="Послестроительная уборка">Послестроительная уборка</option>
                        <option value="Химчистка ковров и мебели">Химчистка ковров и мебели</option>
                        <option value="Другая услуга">Другая услуга</option>
                    </select>
                    <div class="invalid-feedback">Выберите тип услуги.</div>
                </div>

                <!-- Поле для "Другой услуги" -->
                <div class="mb-3 d-none" id="otherServiceInput">
                    <label for="otherInput" class="form-label">Укажите другую услугу</label>
                    <input type="text" class="form-control" id="otherInput" name="other_service"
                           placeholder="Введите тип услуги">
                </div>

                <!-- Способ оплаты -->
                <div class="mb-3">
                    <label class="form-label">Способ оплаты</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="cash" value="Наличные" checked>
                        <label class="form-check-label" for="cash">Наличные</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="payment_method"
                               id="card" value="Банковская карта">
                        <label class="form-check-label" for="card">Банковская карта</label>
                    </div>
                </div>

                <!-- Кнопка отправки -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Отправить заявку</button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const select = document.getElementById('serviceTypeSelect');
    const otherInputDiv = document.getElementById('otherServiceInput');
    const otherInputField = document.getElementById('otherInput');

    select.addEventListener('change', function () {
        if (this.value === 'Другая услуга') {
            otherInputDiv.classList.remove('d-none');
            otherInputField.setAttribute('required', true);
            otherInputField.focus();
        } else {
            otherInputDiv.classList.add('d-none');
            otherInputField.removeAttribute('required');
        }
    });
</script>

</body>
</html>