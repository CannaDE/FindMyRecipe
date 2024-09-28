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

// Zutaten abfragen, die mit dem Suchbegriff übereinstimmen
$query = $_GET['query'];
$sql = "SELECT name FROM ingredients WHERE name LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$like_query = '%' . $query . '%';
$stmt->bind_param("s", $like_query);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="suggestion" onclick="selectIngredient(\'' . htmlspecialchars($row['name']) . '\')">' . htmlspecialchars($row['name']) . '</div>';
    }
} else {
    echo '<div class="suggestion">Keine Zutaten gefunden.</div>';
}

$stmt->close();
$conn->close();
?>