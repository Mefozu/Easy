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
    $author = trim($_POST['author']);
    $title = trim($_POST['title']);
    $action = $_POST['action'] ?? null;

    // Проверяем обязательные поля на сервере
    if (empty($author) || empty($title) || !in_array($action, ['library', 'share'])) {
        $error = 'Пожалуйста, заполните все обязательные поля.';
    } else {
        // Определяем значения для want_library и ready_share
        $want_library = ($action === 'library') ? 1 : 0;
        $ready_share = ($action === 'share') ? 1 : 0;

        // Подготавливаем запрос к таблице cards
        $stmt = $conn->prepare("INSERT INTO cards (user_id, author, title, want_library, ready_share, status) VALUES (?, ?, ?, ?, ?, 'новая')");
        $stmt->bind_param("isssi", $_SESSION['user_id'], $author, $title, $want_library, $ready_share);
        $stmt->execute();

        // Перенаправляем после успешного добавления
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить книгу</title>
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

            <h4 class="card-title text-center mb-4">Добавить книгу</h4>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post">

                <!-- Автор книги -->
                <div class="mb-3">
                    <label for="author" class="form-label">Автор книги</label>
                    <input type="text" class="form-control" id="author" name="author" value="<?= htmlspecialchars($_POST['author'] ?? '') ?>">
                </div>

                <!-- Название книги -->
                <div class="mb-3">
                    <label for="title" class="form-label">Название книги</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                </div>

                <!-- Радиокнопки -->
                <div class="mb-3">
                    <label class="form-label">Выберите действие:</label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="action" id="share" value="share"<?= (isset($_POST['action']) && $_POST['action'] === 'share') ? ' checked' : '' ?>>
                        <label class="form-check-label" for="share">Готов поделиться</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="action" id="library" value="library"<?= (isset($_POST['action']) && $_POST['action'] === 'library') ? ' checked' : '' ?>>
                        <label class="form-check-label" for="library">Хочу в свою библиотеку</label>
                    </div>
                </div>

                <!-- Кнопка отправки -->
                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">Добавить</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>