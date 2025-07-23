<?php
require_once 'config.php';

// Включаем вывод ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Обработка формы отправки отзыва
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Очищаем и проверяем введенные данные
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $rating = (int)$_POST['rating'];
    $message = htmlspecialchars(trim($_POST['message']));

    // Проверяем данные
    $errors = [];
    if (empty($name)) $errors[] = 'Пожалуйста, введите ваше имя';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Пожалуйста, введите корректный email';
    if ($rating < 1 || $rating > 5) $errors[] = 'Пожалуйста, выберите оценку от 1 до 5';
    if (empty($message)) $errors[] = 'Пожалуйста, напишите ваш отзыв';

    // Если ошибок нет - сохраняем в БД
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, message, status) VALUES (?, ?, ?, ?, 'approved')");
            $stmt->execute([$name, $email, $rating, $message]);
            $success = 'Спасибо за ваш отзыв!';
            
            // Перенаправляем чтобы избежать повторной отправки формы
            header("Location: reviews.php?success=1");
            exit;
        } catch(PDOException $e) {
            $errors[] = 'Ошибка при сохранении отзыва: ' . $e->getMessage();
        }
    }
}

// Получаем все одобренные отзывы из БД
try {
    $stmt = $pdo->query("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Ошибка при загрузке отзывов: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы | SPORT FUEL</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="images/pict.png" type="image/png">
    <style>
        /* Стили для формы и отзывов */
        .reviews-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .alert {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #ffdddd;
            border-left: 4px solid #f44336;
        }
        .alert-success {
            background-color: #ddffdd;
            border-left: 4px solid #4CAF50;
        }
        .review-form {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .rating input {
            display: none;
        }
        .rating label {
            color: #ddd;
            font-size: 24px;
            cursor: pointer;
        }
        .rating input:checked ~ label {
            color: gold;
        }
        .submit-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .reviews-list {
            margin-top: 30px;
        }
        .review-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-rating {
            color: gold;
        }
        .review-date {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="link">
    <nav class="nav">
      <ul>
        <li><a href="main.html"><i class="fas fa-home"></i> ГЛАВНАЯ</a></li>
        <li><a href="catalog.html"><i class="fas fa-store"></i> КАТАЛОГ</a></li>
        <li><a href="helper.html"><i class="fas fa-medal"></i> ПОМОЩНИК</a></li>
        <li><a href="reviews.php"><i class="fas fa-headset"></i> ОТЗЫВЫ</a></li>
      </ul>
    </nav>
  </div>
    <div class="reviews-container">
        <h1>Отзывы наших клиентов</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <p>Спасибо за ваш отзыв!</p>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="review-form">
            <h2>Оставить отзыв</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="name">Ваше имя:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Ваш email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Ваша оценка:</label>
                    <div class="rating">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                            <label for="star<?= $i ?>">★</label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="message">Ваш отзыв:</label>
                    <textarea id="message" name="message" rows="5" required></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Отправить отзыв</button>
            </form>
        </div>

        <div class="reviews-list">
            <h2>Последние отзывы</h2>
            
            <?php if (empty($reviews)): ?>
                <p>Пока нет отзывов. Будьте первым!</p>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <h3><?= htmlspecialchars($review['name']) ?></h3>
                            <div class="review-rating">
                                <?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?>
                            </div>
                        </div>
                        <p><?= nl2br(htmlspecialchars($review['message'])) ?></p>
                        <div class="review-date">
                            <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>