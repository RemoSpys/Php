<?php

// update books
if (isset($_POST['action']) && $_POST['action'] == 'Save') {

    require_once('./connection.php');
    
    $id = $_GET['id'];

    $stmt = $pdo->prepare('UPDATE books SET title = :title, price = :price WHERE id = :id');
    $stmt->execute([
        'id' => $id,
        'title' => $_POST['title'],
        'price' => $_POST['price']
    ]);

    header("Location: ./edit.php?id={$id}");
    exit;

} else {
    header("Location : ./index.php");
}
?>
