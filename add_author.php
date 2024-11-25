<?php
require_once('./connection.php');

// Kontrollime, kas vajalikud POST-parameetrid on määratud
if (isset($_POST['book_id'], $_POST['action']) && $_POST['action'] === 'add_author') {
    $book_id = $_POST['book_id'];

    try {
        // Algatame muutujaga author_id
        $author_id = null;

        // Kui valiti olemasolev autor
        if (!empty($_POST['author_id'])) {
            $author_id = $_POST['author_id'];
        }
        // Kui lisati uue autori andmed
        elseif (!empty($_POST['new_author_first_name']) && !empty($_POST['new_author_last_name'])) {
            $first_name = trim($_POST['new_author_first_name']); // Eesnimi
            $last_name = trim($_POST['new_author_last_name']);   // Perekonnanimi

            // Kontrollime, kas autor on juba andmebaasis olemas
            $stmt = $pdo->prepare('SELECT id FROM authors WHERE first_name = :first_name AND last_name = :last_name');
            $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name]);
            $author = $stmt->fetch();

            // Kui autor on olemas, kasutame olemasolevat ID-d; vastasel juhul lisame uue autori
            if ($author) {
                $author_id = $author['id'];
            } else {
                // Lisame uue autori tabelisse authors
                $stmt = $pdo->prepare('INSERT INTO authors (first_name, last_name) VALUES (:first_name, :last_name)');
                $stmt->execute(['first_name' => $first_name, 'last_name' => $last_name]);
                $author_id = $pdo->lastInsertId(); // Saame äsja lisatud autori ID
            }
        }

        // Seome autori raamatuga
        if (isset($author_id)) {
            $stmt = $pdo->prepare('INSERT INTO book_authors (book_id, author_id) VALUES (:book_id, :author_id)');
            $stmt->execute(['book_id' => $book_id, 'author_id' => $author_id]);
        }

        // Suuname tagasi raamatu muutmise lehele
        header("Location: ./edit.php?id={$book_id}");
        exit();
    } catch (Exception $e) {
        // Vigade käsitlemine
        die("Viga: " . $e->getMessage());
    }
}

// Kui parameetrid puuduvad, suuname avalehele
header("Location: ./index.php");
exit();
