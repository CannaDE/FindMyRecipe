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
    $response = ['recipes' => null, 'status' => 'success'];
    echo json_encode($response);
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
    AND i.name IN ($placeholders)
    GROUP BY r.id 
    HAVING COUNT(DISTINCT i.name) = ?";

$stmt = $conn->prepare($sql);
$ingredient_count = count($ingredients);

// Parameter für die bind_param-Funktion vorbereiten
//$params = array_merge($ingredients, [$ingredient_count]);
//$stmt->bind_param(str_repeat('s', $ingredient_count) . 'i', ...$params);
$stmt->execute(array_merge($ingredients, [$ingredient_count]));
$result = $stmt->get_result();

$response = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) { 
        $json = [
            'title' => $row['title'],
            'description' => $row['description'],
            'image_url' => $row['image_url'],
            'source_url' => $row['source_url'],
            'url' => htmlspecialchars($row['url'])
        ];
        array_push($response, $json);
    }
}
$response = ['recipes' => $response, 'status' => 'success'];
echo json_encode($response);

$stmt->close();
$conn->close();
?>
