<?php
session_start();

// Подключение к базе данных
$mysqli = new mysqli("localhost", "root", "", "bdcursach");

// Проверка подключения
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['login'])) {
    header('Location: login.php');
    exit;
}

// Получение данных о пользователе из базы данных
$login = $_SESSION['login'];
$stmt = $mysqli->prepare("SELECT * FROM student WHERE login = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$result = $stmt->get_result();

// Проверка наличия данных о пользователе
if ($result->num_rows === 0) {
    echo "Ошибка: Пользователь не найден";
    exit;
}

$user = $result->fetch_assoc();

// Получение списка курсов, на которые пользователь не подписан
$courses_stmt = $mysqli->prepare("SELECT * FROM course WHERE course_id NOT IN (SELECT course_id FROM student WHERE FIND_IN_SET(course_id, ?))");
$courses_stmt->bind_param("s", $user['subscribed_courses']);
$courses_stmt->execute();
$courses_result = $courses_stmt->get_result();

// Функция удаления аккаунта
function deleteAccount() {
    global $mysqli, $login;
    $delete_stmt = $mysqli->prepare("DELETE FROM student WHERE login = ?");
    $delete_stmt->bind_param("s", $login);
    $delete_stmt->execute();
    session_destroy();
    header('Location: login.php');
    exit;
}

function subscribeCourse($course_id, $course_price) {
    global $mysqli, $user;
    
    // Проверяем, является ли курс пробным (ID 1 в базе данных)
    $is_trial_course = ($course_id == 1); // Предполагая, что ID пробного курса равен 1

    // Проверяем, хватает ли у пользователя коинов для оплаты курса, если это не пробный курс
    if (!$is_trial_course && $user['coins'] < $course_price) {
        // Недостаточно коинов для оплаты курса
        return false;
    }

    // Если это не пробный курс или пользователь имеет достаточно коинов, продолжаем подписку
    // ...

    if ($user['coins'] >= $course_price || $is_trial_course) {
        // Вычитаем стоимость курса из баланса пользователя
        if (!$is_trial_course) {
            $new_coins_balance = $user['coins'] - $course_price;

            // Обновляем баланс пользователя в базе данных
            $update_coins_stmt = $mysqli->prepare("UPDATE student SET coins = ? WHERE login = ?");
            $update_coins_stmt->bind_param("is", $new_coins_balance, $user['login']);
            $update_coins_stmt->execute();

            // Проверяем успешность обновления баланса
            if ($update_coins_stmt->affected_rows !== 1) {
                return false;
            }
        }

        // Добавляем ID курса в список подписанных курсов пользователя
        $subscribed_courses = $user['subscribed_courses'] ? explode(",", $user['subscribed_courses']) : [];
        if (!in_array($course_id, $subscribed_courses)) {
            $subscribed_courses[] = $course_id;
            $subscribed_courses_str = implode(",", $subscribed_courses);
            $update_subscribed_courses_stmt = $mysqli->prepare("UPDATE student SET subscribed_courses = ? WHERE login = ?");
            $update_subscribed_courses_stmt->bind_param("ss", $subscribed_courses_str, $user['login']);
            $update_subscribed_courses_stmt->execute();

            // Проверяем успешность обновления списка подписанных курсов
            if ($update_subscribed_courses_stmt->affected_rows === 1) {
                return true; // Пользователь успешно подписан на курс
            } else {
                return false;
            }
        }
    } else {
        // Недостаточно коинов для оплаты курса
        return false;
    }
}

// Проверка нажатия кнопки "Удалить аккаунт"
if (isset($_POST['delete_account'])) {
    deleteAccount();
}

// Проверка нажатия кнопки "Подписаться на курс"
if (isset($_POST['subscribe_course'])) {
    $course_id = $_POST['course_id'];
    $course_price = $_POST['course_price']; // Добавляем получение цены курса
   
    if (subscribeCourse($course_id, $course_price)) {
        // Пользователь успешно подписан на курс
        header('Location: profile.php'); // Перенаправление на страницу профиля
        exit;
    } else {
        // Недостаточно коинов для оплаты курса
        echo "<script>alert('Недостаточно коинов для оплаты курса');</script>";
    }
}


// Проверка нажатия кнопки "Посмотреть курс" для подписанных курсов
if (isset($_POST['view_subscribed_course'])) {
    $course_id = $_POST['course_id'];
    header("Location: course.php?course_id=$course_id"); // Перенаправление на страницу курса
    exit;
}

// Закрытие подключения
$stmt->close();
$courses_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" type="text/css" href="profile_styles.css">
</head>
<body>
<header>
        <div class="logo-holder">
            <img src="images/eliyabhet.svg" alt="" style="height: 12vh; width: 12vw;">
        </div>
        <ul class="menu">
           
                <li><a href="index.html">Вернуться на Главную</a></li>
        </ul>
    </header>
    
    <h1>Профиль пользователя</h1>
    <div>
        <p>Имя: <?php echo $user['Name']; ?></p>
        <p>Фамилия: <?php echo $user['Surname']; ?></p>
        <div class="coin-block">
        <img src="images/coin_icon.png" alt="Coin Icon" class="coin-icon" width="16px" >
        <span class="coin-count"><?php echo $user['coins']; ?></span>
        <form method="post" action="payment_simulation.php">
            <button type="submit" name="simulate_payment">Пополнить баланс</button>
        </form>
    </div>
        <form method="post">
            <button type="submit" name="delete_account">Удалить аккаунт</button>
        </form>
    </div>
    <h2>Доступные курсы</h2>
    <table>
        <thead>
            <tr>
                <th>Название курса</th>
                <th>Цена</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($course = $courses_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $course['description']; ?></td>
                    <td><?php echo strval($course['course_price']); ?></td>

                    <td>
                        <form method="post">
                            <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                            <input type="hidden" name="course_price" value="<?php echo $course['course_price']; ?>">
                            <button type="submit" name="subscribe_course">Подписаться на курс</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <h2>Подписанные курсы</h2>
<?php 
// Получение подписанных курсов пользователя
$subscribed_courses = explode(",", $user['subscribed_courses']);
if (empty($subscribed_courses)) {
    echo "<p>У вас пока нет подписанных курсов</p>";
} else {
    // Формируем строку с плейсхолдерами для IN оператора
    $placeholders = implode(',', array_fill(0, count($subscribed_courses), '?'));
    // Подготавливаем запрос с динамическим количеством параметров
    $subscribed_courses_stmt = $mysqli->prepare("SELECT * FROM course WHERE course_id IN ($placeholders)");
    if ($subscribed_courses_stmt) {
        // Привязываем параметры
        $types = str_repeat('i', count($subscribed_courses)); // 'i' означает integer
        $subscribed_courses_stmt->bind_param($types, ...$subscribed_courses);
        // Выполняем запрос
        $subscribed_courses_stmt->execute();
        $subscribed_courses_result = $subscribed_courses_stmt->get_result();
?>
<table>
    <thead>
        <tr>
            <th>Название курса</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($course = $subscribed_courses_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $course['description']; ?></td>
                <td><?php echo $course['course_price']; ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
                        <button type="submit" name="view_subscribed_course">Посмотреть курс</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php } else { ?>
    <p>Ошибка при выполнении запроса к базе данных</p>
<?php } ?>
<?php } ?>

</body>
</html>
