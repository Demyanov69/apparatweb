<!DOCTYPE html>
<html>
<head>
    <title>Форма</title>
    <link rel="stylesheet" href="styles.css">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self'; script-src 'self' 'unsafe-inline';">
</head>
<body>
<div class="container">
    <h1>Регистрационная форма</h1>

    <?php
    if (isset($_SESSION['show_credentials']) && $_SESSION['show_credentials']) {
        echo '<div style="color: green;">Ваш логин:<br>' . htmlspecialchars($_SESSION['new_login']) . '<br>пароль:<br>' . htmlspecialchars($_SESSION['new_password']) . '<br><br> <b>Запишите эти данные, после перехода на другую страницу вы их больше не увидите!</b></div>';
        unset($_SESSION['new_login']);
        unset($_SESSION['new_password']);
        unset($_SESSION['show_credentials']);
    }
    ?>
    <?php
    if (!empty($_SESSION['login'])) {
        echo "<p>Вы вошли как: " . htmlspecialchars($_SESSION['login']) . " <a href='logout.php'>Выйти</a></p>";
    } else {
        echo "<p><a href='login.php'>Войти</a></p>";
    }
    ?>
    <p><a href="admin.php">Страница администратора</a></p>

    <?php
    if (!empty($messages)) {
        echo '<div id="messages" style="color: red;">';
        foreach ($messages as $message) {
            echo htmlspecialchars($message) . '<br>';
        }
        echo '</div>';
    }
    ?>

    <form method="post" action="index.php">
        <label for="fio">ФИО:</label>
        <input type="text" id="fio" name="fio"
               value="<?php echo htmlspecialchars($values['fio'] ?? ''); ?>"
            <?php if (!empty($errors['fio'])): ?>class="error-input"<?php endif; ?>>
        <?php if (!empty($errors['fio'])): ?>
            <div class="error"><?php echo $errors['fio'] ?></div>
        <?php endif; ?>

        <label for="phone">Телефон:</label>
        <input type="tel" id="phone" name="phone"
               value="<?php echo htmlspecialchars($values['phone'] ?? ''); ?>"
            <?php if (!empty($errors['phone'])): ?>class="error-input"<?php endif; ?>>
        <?php if (!empty($errors['phone'])): ?>
            <div class="error"><?php echo $errors['phone'] ?></div>
        <?php endif; ?>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email"
               value="<?php echo htmlspecialchars($values['email'] ?? ''); ?>"
            <?php if (!empty($errors['email'])): ?>class="error-input"<?php endif; ?>>
        <?php if (!empty($errors['email'])): ?>
            <div class="error"><?php echo $errors['email'] ?></div>
        <?php endif; ?>

        <label for="birthdate">Дата рождения:</label>
        <input type="date" id="birthdate" name="birthdate"
               value="<?php echo htmlspecialchars($values['birthdate'] ?? ''); ?>"
            <?php if (!empty($errors['birthdate'])): ?>class="error-input"<?php endif; ?>>
        <?php if (!empty($errors['birthdate'])): ?>
            <div class="error"><?php echo $errors['birthdate'] ?></div>
        <?php endif; ?>

        <div class="radio-group">
            <label>Пол:</label>
            <label><input type="radio" name="gender" value="male" <?php if (isset($values['gender']) && $values['gender'] == 'male') {
                        echo 'checked';
                    } ?>>Мужской</label>
            <label><input type="radio" name="gender" value="female" <?php if (isset($values['gender']) && $values['gender'] == 'female') {
                        echo 'checked';
                    } ?>>Женский</label>
        </div>
        <?php if (!empty($errors['gender'])): ?>
            <div class="error"><?php echo $errors['gender'] ?></div>
        <?php endif; ?>

        <label for="languages">Любимый язык программирования:</label>
        <div class="select-container">
            <select id="languages" name="languages[]" multiple style="width:100%;"
                <?php if (!empty($errors['languages'])): ?>class="error-input"<?php endif; ?>>
                <option value="1" <?php if (isset($values['languages']) && in_array(1, $values['languages'])) {
                    echo "selected";
                } ?>>Pascal
                </option>
                <option value="2" <?php if (isset($values['languages']) && in_array(2, $values['languages'])) {
                    echo "selected";
                } ?>>C
                </option>
                <option value="3" <?php if (isset($values['languages']) && in_array(3, $values['languages'])) {
                    echo "selected";
                } ?>>C++
                </option>
                <option value="4" <?php if (isset($values['languages']) && in_array(4, $values['languages'])) {
                    echo "selected";
                } ?>>JavaScript
                </option>
                <option value="5" <?php if (isset($values['languages']) && in_array(5, $values['languages'])) {
                    echo "selected";
                } ?>>PHP
                </option>
                <option value="6" <?php if (isset($values['languages']) && in_array(6, $values['languages'])) {
                    echo "selected";
                } ?>>Python
                </option>
                <option value="7" <?php if (isset($values['languages']) && in_array(7, $values['languages'])) {
                    echo "selected";
                } ?>>Java
                </option>
                <option value="8" <?php if (isset($values['languages']) && in_array(8, $values['languages'])) {
                    echo "selected";
                } ?>>Haskell
                </option>
                <option value="9" <?php if (isset($values['languages']) && in_array(9, $values['languages'])) {
                    echo "selected";
                } ?>>Clojure
                </option>
                <option value="10" <?php if (isset($values['languages']) && in_array(10, $values['languages'])) {
                    echo "selected";
                } ?>>Prolog
                </option>
                <option value="11" <?php if (isset($values['languages']) && in_array(11, $values['languages'])) {
                    echo "selected";
                } ?>>Scala
                </option>
                <option value="12" <?php if (isset($values['languages']) && in_array(12, $values['languages'])) {
                    echo "selected";
                } ?>>Go
                </option>
            </select>
        </div>
        <?php if (!empty($errors['languages'])): ?>
            <div class="error"><?php echo $errors['languages'] ?></div>
        <?php endif; ?>

        <label for="bio">Биография:</label>
        <textarea id="bio" name="bio" style="resize: vertical;"
            <?php if (!empty($errors['bio'])): ?>class="error-input"<?php endif; ?>><?php echo htmlspecialchars($values['bio'] ?? ''); ?></textarea>
        <?php if (!empty($errors['bio'])): ?>
            <div class="error"><?php echo $errors['bio'] ?></div>
        <?php endif; ?>

        <div class="checkbox-group">
            <label><input type="checkbox" id="agreement" name="agreement" <?php if (!empty($values['agreement'])) {
                    echo 'checked';
                } ?>>С контрактом ознакомлен(а)</label>
        </div>
        <?php if (!empty($errors['agreement'])): ?>
            <div class="error"><?php echo $errors['agreement'] ?></div>
        <?php endif; ?>

        <button type="submit" id="submit">Сохранить</button>
    </form>
</div>

</body>
</html>
