<?php
session_start();
include 'db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM requests WHERE user_id = $user_id");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <!-- Подключаем Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Контейнер с отступом сверху и центрированием -->
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <!-- Имя пользователя -->
                    <h4 class="card-title text-center mb-4"><?= htmlspecialchars($_SESSION['user_name']) ?></h4>

                    <!-- Заголовок таблицы и кнопка -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Ваши заявки:</h5>
                        <a href="create_request.php" class="btn btn-success btn-sm">Создать заявку</a>
                    </div>

                    <!-- Таблица -->
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Адрес</th>
                                    <th scope="col">Телефон</th>
                                    <th scope="col">Дата получения услуги</th>
                                    <th scope="col">Тип услуги</th>
                                    <th scope="col">Статус</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <th><?= htmlspecialchars($row['id']) ?></th>
                                        <td><?= htmlspecialchars($row['address']) ?></td>
                                        <td><?= htmlspecialchars($row['contact']) ?></td>
                                        <td><?= htmlspecialchars($row['date_time']) ?></td>
                                        <td><?= htmlspecialchars($row['service_type']) ?></td>
                                        <td>
                                            <span><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Заявок пока нет.</p>
                    <?php endif; ?>

                    <!-- Кнопка выхода -->
                    <div class="d-grid mt-4">
                        <a href="logout.php" class="btn btn-danger">Выход</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>