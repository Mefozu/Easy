<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $status = $_POST['status'];
    $reason = trim($_POST['reason'] ?? '');

    // Проверка: если статус "отменено", то должна быть указана причина
    if ($status === 'Отменено' && empty($reason)) {
        $error = 'Необходимо указать причину отмены.';
    } else {
        $stmt = $conn->prepare("UPDATE requests SET status = ?, reason = ? WHERE id = ?");
        $stmt->bind_param("ssi", $status, $reason, $request_id);
        $stmt->execute();
    }
}

// Получаем список всех заявок с именами пользователей
$result = $conn->query("SELECT r.*, u.name FROM requests r JOIN users u ON r.user_id = u.id");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <!-- Подключение Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .admin-card {
            max-width: 960px;
            margin-top: 40px;
            margin-left: auto;
            margin-right: auto;
        }

        .reason-input {
            display: none;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            margin-top: 0.25rem;
        }

        .reason-input.show {
            display: block;
        }

        table img {
            max-width: 40px;
            border-radius: 50%;
        }
    </style>
</head>
<body>

<div class="container admin-card">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title mb-0">Панель администратора</h4>
        </div>
        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                    <tr>
                        <th scope="col">Клиент</th>
                        <th scope="col">Тип услуги</th>
                        <th scope="col">Статус</th>
                        <th scope="col">Действие</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= htmlspecialchars($row['service_type']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['status']) ?>
                                <?php if (!empty($row['reason'])): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($row['reason']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form method="post" class="status-form">
                                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select status-select mb-2">
                                        <option value="новая" <?= $row['status'] === 'новая' ? 'selected' : '' ?>>новая</option>
                                        <option value="в работе" <?= $row['status'] === 'в работе' ? 'selected' : '' ?>>в работе</option>
                                        <option value="выполнено" <?= $row['status'] === 'выполнено' ? 'selected' : '' ?>>выполнено</option>
                                        <option value="отменено" <?= $row['status'] === 'отменено' ? 'selected' : '' ?>>отменено</option>
                                    </select>
                                    <input type="text" name="reason" placeholder="Укажите причину"
                                           class="form-control reason-input"
                                           value="<?= htmlspecialchars($row['reason'] ?? '') ?>">
                                    <button type="submit" class="btn btn-success btn-sm mt-2">Обновить</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Кнопка выхода -->
            <div class="d-grid mt-4">
                <a href="logout.php" class="btn btn-danger">Выход</a>
            </div>

        </div>
    </div>
</div>

<!-- JS Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS логика для формы -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.status-form').forEach(form => {
            const select = form.querySelector('.status-select');
            const input = form.querySelector('.reason-input');

            form.addEventListener('submit', function (e) {
                if (select.value === 'отменено' && input.value.trim() === '') {
                    e.preventDefault(); // Останавливаем отправку
                    alert('Пожалуйста, укажите причину отмены.');
                }
            });

            // Отображение/скрытие поля причины
            select.addEventListener('change', function () {
                if (this.value === 'отменено') {
                    input.classList.add('show');
                    input.focus();
                } else {
                    input.classList.remove('show');
                    input.value = '';
                }
            });
        });
    });
</script>
</body>
</html>