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
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Проверяем, было ли указано количество коинов для пополнения
    if (isset($_POST["amount"])) {
        // Получаем количество коинов, которое пользователь хочет добавить
        $amount = intval(trim($_POST["amount"]));

        // Проверяем, чтобы количество коинов было положительным числом
        if ($amount > 0) {
            // Увеличиваем баланс пользователя на указанное количество коинов
            $new_coins_balance = $user['coins'] + $amount;

            // Обновляем баланс пользователя в базе данных
            $update_coins_stmt = $mysqli->prepare("UPDATE student SET coins = ? WHERE login = ?");
            $update_coins_stmt->bind_param("is", $new_coins_balance, $user['login']);
            $update_result = $update_coins_stmt->execute();

            // Проверяем успешность выполнения запроса и ошибки, если есть
            if ($update_result === FALSE) {
                printf("Ошибка при выполнении запроса: %s\n", $mysqli->error);
                exit;
            }

            // Проверяем количество строк, которые были затронуты запросом
            if ($update_coins_stmt->affected_rows === 1) {
                // Пополнение коинов произведено успешно
                header('Location: profile.php');
                exit;
            } else {
                // Ошибка при обновлении баланса
                $error_message = "Ошибка при пополнении коинов. Пожалуйста, попробуйте снова.";
                header('Location: payment_simulation.php?success=false&error=' . urlencode($error_message));
                exit;
            }
        } else {
            // Неверно указано количество коинов
            $error_message = "Пожалуйста, введите корректное количество коинов для пополнения.";
            header('Location: payment_simulation.php?success=false&error=' . urlencode($error_message));
            exit;
        }
    } else {
        // Количество коинов не было передано
        $error_message = "Пожалуйста, укажите количество коинов для пополнения.";
        header('Location: payment_simulation.php?success=false&error=' . urlencode($error_message));
        exit;
    }
}

// Закрытие подключения
$stmt->close();
$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Пополнение коинов</title>
    <link rel="stylesheet" type="text/css" href="payment_styles.css"> 
</head>
<body>
    <div class="container">
        <h2>Пополнение коинов</h2>
        <form id="paymentForm" action="process_payment.php" method="post">
            <label for="cardNumber">Номер карты:</label>
            <input type="text" id="cardNumber" name="cardNumber" required>

            <label for="expiryDate">Срок действия:</label>
            <input type="text" id="expiryDate" name="expiryDate" placeholder="MM/YY" required>

            <label for="cvv">CVV:</label>
            <input type="text" id="cvv" name="cvv" maxlength="3" required>

            <label for="amount">Количество коинов:</label>
            <input type="number" id="amount" name="amount" required>

            <button type="submit" id="submitBtn">Пополнить</button>
        </form>

    <script>
        document.getElementById('paymentForm').addEventListener('submit', function(event) {
            // Отображаем анимацию загрузки и сообщение о запросе в обработке
            document.getElementById('loading').style.display = 'block';
            document.getElementById('messageText').textContent = 'Запрос обрабатывается, подождите...';
            document.getElementById('successMessage').style.display = 'block';

            // Отладочный вывод - печатаем данные формы
            console.log('Form data:', new FormData(this));
        });
    </script>

    <!-- Добавляем кнопку для возврата на страницу профиля -->
    <button id="returnLink" onclick="window.location.href = 'profile.php';">Вернуться в профиль</button>
</body>
</html>


