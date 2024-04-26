<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль учителя</title>
    <link rel="stylesheet" type="text/css" href="students_list_styles.css">
</head>
<body>
<h1>Профиль учителя</h1>
<?php
// Подключение к базе данных
$mysqli = new mysqli("localhost", "root", "", "bdcursach");

// Проверка соединения
if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}

// Обработка нажатия кнопки "отчислить ученика"
if(isset($_POST['dismiss_button'])) {
    // Получаем идентификатор студента из скрытого поля
    $student_id = $_POST['student_id'];

    // Получаем выбранный курс
    $selected_course = $_POST['selected_course'];

    // Выполняем запрос для получения списка курсов студента
    $student_courses_query = "SELECT subscribed_courses FROM student WHERE student_id = $student_id";
    $student_courses_result = $mysqli->query($student_courses_query);
    $student_courses_row = $student_courses_result->fetch_assoc();
    $subscribed_courses = $student_courses_row['subscribed_courses'];

    // Разделяем список курсов на массив
    $courses_array = explode(',', $subscribed_courses);

    // Ищем выбранный курс в массиве и удаляем его
    $key = array_search($selected_course, $courses_array);
    if ($key !== false) {
        unset($courses_array[$key]);
    }

    // Обновляем строку subscribed_courses
    $subscribed_courses = implode(',', $courses_array);

    // Выполняем запрос для обновления данных студента
    $unsubscribe_query = "UPDATE student SET subscribed_courses = '$subscribed_courses' WHERE student_id = $student_id";
    if ($mysqli->query($unsubscribe_query) === TRUE) {
        echo "Студент успешно отчислен.";
    } else {
        echo "Ошибка при отчислении студента: " . $mysqli->error;
    }
}

// Выполнение запроса для получения списка курсов
$course_query = "SELECT * FROM course";
$course_result = $mysqli->query($course_query);

// Проверка наличия результатов
if ($course_result->num_rows > 0) {
    // Формирование выпадающего меню для выбора курса
    echo "<form method='get'>";
    echo "<label for='course'>Выберите курс:</label>";
    echo "<select name='course' id='course'>";
    while ($course_row = $course_result->fetch_assoc()) {
        echo "<option value='".$course_row["course_id"]."'>".$course_row["description"]."</option>";
    }
    echo "</select>";
    echo "<input type='submit' value='Показать'>";
    echo "</form>";

    // Получение выбранного курса
    if (isset($_GET['course'])) {
        $selected_course = $_GET['course'];

        // Выполнение запроса для получения учащихся на выбранном курсе
        $student_query = "SELECT student_id, Name, Surname, tel_number, email FROM student WHERE FIND_IN_SET('$selected_course', subscribed_courses)";
        $student_result = $mysqli->query($student_query);

        // Проверка наличия результатов
        if ($student_result->num_rows > 0) {
            // Вывод списка учащихся в виде таблицы
            echo "<h2>Учащиеся на выбранном курсе:</h2>";
            echo "<table border='1'>";
            echo "<tr><th>Имя</th><th>Фамилия</th><th>Телефонный номер</th><th>Email</th><th>Отписать</th></tr>";
            while ($student_row = $student_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>".$student_row["Name"]."</td>";
                echo "<td>".$student_row["Surname"]."</td>";
                echo "<td>".$student_row["tel_number"]."</td>";
                echo "<td>".$student_row["email"]."</td>";
                echo "<td><form method='post'><input type='hidden' name='student_id' value='".$student_row["student_id"]."'><input type='hidden' name='selected_course' value='$selected_course'><input type='submit' name='dismiss_button' value='Отписать'></form></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "На выбранном курсе нет учащихся.";
        }
    }
} else {
    echo "Нет доступных курсов.";
}

// Закрытие соединения
$mysqli->close();
?>
</body> 
</html>
