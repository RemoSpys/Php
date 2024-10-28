<?php
require_once('./connection.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
 
    <ul>
    <?php while ($row = $stmt->fetch()){   ?>
        <li>
            <a href= './book.php?id=<?=$row['id']; ?>'>
                <?= $row['title']; ?>
            </a>
        </li>
    </ul>
    <?php } ?>
</body>
</html>