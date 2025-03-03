<?php
header('Content-Type: text/html; charset=UTF-8');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self'; font-src 'self'; form-action 'self'; base-uri 'self';");
require_once('db.php');

session_start();

function authenticate()
{
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Area"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}

if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    authenticate();
}

$admin_login = $_SERVER['PHP_AUTH_USER'];
$admin_password = $_SERVER['PHP_AUTH_PW'];

try {
    $db = get_db_connection();

    $stmt = $db->prepare("SELECT id, password FROM admins WHERE login = ?");
    $stmt->execute([$admin_login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) {
        authenticate();
    }

    if (!password_verify($admin_password, $admin['password'])) {
        authenticate();
    }

    echo('Вы успешно авторизовались и видите защищенные паролем данные.<br>');

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $csrf_token = $_SESSION['csrf_token'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $csrf_token) {
            die('CSRF token validation failed.');
        }

        if (isset($_POST['action']) && $_POST['action'] == 'delete' && isset($_POST['id']) && is_numeric($_POST['id'])) {
            $id_to_delete = intval($_POST['id']);
            try {
                delete_application($db, $id_to_delete);
                echo('<p style="color: green;">Запись успешно удалена.</p>');
            } catch (PDOException $e) {
                error_log("Ошибка при удалении записи: " . $e->getMessage());
                echo('<p style="color: red;">Ошибка при удалении записи.</p>');
            }
        }


        if (isset($_POST['action']) && $_POST['action'] == 'edit' && isset($_POST['id']) && is_numeric($_POST['id'])) {

            $id_to_edit = intval($_POST['id']);

            $validation_errors = [];

            $fio = isset($_POST['fio']) ? trim(htmlspecialchars($_POST['fio'])) : '';
            if (!preg_match("/^[a-zA-Zа-яА-Я\s-]+$/u", $fio)) {
                $validation_errors['fio'] = 'ФИО должно содержать только буквы, пробелы и дефисы.';
            }

            $phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'])) : '';
            if (!preg_match("/^[0-9\-\(\)\+]+$/", $phone)) {
                $validation_errors['phone'] = 'Телефон должен содержать только цифры, скобки, дефисы и знаки "+".';
            }

            $email = isset($_POST['email']) ? trim(htmlspecialchars($_POST['email'])) : '';
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validation_errors['email'] = 'Некорректный email.';
            }

            $birthdate = isset($_POST['birthdate']) ? trim(htmlspecialchars($_POST['birthdate'])) : '';
            try {
                if (!empty($birthdate)) {
                    $birthdate_obj = new DateTime($birthdate);
                    $birthdate = $birthdate_obj->format('Y-m-d');
                }
            } catch (Exception $e) {
                $validation_errors['birthdate'] = 'Некорректная дата рождения.';
                $birthdate = '';
            }

            $gender = isset($_POST['gender']) ? trim(htmlspecialchars($_POST['gender'])) : '';
            if (empty($gender)) {
                $validation_errors['gender'] = 'Укажите пол.';
            }

            $bio = isset($_POST['bio']) ? trim(htmlspecialchars($_POST['bio'])) : '';
            if (strlen($bio) > 1000) {
                $validation_errors['bio'] = 'Биография слишком длинная (максимум 1000 символов).';
            }

            $agreement = isset($_POST['agreement']) ? intval($_POST['agreement']) : 0;

            $languages = isset($_POST['languages']) && is_array($_POST['languages']) ? array_map('intval', $_POST['languages']) : [];


            if (empty($validation_errors)) {
                try {
                    update_application($db, $id_to_edit, $fio, $phone, $email, $birthdate, $gender, $bio, $agreement, $languages);
                    echo('<p style="color: green;">Запись успешно обновлена.</p>');
                } catch (PDOException $e) {
                    error_log("Ошибка при обновлении записи: " . $e->getMessage());
                    echo('<p style="color: red;">Ошибка при обновлении записи.</p>');
                }
            } else {
                echo('<p style="color: red;">Обнаружены ошибки валидации:</p>');
                echo('<ul>');
                foreach ($validation_errors as $field => $error) {
                    echo('<li>' . htmlspecialchars($error) . '</li>');
                }
                echo('</ul>');
            }

        }
    }

    $applications = get_all_applications($db);

    echo('<h2>Данные пользователей:</h2>');
    if (!empty($applications)) {
        echo('<table border="1">');
        echo('<tr><th>ID</th><th>ФИО</th><th>Телефон</th><th>Email</th><th>Дата рождения</th><th>Пол</th><th>Биография</th><th>Согласие</th><th>Языки</th><th>Действия</th></tr>');
        foreach ($applications as $app) {
            $languages = get_application_languages($db, $app['id']);
            $language_names = [];
            foreach ($languages as $lang_id) {
                $language_names[] = get_language_name($db, $lang_id);
            }
            $language_string = implode(', ', $language_names);

            echo('<tr>');
            echo('<td>' . htmlspecialchars($app['id']) . '</td>');
            echo('<td>' . htmlspecialchars($app['fio']) . '</td>');
            echo('<td>' . htmlspecialchars($app['phone']) . '</td>');
            echo('<td>' . htmlspecialchars($app['email']) . '</td>');
            echo('<td>' . htmlspecialchars($app['birthdate']) . '</td>');
            echo('<td>' . htmlspecialchars($app['gender']) . '</td>');
            echo('<td>' . htmlspecialchars($app['bio']) . '</td>');
            echo('<td>' . htmlspecialchars($app['agreement']) . '</td>');
            echo('<td>' . htmlspecialchars($language_string) . '</td>');
            echo('<td>
                <form method="post" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="' . htmlspecialchars($app['id']) . '">
                    <input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token) . '">
                    <button type="submit">Удалить</button>
                </form>
                <button onclick="showEditForm(' . htmlspecialchars($app['id']) . ')">Редактировать</button>
              </td>');
            echo('</tr>');

            echo('<tr id="editForm_' . htmlspecialchars($app['id']) . '" style="display: none;">');
            echo('<td colspan="10">');
            echo('<h3>Редактирование записи ID: ' . htmlspecialchars($app['id']) . '</h3>');
            echo('<form method="post">');
            echo('<input type="hidden" name="action" value="edit">');
            echo('<input type="hidden" name="id" value="' . htmlspecialchars($app['id']) . '">');
            echo('<input type="hidden" name="csrf_token" value="' . htmlspecialchars($csrf_token) . '">');

            echo('<label>ФИО: <input type="text" name="fio" value="' . htmlspecialchars($app['fio']) . '"></label><br>');
            echo('<label>Телефон: <input type="text" name="phone" value="' . htmlspecialchars($app['phone']) . '"></label><br>');
            echo('<label>Email: <input type="email" name="email" value="' . htmlspecialchars($app['email']) . '"></label><br>');
            echo('<label>Дата рождения: <input type="date" name="birthdate" value="' . htmlspecialchars($app['birthdate']) . '"></label><br>');
            echo('<label>Пол: <input type="text" name="gender" value="' . htmlspecialchars($app['gender']) . '"></label><br>');
            echo('<label>Биография: <textarea name="bio">' . htmlspecialchars($app['bio']) . '</textarea></label><br>');
            echo('<label>Согласие: <input type="checkbox" name="agreement" value="1" ' . ($app['agreement'] ? 'checked' : '') . '></label><br>');

            echo('<label>Языки программирования:</label><br>');
            $all_languages = get_all_languages($db);
            $selected_languages = get_application_languages($db, $app['id']);

            foreach ($all_languages as $language) {
                $checked = in_array($language['id'], $selected_languages) ? 'checked' : '';
                echo('<label><input type="checkbox" name="languages[]" value="' . htmlspecialchars($language['id']) . '" ' . $checked . '> ' . htmlspecialchars($language['name']) . '</label><br>');
            }

            echo('<button type="submit">Сохранить изменения</button>');
            echo('</form>');
            echo('</td>');
            echo('</tr>');
        }
        echo('</table>');
    } else {
        echo('<p>Нет данных для отображения.</p>');
    }

    $language_stats = get_language_statistics($db);
    if (!empty($language_stats)) {
        echo('<h2>Статистика по языкам программирования:</h2>');
        echo('<ul>');
        foreach ($language_stats as $stat) {
            echo('<li>' . htmlspecialchars($stat['name']) . ': ' . htmlspecialchars($stat['count']) . '</li>');
        }
        echo('</ul>');
    } else {
        echo('<p>Нет статистики по языкам для отображения.</p>');
    }

    echo('<a href="logout.php">Выйти</a>');

} catch (PDOException $e) {
    error_log("Ошибка базы данных: " . $e->getMessage());
    die("Ошибка: Произошла ошибка на сервере.");
}
?>

<script>
function showEditForm(id) {
  var form = document.getElementById('editForm_' + id);
  if (form) {
    form.style.display = 'table-row';
  }
}
</script>
