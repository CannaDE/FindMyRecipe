<?php
$servername = "localhost";
$username = "root";
$password = ""; // Dein MySQL-Passwort
$dbname = "rezept"; // Deine Datenbank

// Verbindung zur MySQL-Datenbank herstellen
$conn = new mysqli($servername, $username, $password, $dbname);

// Überprüfen, ob die Verbindung erfolgreich war
if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Abfrage, um 40 Rezepte abzurufen
$sql = "SELECT r.id, r.title, r.description, r.url 
        FROM recipes r 
        LIMIT 40";

$result = $conn->query($sql);

// Überprüfen, ob Rezepte gefunden wurden
if ($result->num_rows > 0) {
    // Ausgabe der Rezepte
    while ($row = $result->fetch_assoc()) {
        echo "<h2>" . htmlspecialchars($row["title"]) . "</h2>";
        echo "<p>" . htmlspecialchars($row["description"]) . "</p>";
        echo "<a href='" . htmlspecialchars($row["url"]) . "'>Rezept anzeigen</a>";

        // Zutaten für das aktuelle Rezept abrufen
        $recipe_id = $row["id"];
        $sql_ingredients = "SELECT i.name, ri.quantity 
                            FROM recipe_ingredients ri 
                            JOIN ingredients i ON ri.ingredient_id = i.id 
                            WHERE ri.recipe_id = ?";
        
        $stmt = $conn->prepare($sql_ingredients);
        $stmt->bind_param("i", $recipe_id);
        $stmt->execute();
        $ingredient_result = $stmt->get_result();

        if ($ingredient_result->num_rows > 0) {
            echo "<h3>Zutaten:</h3><ul>";
            while ($ingredient_row = $ingredient_result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($ingredient_row["quantity"]) . " " . htmlspecialchars($ingredient_row["name"]) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Keine Zutaten gefunden.</p>";
        }

        echo "<hr>"; // Trenner zwischen Rezepten
    }
} else {
    echo "Keine Rezepte gefunden.";
}

// Verbindung schließen
$conn->close();
?>
