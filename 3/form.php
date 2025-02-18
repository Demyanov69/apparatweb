<!DOCTYPE html>
<html>
<head>
  <title>Форма</title>
  <style>
    body {
      font-family: sans-serif;
      background-color: #f0f8ff;
    }

    .container {
      width: 600px;
      margin: 30px auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
      color: #008080;
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="tel"],
    input[type="email"],
    input[type="date"],
    textarea,
    select {
      width: 95%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 16px;
    }

    textarea {
      height: 100px;
      overflow: auto;
    }

    .radio-group,
    .checkbox-group {
      margin-bottom: 15px;
    }

    .radio-group label,
    .checkbox-group label {
      display: inline-block;
      margin-right: 15px;
      font-weight: normal;
    }

    button {
      background-color: #008080;
      color: white;
      padding: 12px 20px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      display: block;
      margin: 20px auto;
    }

    button:hover {
      background-color: #006666;
    }

    .error {
      color: red;
    }

    .select-container {
        width: 95%;
        margin-bottom: 15px;
    }

  </style>
</head>
<body>

  <div class="container">
    <h1>Регистрационная форма</h1>

    <form method="post" action="index.php">
      <label for="fio">ФИО:</label>
      <input type="text" id="fio" name="fio" required pattern="[А-Яа-яЁёA-Za-z\s]+" title="ФИО должно содержать только буквы и пробелы">

      <label for="phone">Телефон:</label>
      <input type="tel" id="phone" name="phone" required pattern="\d+" title="Телефон должен содержать только цифры">

      <label for="email">E-mail:</label>
      <input type="email" id="email" name="email" required>

      <label for="birthdate">Дата рождения:</label>
      <input type="date" id="birthdate" name="birthdate">

      <div class="radio-group">
        <label>Пол:</label>
        <label><input type="radio" name="gender" value="male">Мужской</label>
        <label><input type="radio" name="gender" value="female">Женский</label>
      </div>

      <label for="languages">Любимый язык программирования:</label>
      <div class="select-container">
        <select id="languages" name="languages[]" multiple style="width:100%;">
            <option value="Pascal">Pascal</option>
            <option value="C">C</option>
            <option value="C++">C++</option>
            <option value="JavaScript">JavaScript</option>
            <option value="PHP">PHP</option>
            <option value="Python">Python</option>
            <option value="Java">Java</option>
            <option value="Haskell">Haskell</option>
            <option value="Clojure">Clojure</option>
            <option value="Prolog">Prolog</option>
            <option value="Scala">Scala</option>
            <option value="Go">Go</option>
        </select>
      </div>

      <label for="bio">Биография:</label>
      <textarea id="bio" name="bio" style="resize: vertical;"></textarea>

      <div class="checkbox-group">
        <label><input type="checkbox" id="agreement" name="agreement">С контрактом ознакомлен(а)</label>
      </div>

      <button type="submit">Сохранить</button>
    </form>
  </div>

</body>
</html>