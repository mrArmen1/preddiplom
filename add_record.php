<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Подключение к базе данных
    $mysqli = new mysqli("localhost", "root", "", "bdcursach");

    // Проверка подключения
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Получение данных из формы
    $lesson_name = $_POST["lesson_name"];
    $course_id = $_POST["course_id"];
    $url = $_POST["url"];

    // SQL запрос для добавления записи в таблицу video_lessons
    $sql = "INSERT INTO video_lessons (lesson_name, course_id, url) VALUES (?, ?, ?)";

    // Подготовка запроса
    $stmt = $mysqli->prepare($sql);

    // Привязываем параметры
    $stmt->bind_param("sis", $lesson_name, $course_id, $url);

    // Выполняем запрос
    if ($stmt->execute()) {
        // Закрываем запрос
        $stmt->close();
        // Закрываем подключение к БД
        $mysqli->close();
        // Перенаправляем обратно на страницу учителя
        header("Location: teacher_profile.php");
        exit();
    } else {
        // Ошибка добавления записи
        echo "Ошибка добавления записи: " . $stmt->error;
    }
}
?>
