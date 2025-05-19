<?php
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    if (!empty($login) && !empty($password) && !empty($name) && !empty($phone) && !empty($email)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Логин уже занят.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (login, password, name, phone, email) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $login, $password, $name, $phone, $email);
            $stmt->execute();
            header("Location: login.php");
            exit;
        }
    } else {
        $error = "Заполните все поля.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header text-center">
                    <h4>Регистрация</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль (минимум 6 символов)</label>
                            <input type="password" name="password" class="form-control" minlength="6" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ФИО</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Телефон (+7(XXX)-XXX-XX-XX)</label>
                            <input type="text" name="phone" class="form-control" placeholder="+7(XXX)-XXX-XX-XX" pattern="\+7$$([0-9]{3}$$-[0-9]{3}-[0-9]{2}-[0-9]{2}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Зарегистрироваться</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <a href="login.php">Уже есть аккаунт?</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Подключение Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>