<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_id = intval($_POST['card_id']);
    $status = $_POST['status'];
    $reason = trim($_POST['reason'] ?? '');

    // Получаем текущую запись, чтобы понять, кто её удалил (пользователь или админ)
    $stmt = $conn->prepare("SELECT is_archived FROM cards WHERE id = ?");
    $stmt->bind_param("i", $card_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $card = $result->fetch_assoc();

    $user_cancelled = ($card && $card['is_archived'] == 1 && $status === 'отменено');

    // Проверка: если это администратор помечает как "отменено" — нужна причина
    if (!$user_cancelled && $status === 'отменено' && empty($reason)) {
        $error = 'Необходимо указать причину отмены.';
    } else {
        // Если админ помечает как "отменено", также перемещаем в архив
        if ($status === 'отменено') {
            $stmt_update = $conn->prepare("UPDATE cards SET status = ?, reason = ?, is_archived = 1 WHERE id = ?");
        } else {
            $stmt_update = $conn->prepare("UPDATE cards SET status = ?, reason = ? WHERE id = ?");
        }

        if ($status === 'отменено') {
            $stmt_update->bind_param("ssi", $status, $reason, $card_id);
        } else {
            $stmt_update->bind_param("ssi", $status, $reason, $card_id);
        }

        $stmt_update->execute();
        $stmt_update->close();
        header("Location: admin_panel.php");
        exit;
    }
}

// Получаем все карточки с именами пользователей
$result = $conn->query("
    SELECT c.*, u.name 
    FROM cards c 
    JOIN users u ON c.user_id = u.id
");
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .admin-card { max-width: 1000px; margin: 40px auto; }
        .table th, .table td { vertical-align: middle; }
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
                            <th scope="col">Пользователь</th>
                            <th scope="col">Автор книги</th>
                            <th scope="col">Название книги</th>
                            <th scope="col">Тип карточки</th>
                            <th scope="col">Статус</th>
                            <th scope="col">Причина отмены</th>
                            <th scope="col">Действие</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()):
                            $type = '';
                            if ($row['ready_share']) {
                                $type = 'Готов поделиться';
                            } elseif ($row['want_library']) {
                                $type = 'Хочу в свою библиотеку';
                            }

                            // Определяем, можно ли редактировать поле причины
                            $is_cancelled = $row['status'] === 'отменено';
                            $disabled = $is_cancelled ? '' : 'disabled';
                        ?>
                            <tr>
                                <form method="post">
                                    <input type="hidden" name="card_id" value="<?= $row['id'] ?>">

                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['author']) ?></td>
                                    <td><?= htmlspecialchars($row['title']) ?></td>
                                    <td><?= htmlspecialchars($type) ?></td>

                                    <td>
                                        <select name="status" class="form-select status-select">
                                            <option value="новая" <?= $row['status'] === 'новая' ? 'selected' : '' ?>>Новая</option>
                                            <option value="опубликована" <?= $row['status'] === 'опубликована' ? 'selected' : '' ?>>Опубликована</option>
                                            <option value="отменено" <?= $row['status'] === 'отменено' ? 'selected' : '' ?>>Отменено</option>
                                        </select>
                                    </td>

                                    <td>
                                        <input type="text" name="reason" placeholder="Укажите причину"
                                               class="form-control"
                                               value="<?= htmlspecialchars($row['reason'] ?? '') ?>"
                                               <?= $disabled ?>>
                                    </td>

                                    <td>
                                        <button type="submit" class="btn btn-success btn-sm">Обновить</button>
                                    </td>
                                </form>
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


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('tr').forEach(tr => {
        const select = tr.querySelector('.status-select');
        const input = tr.querySelector('input[name="reason"]');

        if (!select || !input) return;

        const toggle = () => {
            input.disabled = (select.value !== 'отменено');
        };

        toggle(); // применить сразу

        select.addEventListener('change', () => {
            toggle();
        });
        
        // Блокировка без причины
        const form = tr.querySelector('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (select.value === 'отменено' && input.value.trim() === '') {
                    e.preventDefault();
                    alert('Укажите причину отмены.');
                }
            });
        }
    });
});
</script>

</body>
</html>