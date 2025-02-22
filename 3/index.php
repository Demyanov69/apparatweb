<?php

// index.php
header('Content-Type: text/html; charset=UTF-8');

// Конфигурация базы данных
$db_host = 'localhost';
$db_name = 'u68761'; 
$db_user = 'u68761';   
$db_pass = '7216447';   
try {
    $db = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true]);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }
    include('form.php');
    exit();
}

// POST обработка:

$errors = FALSE;

// Валидация ФИО
if (empty($_POST['fio'])) {
    print('Заполните ФИО.<br/>');
    $errors = TRUE;
} else {
    $fio = trim($_POST['fio']);
    if (!preg_match("/^[a-zA-Zа-яА-Я\s]+$/u", $fio)) {
        print('ФИО должно содержать только буквы и пробелы.<br/>');
        $errors = TRUE;
    }
}

// Валидация телефона
if (empty($_POST['phone'])) {
    print('Заполните телефон.<br/>');
    $errors = TRUE;
} else {
    $phone = trim($_POST['phone']);
    if (!preg_match("/^\d+$/", $phone)) {
        print('Телефон должен содержать только цифры.<br/>');
        $errors = TRUE;
    }
}

// Валидация email
if (empty($_POST['email'])) {
    print('Заполните email.<br/>');
    $errors = TRUE;
} else {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        print('Некорректный email.<br/>');
        $errors = TRUE;
    }
}

// Валидация даты рождения (простая проверка, можно улучшить)
$birthdate = null; 
if (!empty($_POST['birthdate'])) {
    $birthdate = trim($_POST['birthdate']);
    try {
        $birthdate_obj = new DateTime($birthdate);
        $birthdate = $birthdate_obj->format('Y-m-d'); 
    } catch (Exception $e) {
        print('Некорректная дата рождения.<br/>');
        $errors = TRUE;
    }
}

// Валидация пола
if (empty($_POST['gender'])) {
    print('Укажите пол.<br/>');
    $errors = TRUE;
} else {
    $gender = trim($_POST['gender']);
    if (!in_array($gender, ['male', 'female'])) {
        print('Некорректное значение пола.<br/>');
        $errors = TRUE;
    }
}

// Валидация языков программирования
if (isset($_POST['languages']) && is_array($_POST['languages'])) {
    $languages = $_POST['languages'];
        $placeholders = implode(',', array_fill(0, count($languages), '?')); 
        $stmt = $db->prepare("SELECT id FROM languages WHERE name IN ($placeholders)");
        $stmt->execute($languages);  
        $language_ids = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); 
    
        if (count($language_ids) !== count($languages)) {
            print('Один или несколько языков программирования не найдены в базе данных.<br/>');
            $errors = TRUE;
        }
    } else {
        $languages = []; 
        $language_ids = [];
    }
    
    // Валидация биографии
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : ''; 
    
    // Валидация соглашения
    if (empty($_POST['agreement'])) {
        print('Необходимо согласие с контрактом.<br/>');
        $errors = TRUE;
    } else {
        $agreement = ($_POST['agreement'] == 'on') ? 1 : 0; 
    }
    
    
    if ($errors) {
        exit();
    }
    
    try {
        $stmt = $db->prepare("INSERT INTO applications (fio, phone, email, birthdate, gender, bio, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $agreement]);
    
        $application_id = $db->lastInsertId();
    
        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($language_ids as $lang_id) {
            $stmt->execute([$application_id, $lang_id]);
        }
    
    } catch (PDOException $e) {
        print('Error : ' . $e->getMessage());
        exit();
    }
    
    // Редирект на страницу с сообщением об успехе
    header('Location: index.php?save=1');
    exit();
?>
