<?php

require_once('./connection.php');

$id = $_GET['id'];

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();

$stmt = $pdo->prepare('SELECT * FROM book_authors ba LEFT JOIN authors a ON ba.author_id = a.id WHERE ba.book_id = :id');
$stmt->execute(['id' => $id]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" href="book.webp" type="image/webp">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            display: flex;
            align-items: center;
        }
        .image-container {
            flex: 0 0 200px;
            margin-right: 20px;
        }
        img {
            max-width: 100%;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .info-container {
            flex: 1;
        }
        h1 {
            color: #4CAF50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        h3 {
            color: #4CAF50;
            margin-top: 15px;
            margin-bottom: 5px;
        }
        .authors {
            font-style: italic;
            color: #555;
            margin: 5px 0 15px 0;
        }
        p {
            line-height: 1.6;
            margin: 15px 0;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .price {
            font-size: 1.5em;
            color: #ff5722;
            margin: 10px 0;
        }
        .edit-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }
        .edit-link:hover {
            background-color: #45a049;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #64b5f6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            text-align: center;
        }
        .back-link:hover {
            background-color: #42a5f5;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="image-container">
            <img src="<?= htmlspecialchars($book['cover_path'], ENT_QUOTES, 'UTF-8'); ?>" alt="Cover image of <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="info-container">
            <h1><?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <div class="authors">
                <?php
                $authors = [];
                while ($author = $stmt->fetch()) {
                    $authors[] = htmlspecialchars($author['first_name'] . ' ' . $author['last_name'], ENT_QUOTES, 'UTF-8');
                }
                echo implode(', ', $authors);
                ?>
            </div>
            <h3>Release Date:</h3>
            <p><?= htmlspecialchars($book['release_date'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h3>Language:</h3>
            <p><?= htmlspecialchars($book['language'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h3>Summary:</h3>
            <p><?= htmlspecialchars($book['summary'], ENT_QUOTES, 'UTF-8'); ?></p>
            <h3 class="price">Price: $<?= number_format($book['price'], 2); ?></h3>
            <h3 class="stock">Stock: <?= htmlspecialchars($book['stock_saldo'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <h3 class="pages">Pages: <?= htmlspecialchars($book['pages'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <h3 class="type">Type: <?= htmlspecialchars($book['type'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <a class="edit-link" href="./edit.php?id=<?= $id; ?>">Edit Book Details</a>
            <a class="back-link" href="./index.php">Go Back to Index</a>
        </div>
    </div>
    <form action="./delete.php" method="post" style="margin-top: 20px;">
        <input type="hidden" name="id" value="<?= $id; ?>">
        <input type="submit" name="action" value="Delete" style="padding: 10px 15px; background-color: #f44336; color: white; border: none; border-radius: 5px; cursor: pointer;">
    </form>
</body>
</html>
