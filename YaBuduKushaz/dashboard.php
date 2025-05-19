<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM requests WHERE user_id = $user_id");

// Если была отправка формы (оставление комментария)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $request_id = intval($_POST['request_id']);
    $comment = trim($_POST['comment']);

    // Защита от SQL инъекций
    $stmt = $conn->prepare("UPDATE requests SET comment = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $comment, $request_id, $user_id);
    $stmt->execute();
    $stmt->close();

    // Перезагружаем страницу для обновления данных
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <!-- Bootstrap 5 CDN -->
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
                        <h5 class="mb-0">Ваши брони:</h5>
                        <a href="create_request.php" class="btn btn-success btn-sm">Забронировать стол</a>
                    </div>

                    <!-- Таблица -->
                    <?php if ($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                <tr>
                                    <th scope="col">Дата получения услуги</th>
                                    <th scope="col">Контактные данные</th>
                                    <th scope="col">Количество гостей</th>
                                    <th scope="col">Статус</th>
                                    <th scope="col">Действие</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php while ($row = $result->fetch_assoc()):
                                    $canComment = ($row['status'] === 'Посещение состоялось' && empty($row['comment']));
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['date_time']) ?></td>
                                        <td><?= htmlspecialchars($row['contact']) ?></td>
                                        <td><?= htmlspecialchars($row['count_guests']) ?></td>
                                        <td>
                                            <span><?= htmlspecialchars($row['status']) ?></span>
                                        </td>
                                        <td>
                                            <?php if ($canComment): ?>
                                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#commentModal<?= $row['id'] ?>">
                                                    Оставить комментарий
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Модальное окно для оставления комментария -->
                                    <?php if ($canComment): ?>
                                    <div class="modal fade" id="commentModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="post">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Оставить отзыв</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                                                        <label for="comment" class="form-label">Ваш комментарий:</label>
                                                        <textarea name="comment" class="form-control" rows="4" required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                        <button type="submit" name="submit_comment" class="btn btn-primary">Отправить</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Бронирований нет.</p>
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

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>