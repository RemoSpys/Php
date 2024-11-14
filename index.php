<?php
require_once('./connection.php');

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$query = "SELECT * FROM books WHERE is_deleted = 0";
$params = [];

if (!empty($search)) {
    $query .= " AND title LIKE ?";
    $params[] = "%$search%";
}

$query .= " LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($query);
$stmt->execute($params);

$countQuery = "SELECT COUNT(*) FROM books WHERE is_deleted = 0";
if (!empty($search)) {
    $countQuery .= " AND title LIKE ?";
}
$countStmt = $pdo->prepare($countQuery);
$countStmt->execute($params);
$totalBooks = $countStmt->fetchColumn();
$totalPages = ceil($totalBooks / $limit);

$maxDisplayPages = 5;
$startPage = max(2, $page - 2);
$endPage = min($totalPages - 1, $page + 2);
if ($endPage - $startPage + 1 < $maxDisplayPages) {
    if ($startPage === 2) {
        $endPage = min($totalPages - 1, $startPage + $maxDisplayPages - 1);
    } else {
        $startPage = max(2, $endPage - $maxDisplayPages + 1);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raamatupoe esileht</title>
    <link rel="icon" href="book.webp" type="image/webp">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background-color: #4CAF50;
            color: white;
            padding: 1rem;
            text-align: center;
            font-size: 1.5rem;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 1rem;
        }
        form {
            margin-bottom: 1rem;
            display: flex;
            gap: 0.5rem;
        }
        input[type="text"] {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            padding: 0.5rem 1rem;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            background-color: #fff;
            margin: 0.5rem 0;
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            transition: box-shadow 0.3s ease;
        }
        li:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
        }
        .pagination a {
            margin: 0 0.5rem;
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: #4CAF50;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pagination a.active {
            background-color: #4CAF50;
            color: white;
        }
        .pagination a:hover {
            background-color: #45a049;
            color: white;
        }
    </style>
</head>
<body>
    <header>Raamatupoe esileht</header>
    <div class="container">
        <form method="GET">
            <input type="text" name="search" placeholder="Otsi raamatuid" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Otsi</button>
        </form>

        <ul>
            <?php while ($book = $stmt->fetch()) { ?> 
                <li>
                    <a href='./book.php?id=<?= $book['id']; ?>'>
                        <?= htmlspecialchars($book['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </li>
            <?php } ?>
        </ul>

        <div class="pagination">
            <a href="?search=<?= urlencode($search); ?>&page=1" class="<?= $page === 1 ? 'active' : ''; ?>">1</a>
            <?php if ($startPage > 2) { ?>
                <span>...</span>
            <?php } ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++) { ?>
                <a href="?search=<?= urlencode($search); ?>&page=<?= $i; ?>" class="<?= $i === $page ? 'active' : ''; ?>">
                    <?= $i; ?>
                </a>
            <?php } ?>

            <?php if ($endPage < $totalPages - 1) { ?>
                <span>...</span>
            <?php } ?>
            <?php if ($totalPages > 1) { ?>
                <a href="?search=<?= urlencode($search); ?>&page=<?= $totalPages; ?>" class="<?= $page === $totalPages ? 'active' : ''; ?>">
                    <?= $totalPages; ?>
                </a>
            <?php } ?>
        </div>
    </div>
</body>
</html>