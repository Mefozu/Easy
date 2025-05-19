<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// === ПЕРЕМЕЩЕНИЕ В АРХИВ С ОБНОВЛЕНИЕМ СТАТУСА НА "отменено" ===
if (isset($_GET['archive_id'])) {
    $card_id = intval($_GET['archive_id']);

    // Обновляем is_archived И статус на "отменено"
    $stmt = $conn->prepare("UPDATE cards SET is_archived = 1, status = 'отменено' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $card_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: dashboard.php");
    exit;
}

// Загружаем активные карточки (не архивные и не отменённые)
$active_cards = $conn->query("SELECT * FROM cards WHERE user_id = $user_id AND is_archived = 0 AND status != 'отменено'");

// Загружаем архивные карточки (включая отменённые)
$archived_cards = $conn->query("SELECT * FROM cards WHERE user_id = $user_id AND is_archived = 1");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <!-- Имя пользователя -->
                    <h4 class="card-title text-center mb-4"><?= htmlspecialchars($_SESSION['user_name']) ?></h4>

                    <!-- Кнопка добавления карточки -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Мои карточки:</h5>
                        <a href="create_request.php" class="btn btn-success btn-sm">Добавить карточку</a>
                    </div>

                    <!-- Таблица активных карточек -->
                    <h6>Активные карточки</h6>
                    <?php if ($active_cards && $active_cards->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">Автор</th>
                                        <th scope="col">Название книги</th>
                                        <th scope="col">Примечание</th>
                                        <th scope="col">Статус</th>
                                        <th scope="col">Действие</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $active_cards->fetch_assoc()):
                                        $type = ($row['ready_share']) ? 'Готов поделиться' : (($row['want_library']) ? 'Хочу в библиотеку' : '-');
                                        $is_cancelled = $row['status'] === 'отменено';
                                    ?>
                                        <tr class="<?= $is_cancelled ? 'table-light text-muted' : '' ?>">
                                            <td><?= htmlspecialchars($row['author']) ?></td>
                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                            <td><?= $type ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= htmlspecialchars($row['status']) ?></span>
                                            </td>
                                            <td>
                                                <a href="?archive_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Переместить в архив?')">Удалить</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Нет активных карточек.</p>
                    <?php endif; ?>

                    <!-- Таблица архивных карточек -->
                    <h6 class="mt-4">Архивные карточки</h6>
                    <?php if ($archived_cards && $archived_cards->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle">
                                <thead class="table-secondary">
                                    <tr>
                                        <th scope="col">Автор</th>
                                        <th scope="col">Название книги</th>
                                        <th scope="col">Тип</th>
                                        <th scope="col">Статус</th>
                                        <th scope="col">Причина</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $archived_cards->fetch_assoc()):
                                        $type = ($row['ready_share']) ? 'Готов поделиться' : (($row['want_library']) ? 'Хочу в библиотеку' : '-');
                                    ?>
                                        <tr class="table-light">
                                            <td><?= htmlspecialchars($row['author']) ?></td>
                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                            <td><?= $type ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['status']) ?></span></td>
                                            <?php if (!empty($row['reason'])): ?>
                                    <td><?= htmlspecialchars($row['reason']) ?></td>
                                <?php endif; ?>

                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Нет архивных карточек.</p>
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