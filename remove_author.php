<?php

// Remove an author from the book
if (isset($_POST['action']) && $_POST['action'] == 'remove_author') {
    
    require_once('./connection.php');
    
    $id = $_GET['id'];

    $stmt = $pdo->prepare('DELETE FROM book_authors WHERE book_id = :book_id AND author_id = :author_id');
    $stmt->execute([
        'book_id' => $id,
        'author_id' => $_POST['author_id']]);

    header("Location: ./edit.php?id={$id}");

} else {
    header("Location : ./index.php");
}
?>