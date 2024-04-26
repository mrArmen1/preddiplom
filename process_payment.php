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

// Проверяем, были ли переданы данные из формы пополнения
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, было ли указано количество коинов для пополнения
    if (isset($_POST["amount"])) {
        // Получаем количество коинов, которое пользователь хочет добавить
        $amount = intval($_POST["amount"]);
        echo "Amount: " . $amount; // Добавляем отладочный вывод

        // Проверяем, чтобы количество коинов было положительным числом
        if ($amount > 0) {
            // Увеличиваем баланс пользователя на указанное количество коинов
            $new_coins_balance = $user['coins'] + $amount;
            echo "New Coins Balance: " . $new_coins_balance; // Добавляем отладочный вывод

            // Обновляем баланс пользователя в базе данных
            $update_coins_stmt = $mysqli->prepare("UPDATE student SET coins = ? WHERE login = ?");
            $update_coins_stmt->bind_param("is", $new_coins_balance, $user['login']);
            $update_coins_stmt->execute();

            // Проверяем успешность обновления баланса
            if ($update_coins_stmt->affected_rows === 1) {
                // Пополнение коинов произведено успешно
                header('Location: payment_simulation.php?success=true');
                exit;
            } else {
                // Ошибка при обновлении баланса
                echo "Ошибка при пополнении коинов. Пожалуйста, попробуйте снова.";
            }
        } else {
            // Неверно указано количество коинов
            echo "Пожалуйста, введите корректное количество коинов для пополнения.";
        }
    } else {
        // Количество коинов не было передано
        echo "Пожалуйста, укажите количество коинов для пополнения.";
    }
} else {
    // Если данные не были переданы методом POST
    echo "Ошибка: Недопустимый метод запроса.";
}

// Закрытие подключения
$stmt->close();
$mysqli->close();
?>
