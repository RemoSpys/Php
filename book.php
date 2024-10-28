<?php
require_once('./connection.php');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "No book selected.";
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch();
if (!$book) {
    echo "Book not found.";
    exit;
}

$stmt = $pdo->prepare('
    SELECT a.id, a.first_name, a.last_name
    FROM book_authors ba
    LEFT JOIN authors a ON ba.author_id = a.id
    WHERE ba.book_id = :id
');
$stmt->execute(['id' => $id]);
$authors = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare('UPDATE books SET is_deleted = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
        header("Location: index.php");
        exit;
    } elseif (isset($_POST['save_changes'])) {
        $title = $_POST['title'];
        $price = $_POST['price'];
        $release_date = $_POST['release_date'];
        $language = $_POST['language'];
        $pages = $_POST['pages'];
        $summary = $_POST['summary'];
        $stock_saldo = $_POST['stock_saldo'];
        $author_ids = $_POST['author_ids'] ?? [];
        $new_author_first_name = $_POST['new_author_first_name'] ?? '';
        $new_author_last_name = $_POST['new_author_last_name'] ?? '';

        if ($new_author_first_name && $new_author_last_name) {
            $stmt = $pdo->prepare('INSERT INTO authors (first_name, last_name) VALUES (:first_name, :last_name)');
            $stmt->execute(['first_name' => $new_author_first_name, 'last_name' => $new_author_last_name]);
            $new_author_id = $pdo->lastInsertId();
            $author_ids[] = $new_author_id;
        }

        $stmt = $pdo->prepare('
            UPDATE books SET title = :title, price = :price, release_date = :release_date,
            language = :language, pages = :pages, summary = :summary, stock_saldo = :stock_saldo
            WHERE id = :id
        ');
        $stmt->execute([
            'title' => $title, 'price' => $price, 'release_date' => $release_date,
            'language' => $language, 'pages' => $pages, 'summary' => $summary,
            'stock_saldo' => $stock_saldo, 'id' => $id
        ]);

        $pdo->prepare('DELETE FROM book_authors WHERE book_id = :id')->execute(['id' => $id]);
        foreach ($author_ids as $author_id) {
            $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)')
                ->execute(['book_id' => $id, 'author_id' => $author_id]);
        }

        header("Location: book.php?id=$id");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($book['title']); ?> - Book Details</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="container mx-auto max-w-5xl bg-white shadow-lg rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4"><?= htmlspecialchars($book['title']); ?></h1>
        <div class="text-gray-500 mb-2">by <?= !empty($authors) ? implode(', ', array_map(fn($a) => htmlspecialchars($a['first_name'] . ' ' . $a['last_name']), $authors)) : 'Unknown'; ?></div>
        <div class="text-gray-700 space-y-2 mb-4">
            <p><strong>Release Date:</strong> <?= htmlspecialchars($book['release_date']); ?></p>
            <p><strong>Language:</strong> <?= htmlspecialchars($book['language']); ?></p>
            <p><strong>Pages:</strong> <?= htmlspecialchars($book['pages']); ?> pages</p>
            <p><strong>Type:</strong> <?= ucfirst(htmlspecialchars($book['type'])); ?></p>
            <p><strong>Summary:</strong> <?= nl2br(htmlspecialchars($book['summary'] ?? 'No summary available.')); ?></p>
        </div>
        <div class="flex items-center space-x-4 mb-4">
            <span class="text-xl font-semibold text-green-600">€<?= htmlspecialchars(number_format($book['price'], 2)); ?></span>
            <span class="<?= $book['stock_saldo'] > 0 ? 'text-green-500' : 'text-red-500'; ?> font-semibold">
                <?= $book['stock_saldo'] > 0 ? $book['stock_saldo'] . ' in stock' : 'Out of stock'; ?>
            </span>
        </div>
        <a href="./index.php" class="text-blue-600 hover:underline">← Back to Book List</a>
        <button id="editButton" class="ml-4 text-blue-600 hover:underline">✎ Edit</button>
        <form action="book.php?id=<?= $id ?>" method="post" class="inline ml-4">
            <button name="delete" class="text-red-600 hover:underline">🗑 Delete</button>
        </form>

        <div id="editForm" class="hidden mt-6">
            <h2 class="text-xl font-bold mb-4">Edit Book</h2>
            <form action="book.php?id=<?= $id ?>" method="post">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden mb-6">
                    <tbody>
                        <tr><td class="p-4 font-semibold">Title</td><td><input type="text" name="title" value="<?= htmlspecialchars($book['title']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">Price (€)</td><td><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($book['price']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">Release Date</td><td><input type="year" name="release_date" value="<?= htmlspecialchars($book['release_date']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">Language</td><td><input type="text" name="language" value="<?= htmlspecialchars($book['language']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">Pages</td><td><input type="number" name="pages" value="<?= htmlspecialchars($book['pages']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">Summary</td><td><textarea name="summary" class="w-full p-2 rounded border"><?= htmlspecialchars($book['summary']); ?></textarea></td></tr>
                        <tr><td class="p-4 font-semibold">Stock</td><td><input type="number" name="stock_saldo" value="<?= htmlspecialchars($book['stock_saldo']); ?>" class="w-full p-2 rounded border"></td></tr>
                        <tr>
                            <td class="p-4 font-semibold">Authors</td>
                            <td>
                                <select name="author_ids[]" id="author_ids" multiple class="w-full p-2 rounded border">
                                    <?php
                                    $all_authors = $pdo->query('SELECT id, first_name, last_name FROM authors')->fetchAll();
                                    foreach ($all_authors as $author) {
                                        $selected = in_array($author['id'], array_column($authors, 'id')) ? 'selected' : '';
                                        echo "<option value=\"{$author['id']}\" $selected>" . htmlspecialchars($author['first_name'] . ' ' . $author['last_name']) . "</option>";
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <tr><td class="p-4 font-semibold">New Author First Name</td><td><input type="text" name="new_author_first_name" class="w-full p-2 rounded border"></td></tr>
                        <tr><td class="p-4 font-semibold">New Author Last Name</td><td><input type="text" name="new_author_last_name" class="w-full p-2 rounded border"></td></tr>
                    </tbody>
                </table>
                <button type="submit" name="save_changes" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        $('#editButton').on('click', function() {
            $('#editForm').toggleClass('hidden');
        });
    </script>
</body>
</html>
