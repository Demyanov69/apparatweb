<?php

$db_host = 'localhost';
$db_name = 'u68761';
$db_user = 'u68761';
$db_pass = '7216447';

function get_db_connection()
{
    global $db_host, $db_name, $db_user, $db_pass;
    try {
        $db = new PDO(
            "mysql:host=$db_host;dbname=$db_name;charset=utf8",
            $db_user,
            $db_pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_PERSISTENT => true]
        );
        return $db;
    } catch (PDOException $e) {
        die("Ошибка подключения к базе данных: " . $e->getMessage());
    }
}

function get_all_applications(PDO $db)
{
    $stmt = $db->query("SELECT * FROM applications");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function delete_application(PDO $db, $id)
{
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("DELETE FROM applications WHERE id = ?");
        $stmt->execute([$id]);

        $db->commit();
    } catch (PDOException $e) {
        $db->rollBack();
        throw $e; 
    }
}

function update_application(PDO $db, $id, $fio, $phone, $email, $birthdate, $gender, $bio, $agreement, $languages)
{
    try {
        $db->beginTransaction();

        $stmt = $db->prepare("UPDATE applications SET fio = ?, phone = ?, email = ?, birthdate = ?, gender = ?, bio = ?, agreement = ? WHERE id = ?");
        $stmt->execute([$fio, $phone, $email, $birthdate, $gender, $bio, $agreement, $id]);

        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = ?");
        $stmt->execute([$id]);

        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($languages as $lang_id) {
            $stmt->execute([$id, $lang_id]);
        }

        $db->commit();

    } catch (PDOException $e) {
        $db->rollBack();
        throw $e;  
    }
}


function get_language_statistics(PDO $db)
{
    $stmt = $db->query("
        SELECT l.name, COUNT(al.application_id) AS count
        FROM languages l
        LEFT JOIN application_languages al ON l.id = al.language_id
        GROUP BY l.id
        ORDER BY count DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_application_languages(PDO $db, $application_id)
{
    $stmt = $db->prepare("SELECT language_id FROM application_languages WHERE application_id = ?");
    $stmt->execute([$application_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function get_language_name(PDO $db, $language_id)
{
    $stmt = $db->prepare("SELECT name FROM languages WHERE id = ?");
    $stmt->execute([$language_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['name'] : '';
}

function get_all_languages(PDO $db)
{
    $stmt = $db->query("SELECT id, name FROM languages ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>