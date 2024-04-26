<?php
// Подключение к базе данных
$mysqli = new mysqli("localhost", "root", "", "bdcursach");

// Проверка подключения
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Получение данных из формы
$name = $_POST['name'];
$surname = $_POST['surname'];
$tel_number = $_POST['tel_number'];
$email = $_POST['email'];
$login = $_POST['login'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Хэширование пароля

// Подготовленный запрос для вставки данных
$stmt = $mysqli->prepare("INSERT INTO student (Name, Surname, tel_number, email, login, password) VALUES (?, ?, ?, ?, ?, ?)");

// Проверка успешности подготовки запроса
if ($stmt === false) {
    die("Ошибка подготовки запроса: " . $mysqli->error);
}

// Привязка параметров
$stmt->bind_param("ssssss", $name, $surname, $tel_number, $email, $login, $password);

// Выполнение запроса
if ($stmt->execute()) {
    // Регистрация успешно завершена, перенаправляем на страницу авторизации
    header("Location: login_form.html");
    exit();
} else {
    echo "Ошибка при выполнении запроса: " . $mysqli->error;
}

// Закрытие подключения
$stmt->close();
$mysqli->close();
?>
