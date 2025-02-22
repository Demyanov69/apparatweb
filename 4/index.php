<?php
header('Content-Type: text/html; charset=UTF-8');

// Конфигурация базы данных 
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


if ($_SERVER['REQUEST_METHOD'] == 'GET') {


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
    include('form.php');

    exit();
}

// POST обработка:
$errors = [];
$messages = [];

// Валидация ФИО
$fio = isset($_POST['fio']) ? trim($_POST['fio']) : '';
if (empty($fio)) { $errors['fio'] = 'Заполните ФИО.';
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

// Сохранение значений в Cookies
$cookie_expiry_session = time() + 3600; 
setcookie('fio_value', $fio, $cookie_expiry_session, '/');
setcookie('phone_value', $phone, $cookie_expiry_session, '/');
setcookie('email_value', $email, $cookie_expiry_session, '/');
setcookie('birthdate_value', $birthdate, $cookie_expiry_session, '/');
if (isset($gender)) {
    setcookie('gender_value', $gender, $cookie_expiry_session, '/');
}
setcookie('languages_value', implode(',', $languages), time() + 30 * 24 * 60 * 60, '/');
setcookie('bio_value', $bio, $cookie_expiry_session, '/');
if (isset($agreement)) {
    setcookie('agreement_value', $agreement, $cookie_expiry_session, '/');
}

if (!empty($errors)) {
    foreach ($errors as $key => $error) {
        setcookie($key . '_error', $error, $cookie_expiry_session, '/');
    }
    setcookie('save', '', time() - 3600, '/');
    header('Location: index.php');
    exit();
}

try {
    $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birthdate, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $agreement]);

    $application_id = $db->lastInsertId();

   $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $lang_id) {
        $stmt->execute([$application_id, $lang_id]);
    }

} catch (PDOException $e) {
    print ('Error : ' . $e->getMessage());
    exit();
}

// Устанавливаем Cookies на год при успешном заполнении
$cookie_expiry = time() + 365 * 24 * 60 * 60;
setcookie('fio_value', $fio, $cookie_expiry, '/');
setcookie('phone_value', $phone, $cookie_expiry, '/');
setcookie('email_value', $email, $cookie_expiry, '/');
setcookie('birthdate_value', $birthdate, $cookie_expiry, '/');
setcookie('gender_value', $gender, $cookie_expiry, '/');
setcookie('languages_value', implode(',', $languages), $cookie_expiry, '/');
setcookie('bio_value', $bio, $cookie_expiry, '/');
setcookie('agreement_value', $agreement, $cookie_expiry, '/');

setcookie('save', '1', $cookie_expiry, '/');

header('Location: index.php');
exit();
?>
