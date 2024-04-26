<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль учителя</title>
    <link rel="stylesheet" type="text/css" href="styles_teacher_profile.css">

</head>
<body>
    <h1>Профиль учителя</h1>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="table">Выберите таблицу:</label>
        <select name="table" id="table">
            <option value="course">Таблица course</option>
            <option value="video_lessons">Таблица video_lessons</option>
        </select>
        <button type="submit">Показать данные</button>
    </form>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $selected_table = $_POST["table"];
        // Подключение к базе данных
        $mysqli = new mysqli("localhost", "root", "", "bdcursach");

        // Проверка подключения
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        // SQL запрос для выбора данных из выбранной таблицы
        $sql = "SELECT * FROM " . $selected_table;
        $result = $mysqli->query($sql);

        if ($result->num_rows > 0) {
            echo "<h2>Данные из таблицы $selected_table:</h2>";
            echo "<table border='1'>";
            // Вывод заголовков таблицы
            echo "<tr>";
            $row = $result->fetch_assoc();
            foreach ($row as $key => $value) {
                echo "<th>$key</th>";
            }
            echo "</tr>";
            // Вывод данных таблицы
            do {
                echo "<tr>";
                foreach ($row as $value) {
                    echo "<td>$value</td>";
                }
                echo "</tr>";
            } while ($row = $result->fetch_assoc());
            echo "</table>";

            // Показываем форму для добавления записи только для таблицы curse и video_lessons
            if ($selected_table == 'course' || $selected_table == 'video_lessons') {
                echo "<h2>Добавление новой записи в таблицу $selected_table:</h2>";
                echo "<form method='post' action='";
                if ($selected_table == 'course') {
                    echo "add_curse.php";
                    echo "'>";
                    echo "Описание курса: <input type='text' name='description'><br>";
                    echo "Цена курса: <input type='text' name='course_price'><br>";
                    echo "Количество видеоуроков: <input type='text' name='videolessons'><br>"; // Новое поле
                } elseif ($selected_table == 'video_lessons') {
                    echo "add_record.php";
                    echo "'>";
                    echo "Название урока: <input type='text' name='lesson_name'><br>";
                    echo "ID курса: <input type='text' name='course_id'><br>";
                    echo "URL видеоурока: <input type='text' name='url'><br>";
                }
                echo "<button type='submit'>Добавить запись</button>";
                echo "</form>";
            }
        } else {
            echo "Нет данных в таблице $selected_table";
        }

        // Закрытие подключения
        $mysqli->close();
    }
    ?>

    <!-- Кнопка для перехода на страницу "Список учащихся" -->
    <a href="students_list.php">Список учащихся</a>
</body>
</html>
