<?php
session_start();

// Подключение к базе данных
$mysqli = new mysqli("localhost", "root", "", "bdcursach");

// Проверка подключения
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Проверяем, передан ли параметр course_id
if (!isset($_GET['course_id'])) {
    echo "Ошибка: Не указан идентификатор курса";
    exit;
}

$course_id = $_GET['course_id'];

// Получение информации о курсе
$stmt = $mysqli->prepare("SELECT * FROM course WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Проверка наличия данных о курсе
if ($result->num_rows === 0) {
    echo "Ошибка: Курс не найден";
    exit;
}

$course = $result->fetch_assoc();

// Получение уроков для выбранного курса
$lessons_stmt = $mysqli->prepare("SELECT * FROM video_lessons WHERE course_id = ?");
$lessons_stmt->bind_param("i", $course_id);
$lessons_stmt->execute();
$lessons_result = $lessons_stmt->get_result();

// Закрытие подключения
$stmt->close();
$lessons_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $course['description']; ?></title>
    <style>
        body {
            text-align: center;
        }
        table {
            margin: 0 auto;
        }
        th, td {
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1><?php echo $course['description']; ?></h1>
    <h2>Уроки:</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Название урока</th>
                <th>Ссылка</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($lesson = $lessons_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $lesson['lesson_name']; ?></td>
                    <td><a href="<?php echo $lesson['url']; ?>">Перейти</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
