<?php
header('Content-Type: application/json');

// Datenbankverbindung herstellen (Hier anpassen)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rezept";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Datenbankverbindung fehlgeschlagen."]);
    exit;
}

if (isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $email = $conn->real_escape_string($_POST['email']);

    // Überprüfen, ob die E-Mail bereits vorhanden ist
    $check_query = "SELECT * FROM subscribers WHERE email = '$email'";
    $result = $conn->query($check_query);

    if ($result->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Diese E-Mail ist bereits registriert."]);
    } else {
        // E-Mail in die Datenbank einfügen
        $insert_query = "INSERT INTO subscribers (email) VALUES ('$email')";

        if ($conn->query($insert_query) === TRUE) {
            echo json_encode(["success" => true, "message" => "E-Mail erfolgreich registriert."]);
        } else {
            echo json_encode(["success" => false, "message" => "Fehler beim Einfügen der E-Mail."]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Ungültige E-Mail-Adresse."]);
}

$conn->close();
?>
