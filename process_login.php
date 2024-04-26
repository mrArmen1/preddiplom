<?php
session_start();

// Подключение к базе данных
$mysqli = new mysqli("localhost", "root", "", "bdcursach");

// Проверка подключения
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Получение данных из формы
$login = $_POST['login'];
$password = $_POST['password'];

echo "Login: " . $login . "<br>";
echo "Password: " . $password . "<br>";

// Подготовленный запрос для проверки авторизации студента
$stmt_student = $mysqli->prepare("SELECT * FROM student WHERE login = ?");
$stmt_student->bind_param("s", $login);
$stmt_student->execute();
$result_student = $stmt_student->get_result();

echo "Student rows: " . $result_student->num_rows . "<br>";

// Проверка наличия студента в базе данных
if ($result_student->num_rows == 1) {
    // Получаем данные студента
    $row_student = $result_student->fetch_assoc();
    $hashed_password_student = $row_student['password'];

    echo "Student hashed password: " . $hashed_password_student . "<br>";

    // Проверяем введенный пароль с хэшированным паролем из базы данных
    if (password_verify($password, $hashed_password_student)) {

        // Авторизация успешна, устанавливаем сессию и перенаправляем на страницу профиля студента
        $_SESSION['login'] = $login;
        header('Location: profile.php');
        exit;
    } else {
        // Пароль неверный
        echo "Неправильный пароль для студента<br>";
        exit;
    }
}

// Проверка наличия учителя в базе данных
$result_teacher = $mysqli->query("SELECT * FROM teacher WHERE login = '$login'");
$row_teacher = $result_teacher->fetch_assoc();

if ($row_teacher && $row_teacher['password'] == $password) {
    // Авторизация успешна, устанавливаем сессию и перенаправляем на страницу профиля учителя
    $_SESSION['login'] = $login;
    header('Location: teacher_profile.php');
    exit;
} else {
    // Пароль неверный
    echo "Неправильный пароль для учителя<br>";
    exit;
}

// Если логин не найден ни среди студентов, ни среди учителей
echo "Неправильный логин или пароль<br>";
exit;

// Закрытие подключения
$stmt_student->close();
$mysqli->close();
?>
