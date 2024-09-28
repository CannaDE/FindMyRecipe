// AJAX Request for Email Submission
document.getElementById("notifyForm").addEventListener("submit", function (e) {
    e.preventDefault();
    console.log(e);
    const email = document.getElementById("email").value;

    if (email === "") {
        document.getElementById("message").innerText = "Bitte geben Sie eine gültige E-Mail-Adresse ein.";

        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.open("POST", "save_email.php", true);
    
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                document.getElementById("message").innerText = "Bitte bestätige nun deine E-Mailadresse! Wir haben dir dazu eine E-Mail gesendet.";
                document.getElementById("message").style.color = "green";
                document.getElementById("notifyForm").remove();
            } else {
                document.getElementById("message").innerText = response.message;
                document.getElementById("message").style.color = "red";
            }
        }
    };

    xhr.send("email=" + encodeURIComponent(email));
});

function changeImageRandomly() {
    // Zufallszahl zwischen 1 und 100
    const randomChance = Math.random() * 100;
    console.log(randomChance);
    // 1% Chance für ein alternatives Bild
    if (randomChance <= 10) {
        document.getElementsByClassName('image-container')[0].style.backgroundImage = "url(../images/searching.jpg)";
        alert('Glückwunsch, du hast den suchenden Dude gefunden! Leider muss er - so wie du - noch etwas warten :(')
    }
}

// Bild beim Laden der Seite ändern
window.onload = changeImageRandomly;