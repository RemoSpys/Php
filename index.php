<?php
require_once('./connection.php');

$booksPerPage = 25;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
$offset = ($page - 1) * $booksPerPage;

$totalBooks = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
$totalPages = ceil($totalBooks / $booksPerPage);

$stmt = $pdo->prepare("SELECT id, title, release_date FROM books LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $booksPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book List - Page <?= $page ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-4">
    <div class="container mx-auto max-w-5xl bg-white rounded-lg shadow-lg p-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Book List</h1>
        
        <ul class="space-y-4">
            <?php foreach ($books as $book): ?>
                <li class="p-4 bg-gray-50 hover:bg-gray-100 rounded-lg shadow-sm">
                    <a href="./book.php?id=<?= $book['id']; ?>" class="text-lg font-semibold text-blue-600 hover:underline">
                        <?= htmlspecialchars($book['title']); ?>
                    </a>
                    <span class="text-sm text-gray-500">(Published: <?= htmlspecialchars($book['release_date']); ?>)</span>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="mt-6 flex justify-center space-x-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Previous</a>
            <?php endif; ?>

            <?php
            if ($page > 2) {
                echo '<a href="?page=1" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-blue-500 hover:text-white">1</a>';
                if ($page > 3) {
                    echo '<span class="px-2">...</span>';
                }
            }

            for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++) {
                if ($i == $page) {
                    echo '<span class="px-4 py-2 bg-blue-700 text-white rounded">' . $i . '</span>';
                } elseif ($i != 1 && $i != $totalPages) {
                    echo '<a href="?page=' . $i . '" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-blue-500 hover:text-white">' . $i . '</a>';
                }
            }

            if ($page < $totalPages - 2) {
                echo '<span class="px-2">...</span>';
                echo '<a href="?page=' . $totalPages . '" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-blue-500 hover:text-white">' . $totalPages . '</a>';
            }
            ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
