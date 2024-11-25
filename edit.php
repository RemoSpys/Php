<?php
// Include the database connection
require_once('./connection.php');

// Get the book ID from the URL (GET parameter)
$id = $_GET['id'];

// Fetch the book details using its ID
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id' => $id]);
$book = $stmt->fetch(); // Store the book data

// Fetch all authors associated with this book
$bookAuthorsStmt = $pdo->prepare('
    SELECT a.id, a.first_name, a.last_name 
    FROM book_authors ba 
    LEFT JOIN authors a ON ba.author_id = a.id 
    WHERE ba.book_id = :id
');
$bookAuthorsStmt->execute(['id' => $id]);

// Fetch all authors who are NOT associated with this book
$availableAuthorsStmt = $pdo->prepare('
    SELECT * 
    FROM authors 
    WHERE id NOT IN (
        SELECT author_id 
        FROM book_authors 
        WHERE book_id = :book_id
    )
');
$availableAuthorsStmt->execute(['book_id' => $id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Book</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
        }
        nav a {
            text-decoration: none;
            color: #007BFF;
            padding: 5px 10px;
            border: 1px solid #007BFF;
            border-radius: 5px;
        }
        nav a:hover {
            background-color: #007BFF;
            color: white;
        }
        h1 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        input, select, button {
            margin: 5px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            width: 100%;
        }
        button {
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin-bottom: 10px;
        }
        .remove-btn {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <nav>
        <a href="./book.php?id=<?= $id; ?>">Back</a>
    </nav>

    <h1>Edit Book</h1>

    <!-- Form to edit book details -->
    <form action="./update_book.php?id=<?= $id; ?>" method="post">
        <label for="title">Title:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($book['title']); ?>" id="title" required>

        <label for="price">Price:</label>
        <input type="text" name="price" value="<?= htmlspecialchars($book['price']); ?>" id="price" required>

        <button type="submit" name="action" value="Save">Save</button>
    </form>

    <h3>Authors:</h3>
    <ul>
        <?php while ($author = $bookAuthorsStmt->fetch()) { ?>
            <li>
                <?= htmlspecialchars($author['first_name']) . ' ' . htmlspecialchars($author['last_name']); ?>
                <form action="./remove_author.php?id=<?= $id; ?>" method="post" style="display:inline;">
                    <input type="hidden" name="author_id" value="<?= $author['id']; ?>">
                    <button type="submit" name="action" value="remove_author" class="remove-btn">
                        Remove
                    </button>
                </form>
            </li>
        <?php } ?>
    </ul>

    <form action="./add_author.php" method="post">
        <input type="hidden" name="book_id" value="<?= $id; ?>">

        <label for="author_id">Add Existing Author:</label>
        <select name="author_id" id="author_id">
            <option value="">Select an author</option>
            <?php while ($author = $availableAuthorsStmt->fetch()) { ?>
                <option value="<?= $author['id']; ?>">
                    <?= htmlspecialchars($author['first_name']) . ' ' . htmlspecialchars($author['last_name']); ?>
                </option>
            <?php } ?>
        </select>

        <h4>OR Add a New Author:</h4>
        <input type="text" name="new_author_first_name" placeholder="First Name">
        <input type="text" name="new_author_last_name" placeholder="Last Name">

        <button type="submit" name="action" value="add_author">Add Author</button>
    </form>
</body>
</html>
