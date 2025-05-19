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


    $stmt = $conn->prepare("UPDATE requests SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status,  $request_id);
    $stmt->execute();

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
                        <th scope="col">Дата получения услуги</th>
                        <th scope="col">Контактные данные</th>
                        <th scope="col">Количество гостей</th>
                        <th scope="col">Статус</th>
                        <th scope ="col">Действие</th>
                        <th scope ="col">Комментарий</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['date_time']) ?></td>
                            <td><?= htmlspecialchars($row['contact']) ?></td>
                            <td>
                                <?= htmlspecialchars($row['count_guests']) ?>
                                <?php if (!empty($row['reason'])): ?>
                                    <small class="text-muted d-block"><?= htmlspecialchars($row['reason']) ?></small>
                                <?php endif; ?>
                            </td>
                            <form method="post" class="status-form">
                                <td>

                                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                    <select name="status" class="form-select status-select ">
                                        <option value="Новая" <?= $row['status'] === 'Новая' ? 'selected' : '' ?>>Новая</option>
                                        <option value="Посещение состоялось" <?= $row['status'] === 'Посещение состоялось' ? 'selected' : '' ?>>Посещение состоялось</option>
                                        <option value="Отменено" <?= $row['status'] === 'Отменено' ? 'selected' : '' ?>>Отменено</option>
                                    </select>
                                    <input type="text" name="reason" placeholder="Укажите причину"
                                           class="form-control reason-input"
                                           value="<?= htmlspecialchars($row['reason'] ?? '') ?>">

                                </td>
                                <td>
                                    <button type="submit" class="btn btn-success btn-sm">Обновить</button>
                                </td>
                            </form>
                            <td>
                                <?php if(htmlspecialchars($row['comment'])):?>
                                    <?= htmlspecialchars($row['comment']) ?>
                                <?php else: ?>
                                    Отсутствует
                                <?php endif; ?>
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

</body>
</html>