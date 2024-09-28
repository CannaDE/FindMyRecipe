<?php
$servername = "localhost";
$username = "root";
$password = ""; // Dein MySQL-Passwort
$dbname = "rezept"; // Deine Datenbank

// Verbindung zur MySQL-Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Zutaten abfragen, die im JSON-Format übergeben werden
$ingredients = isset($_GET['ingredients']) ? json_decode($_GET['ingredients'], true) : [];

// Prüfen, ob Zutaten vorhanden sind
if (empty($ingredients)) {
    echo "<p>Keine Zutaten ausgewählt.</p>";
    exit;
}

// SQL-Query zusammenstellen
$placeholders = implode(',', array_fill(0, count($ingredients), '?'));
$sql = "
    SELECT r.title, r.description, r.url, r.image_url, r.source_id, s.url AS source_url
    FROM recipes r 
    JOIN sources s ON r.source_id = s.id
    JOIN recipe_ingredients ri ON r.id = ri.recipe_id 
    JOIN ingredients i ON ri.ingredient_id = i.id 
    WHERE i.name LIKE '%salz%' 
    OR i.name LIKE '%pfeffer%' 
    OR i.name IN ($placeholders)
    GROUP BY r.id 
    HAVING COUNT(DISTINCT i.name) = ?";

$stmt = $conn->prepare($sql);
$ingredient_count = count($ingredients);

// Parameter für die bind_param-Funktion vorbereiten
//$params = array_merge($ingredients, [$ingredient_count]);
//$stmt->bind_param(str_repeat('s', $ingredient_count) . 'i', ...$params);
$stmt->execute(array_merge($ingredients, [$ingredient_count]));
$result = $stmt->get_result();


if ($result->num_rows > 0) {
    echo '<h2>Du kannst '.$result->num_rows.' Rezpte zubereiten';
    while ($row = $result->fetch_assoc()) { 
        echo '<div class="card resultRecipes">';
        echo '<img class="recipeImg" src='.htmlspecialchars($row['image_url']).' alt="" title="'.htmlspecialchars($row['title']).'" />';
        echo '<h3>'.htmlspecialchars($row["title"]).'</h3>';
        echo '<p class="source">'.preg_replace('/https?:\/\//', '', $row['source_url']).'</p>';
        echo '<p>'.(($row['description']) ? htmlspecialchars($row["description"]) : "").'</p>';
        echo '<a href='.htmlspecialchars($row['url']).'>Zum Rezept</a>';
        echo '<div style="clear: both;"></div></div>';
    }
} else {
    echo "<p class=\"alert error\">Keine Rezepte gefunden für die ausgewählten Zutaten.</p>";
}

$stmt->close();
$conn->close();
?>
