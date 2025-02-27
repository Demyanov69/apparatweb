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

$session_started = false;
if (!empty($_COOKIE[session_name()]) && session_start()) {
    $session_started = true;
    if (!empty($_SESSION['login'])) {
        header('Location: ./');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Вход</title>
    </head>
    <body>

    <form action="" method="post">
        <label for="login">Логин:</label>
        <input type="text" id="login" name="login" required /><br><br>
        <label for="pass">Пароль:</label>
        <input type="password" id="pass" name="pass" required /><br><br>
        <input type="submit" value="Войти" />
    </form>

    </body>
    </html>
    <?php
} else {
    $login = $_POST['login'];
    $pass = $_POST['pass'];

    try {
        $stmt = $db->prepare("SELECT id, password FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($pass, $user['password'])) {

            if (!$session_started) {
                session_start();
            }
            $_SESSION['login'] = $login;
            $_SESSION['uid'] = $user['id'];

            header('Location: ./');
            exit();
        } else {
            $error = "Неверный логин или пароль.";
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Вход</title>
            </head>
            <body>
            <form action="" method="post">
                <label for="login">Логин:</label>
                <input type="text" id="login" name="login" required value="<?php echo htmlspecialchars($login); ?>" /><br><br>
                <label for="pass">Пароль:</label>
                <input type="password" id="pass" name="pass" required /><br><br>
                <input type="submit" value="Войти" />
                <div style='color: red;'><?php echo $error; ?></div>
            </form>
            </body>
            </html>
            <?php
            exit();
        }
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}
?>