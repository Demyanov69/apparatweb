<?php
header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self'; font-src 'self'; form-action 'self'; base-uri 'self';");

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
    error_log("Ошибка подключения к базе данных: " . $e->getMessage());
    die("Ошибка подключения к базе данных.");
}

function clearCookies()
{
    $cookie_names = [
        'fio_error',
        'phone_error',
        'email_error',
        'birthdate_error',
        'gender_error',
        'languages_error',
        'agreement_error',
        'bio_error',
        'fio_value',
        'phone_value',
        'email_value',
        'birthdate_value',
        'gender_value',
        'languages_value',
        'bio_value',
        'agreement_value'
    ];

    foreach ($cookie_names as $cookie) {
        setcookie($cookie, '', time() - 3600, '/');
        if (isset($_COOKIE[$cookie])) {
            unset($_COOKIE[$cookie]);
        }
    }
}

$messages = array();
$errors = array();
$values = array();

session_start();

$is_auth = !empty($_SESSION['login']);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if ($is_auth) {
        $user_id = $_SESSION['uid'];
        try {
            $stmt = $db->prepare("SELECT * FROM applications WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $app_data = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($app_data) {
                $values['id'] = $app_data['id'];  
                $values['fio'] = htmlspecialchars($app_data['fio']);
                $values['phone'] = htmlspecialchars($app_data['phone']);
                $values['email'] = htmlspecialchars($app_data['email']);
                $values['birthdate'] = htmlspecialchars($app_data['birthdate']);
                $values['gender'] = htmlspecialchars($app_data['gender']);
                $values['bio'] = htmlspecialchars($app_data['bio']);
                $values['agreement'] = htmlspecialchars($app_data['agreement']);

                $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
                $stmt->execute([$app_data['id']]);
                $lang_data = $stmt->fetchAll(PDO::FETCH_COLUMN);
                $values['languages'] = $lang_data;


            } else {
                $messages[] = "Данные не найдены для вашего аккаунта.";
            }
        } catch (PDOException $e) {
            error_log("Ошибка базы данных: " . $e->getMessage());
            die("Ошибка базы данных.");
        }
    } else {
        if (!empty($_COOKIE['save'])) {
            setcookie('save', '', time() - 3600, '/');
            unset($_COOKIE['save']);
            $messages[] = 'Спасибо, результаты сохранены.';
        }
    
        $errors['fio'] = $_COOKIE['fio_error'] ?? '';
        $errors['phone'] = $_COOKIE['phone_error'] ?? '';
        $errors['email'] = $_COOKIE['email_error'] ?? '';
        $errors['birthdate'] = $_COOKIE['birthdate_error'] ?? '';
        $errors['gender'] = $_COOKIE['gender_error'] ?? '';
        $errors['languages'] = $_COOKIE['languages_error'] ?? '';
        $errors['agreement'] = $_COOKIE['agreement_error'] ?? '';
        $errors['bio'] = $_COOKIE['bio_error'] ?? '';
    
        $values['fio'] = $_COOKIE['fio_value'] ?? '';
        $values['phone'] = $_COOKIE['phone_value'] ?? '';
        $values['email'] = $_COOKIE['email_value'] ?? '';
        $values['birthdate'] = $_COOKIE['birthdate_value'] ?? '';
        $values['gender'] = $_COOKIE['gender_value'] ?? '';
        $values['languages'] = isset($_COOKIE['languages_value']) ? explode(',', $_COOKIE['languages_value']) : [];
        $values['bio'] = $_COOKIE['bio_value'] ?? '';
        $values['agreement'] = $_COOKIE['agreement_value'] ?? '';
    
        clearCookies();
    }
    
    include('form.php');
    exit();
}

$errors = [];
$messages = [];

// Валидация ФИО
$fio = isset($_POST['fio']) ? trim($_POST['fio']) : '';
if (empty($fio)) {
    $errors['fio'] = 'Заполните ФИО.';
} else {
    if (!preg_match("/^[a-zA-Zа-яА-Я\s-]+$/u", $fio)) {
        $errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы.';
    }
}

// Валидация телефона
$phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
if (empty($phone)) {
    $errors['phone'] = 'Заполните телефон.';
} else {
    if (!preg_match("/^[0-9\-\(\)\+]+$/", $phone)) {
        $errors['phone'] = 'Телефон должен содержать только цифры, скобки, дефисы и знаки "+".';
    }
}

// Валидация email
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
if (empty($email)) {
    $errors['email'] = 'Заполните email.';
} else {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Некорректный email.';
    }
}

// Валидация даты рождения
$birthdate = isset($_POST['birthdate']) ? trim($_POST['birthdate']) : '';
if (empty($birthdate)) {
    $errors['birthdate'] = 'Укажите дату рождения.';
} else {
    try {
        $birthdate_obj = new DateTime($birthdate);
        $max_date = new DateTime('2025-02-22');
        if ($birthdate_obj > $max_date) {
            $errors['birthdate'] = 'Дата рождения не может быть позже 22.02.2025.';
        } else {
            $birthdate = $birthdate_obj->format('Y-m-d');
        }
    } catch (Exception $e) {
        $errors['birthdate'] = 'Некорректная дата рождения.';
    }
}

// Валидация пола
if (empty($_POST['gender'])) {
    $errors['gender'] = 'Укажите пол.';
} else {
    $gender = trim($_POST['gender']);
}

// Валидация языков программирования
$languages = isset($_POST['languages']) && is_array($_POST['languages']) ? $_POST['languages'] : [];
if (empty($languages)) {
    $errors['languages'] = 'Выберите хотя бы один язык программирования.';
}

// Валидация биографии
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
if (strlen($bio) > 1000) {
    $errors['bio'] = 'Биография слишком длинная (максимум 1000 символов).';
}

// Валидация соглашения
if (empty($_POST['agreement'])) {
    $errors['agreement'] = 'Необходимо согласие с контрактом.';
} else {
    $agreement = ($_POST['agreement'] == 'on') ? 1 : 0;
}

if (!empty($errors)) {
    if (!$is_auth) {
        $cookie_expiry_session = time() + 3600;
        setcookie('fio_value', $fio, $cookie_expiry_session, '/');
        setcookie('phone_value', $phone, $cookie_expiry_session, '/');
        setcookie('email_value', $email, $cookie_expiry_session, '/');
        setcookie('birthdate_value', $birthdate, $cookie_expiry_session, '/');
        if (isset($gender)) {
            setcookie('gender_value', $gender, $cookie_expiry_session, '/');
        }
        setcookie('languages_value', implode(',', $languages), $cookie_expiry_session, '/');
        setcookie('bio_value', $bio, $cookie_expiry_session, '/');
        if (isset($agreement)) {
            setcookie('agreement_value', $agreement, $cookie_expiry_session, '/');
        }

        foreach ($errors as $key => $error) {
            setcookie($key . '_error', $error, $cookie_expiry_session, '/');
        }
    }
    header('Location: index.php');
    exit();
}

try {
    if ($is_auth) {
        $user_id = $_SESSION['uid'];

        $stmt = $db->prepare("SELECT id FROM applications WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $app_data = $stmt->fetch(PDO::FETCH_ASSOC);
        $application_id = $app_data['id'];

        $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birthdate = ?, gender = ?, bio = ?, agreement = ? WHERE user_id = ?");
        $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $agreement, $user_id]);

        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmt->execute([$application_id]);

        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($languages as $lang_id) {
            $stmt->execute([$application_id, $lang_id]);
        }

        $messages[] = "Данные успешно обновлены.";


    } else {
        $login = uniqid();
        $pass = substr(md5(rand()), 0, 8);

        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);

        $db->beginTransaction();

        try {
            $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
            $stmt->execute([$login, $pass_hash]);
            $user_id = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birthdate, gender, bio, agreement, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $agreement, $user_id]);
            $application_id = $db->lastInsertId();

            $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
            foreach ($languages as $lang_id) {
                $stmt->execute([$application_id, $lang_id]);
            }

            $db->commit();

            $_SESSION['new_login'] = $login;
            $_SESSION['new_password'] = $pass;
            $_SESSION['show_credentials'] = true;

            $cookie_expiry = time() + 365 * 24 * 60 * 60;
            setcookie('fio_value', $fio, $cookie_expiry, '/');
            setcookie('phone_value', $phone, $cookie_expiry, '/');
            setcookie('email_value', $email, $cookie_expiry, '/');
            setcookie('birthdate_value', $birthdate, $cookie_expiry, '/');
            setcookie('gender_value', $gender, $cookie_expiry, '/');
            setcookie('languages_value', implode(',', $languages), $cookie_expiry, '/');
            setcookie('bio_value', $bio, $cookie_expiry, '/');
            setcookie('agreement_value', $agreement, $cookie_expiry, '/');

        } catch (PDOException $e) {
            $db->rollBack();
            error_log("Ошибка при добавлении пользователя: " . $e->getMessage());
            $messages[] = "Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.";
        }
    }

    $cookie_expiry = time() + 365 * 24 * 60 * 60;
    setcookie('fio_value', $fio, $cookie_expiry, '/');
    setcookie('phone_value', $phone, $cookie_expiry, '/');
    setcookie('email_value', $email, $cookie_expiry, '/');
    setcookie('birthdate_value', $birthdate, $cookie_expiry, '/');
    setcookie('gender_value', $gender, $cookie_expiry, '/');
    setcookie('languages_value', implode(',', $languages), $cookie_expiry, '/');
    setcookie('bio_value', $bio, $cookie_expiry, '/');
    setcookie('agreement_value', $agreement, $cookie_expiry, '/');

    setcookie('save', '1', time() + 3600, '/');

} catch (PDOException $e) {
    error_log("Ошибка базы данных: " . $e->getMessage());
    $messages[] = "Произошла ошибка. Пожалуйста, попробуйте позже.";
}

header('Location: index.php');
exit();
?>
