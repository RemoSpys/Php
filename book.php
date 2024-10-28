<?php
require_once('./connection.php');

$id = $_GET['id'];
 
$stmt = $pdo->prepare('SELECT * FROM books WHERE id = :id');
$stmt->execute(['id'=> $id]);
$book = $stnt->fetch();
 
var_dump($book);
?>