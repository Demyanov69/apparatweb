<?php
header('Content-Type: text/html; charset=UTF-8');

$db_host = 'localhost';
$db_name = 'u68761';
$db_user = 'u68761';
$db_pass = '7216447';

try {
    $db = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true]
    );
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

session_start();

if (isset($_SESSION['login'])) {
    header('Location: ./');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = $_POST['login'];
    $pass = $_POST['pass'];

    try {
        $stmt = $db->prepare("SELECT id, password FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];
            header('Location: ./');
            exit();
        } else {
            $error = "Неверный логин или пароль.";
        }
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Вход</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .error {
            color: red;
        }
    </style>
</head>
<body>

<h2>Вход</h2>

<?php if ($error): ?>
    <p class="error"><?php echo $error; ?></p>
<?php endif; ?>

<form method="post">
    <label for="login">Логин:</label><br>
    <input type="text" id="login" name="login" required><br><br>

    <label for="pass">Пароль:</label><br>
    <input type="password" id="pass" name="pass" required><br><br>

    <button type="submit">Войти</button>
</form>

</body>
</html>