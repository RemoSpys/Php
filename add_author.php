<?php

require_once('./connection.php');

// Check if 'book_id' is provided in the POST request
if (isset($_POST['book_id']) && filter_var($_POST['book_id'], FILTER_VALIDATE_INT)) {
    $id = $_POST['book_id'];
} else {
    die("Invalid book ID.");
}

if (isset($_POST['action']) && $_POST['action'] == 'add_author') {
    try {
        // Add author to the book
        $stmt = $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)');
        $stmt->execute([
            'book_id' => $id,
            'author_id' => $_POST['author_id']
        ]);
        header("Location: ./edit.php?id={$id}");
        exit;
    } catch (Exception $e) {
        die("Error adding author: " . $e->getMessage());
    }
}

// Redirect to index if no valid action
header("Location: ./index.php");
exit;
