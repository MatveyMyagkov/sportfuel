<?php
require_once 'config.php';

// Обработка формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $rating = (int)$_POST['rating'];
    $message = htmlspecialchars($_POST['message']);

    // Валидация
    $errors = [];
    if (empty($name)) $errors[] = 'Введите ваше имя';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Введите корректный email';
    if ($rating < 1 || $rating > 5) $errors[] = 'Выберите оценку от 1 до 5';
    if (empty($message)) $errors[] = 'Напишите ваш отзыв';

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, email, rating, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $rating, $message]);
            $success = 'Спасибо за ваш отзыв! Он будет опубликован после проверки.';
        } catch(PDOException $e) {
            $errors[] = 'Ошибка при сохранении отзыва: ' . $e->getMessage();
        }
    }
}

// Получаем ВСЕ отзывы (не только approved)
$all_reviews = [];
try {
    $stmt = $pdo->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $all_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $errors[] = 'Ошибка при загрузке отзывов: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель - Отзывы</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f2f2f2; }
        .pending { background-color: #fff3cd; }
        .approved { background-color: #d4edda; }
        .actions a { margin-right: 10px; }
    </style>
</head>
<body>
    <h1>Управление отзывами</h1>
    <table>
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Email</th>
            <th>Оценка</th>
            <th>Отзыв</th>
            <th>Дата</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
        <?php foreach ($reviews as $review): ?>
        <tr class="<?= $review['status'] ?>">
            <td><?= $review['id'] ?></td>
            <td><?= htmlspecialchars($review['name']) ?></td>
            <td><?= htmlspecialchars($review['email']) ?></td>
            <td><?= str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']) ?></td>
            <td><?= nl2br(htmlspecialchars($review['message'])) ?></td>
            <td><?= $review['created_at'] ?></td>
            <td><?= $review['status'] === 'approved' ? 'Одобрен' : 'На модерации' ?></td>
            <td class="actions">
                <?php if ($review['status'] !== 'approved'): ?>
                    <a href="?approve=<?= $review['id'] ?>">Одобрить</a>
                <?php endif; ?>
                <a href="?delete=<?= $review['id'] ?>" onclick="return confirm('Удалить этот отзыв?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><a href="logout.php">Выйти</a></p>
</body>
</html>