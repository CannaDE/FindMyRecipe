<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zutaten auswählen und Ergebnisse anzeigen</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            height: 100%;
            background-color: #f4f4f4;
            overflow: hidden; /* Verhindert Scrollbars auf Body-Ebene */
        }

        /* Container für Inhalt */
        .container {
            display: flex;
            justify-content: space-between;
            padding: 20px;
            height: 100vh;
            max-width: 100vw;
            box-sizing: border-box;
        }

        .panel {
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            height: 100vh; /* Höhe des Containers anpassen */
        }

        .content {
            overflow-y: auto; /* Scrollbare Inhalte */
            scrollbar-width: thin; /* Dünne Scrollbars in Firefox */
            scrollbar-color: #888 #f4f4f4; /* Scrollbar-Farbe in Firefox */
            height: 100vh; /* Höhe der Panels anpassen */
            padding: 20px;
        }

        /* Styled Scrollbars für Chrome, Edge und Safari */
        .content::-webkit-scrollbar {
            width: 10px;
        }

        .content::-webkit-scrollbar-track {
            background: #f4f4f4;
        }

        .content::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 5px;
            border: 3px solid #f4f4f4;
        }

        .left-panel {
            flex: 1;
            display: flex;
            margin-right: 20px;
            max-height: 100%;
            flex-direction: column;
            height: 100%;
        }

        .right-panel {
            flex: 2;
            display: flex;
            margin-right: 20px;
            max-height: 100%;
            flex-direction: column;
            height: 100%;
        }

        h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }

        .ingredient-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .ingredient-list li {
            margin-bottom: 10px;
        }

        .ingredient-list label {
            font-size: 18px;
            cursor: pointer;
            color: #555;
        }

        .ingredient-list input[type="checkbox"] {
            margin-right: 10px;
        }

        .results {
            display: flex;
            flex-wrap: wrap; /* Erlaubt Zeilenumbruch */
            gap: 20px; /* Abstand zwischen den Rezepten */
            margin-top: 20px; /* Abstand zum Titel */
            margin-left: 20px;
            margin-right: 20px;
        }
        .results .noResults {
            text-align: center;
            width: 100%;
            margin-top: 25%;
        }

        .recipe {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex; /* Flexbox für quadratische Anordnung */
            overflow: hidden; /* Verhindert Überlappungen */
            width: calc(50% - 10px); /* 2 Rezepte pro Zeile mit Abstand */
            max-height: 160px; /* Maximale Höhe des Rezepts */
        }

        .recipe img {
            width: 150px; /* Feste Breite für das Bild */
            height: 150px; /* Feste Höhe für das Bild */
            object-fit: cover; /* Bild passt in den Container */
        }

        .recipe-content {
            padding: 15px; /* Abstand um den Text */
            display: flex;
            flex-direction: column; /* Textinhalt untereinander */
            justify-content: center; /* Zentriert den Inhalt */
            flex: 1; /* Füllt den verfügbaren Platz */
        }

        .recipe h2 {
            font-size: 20px;
            margin: 10px 0;
            color: #2C3E50;
        }

        .recipe p {
            font-size: 16px;
            color: #666;
            margin-bottom: 10px;
        }

        .recipe .source {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }

        .recipe a {
            display: inline-block;
            background-color: #28a745;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }

        .recipe a:hover {
            background-color: #218838;
        }

        .header-image {
            position: sticky;
            flex-shrink: 0;
            height: 60px;
            background-image: url('../images/cooking.jpg');
            background-size: cover;
            background-position: center;
            top: 0;
            z-index: 1;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .header-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #fff;
            font-size: 25px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .left-panel {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .right-panel {
                width: 100%;
            }

            .recipe {
                width: calc(100% - 10px); /* Vollbreite für mobile Ansicht */
            }
        }

        input[type="text"] {
            margin-bottom: 20px;
            padding: 10px;
            width: calc(100% - 40px);
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:hover {
            border-color: #888;
        }

        input[type="text"]:focus {
            border-color: #28a745;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
            outline: none;
        }

        #suggestions {
            
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            position: relative;
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
            margin-top: -20px;
        }

        #suggestions div {
            padding: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #suggestions div:hover {
            background-color: #f4f4f4;
        }

        /* Styled Scrollbars für Chrome, Edge und Safari */
        #suggestions::-webkit-scrollbar {
            width: 8px;
        }

        #suggestions::-webkit-scrollbar-track {
            background: #f4f4f4;
            border-radius: 5px;
        }

        #suggestions::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 5px;
            border: 2px solid #f4f4f4;
        }

        /* Scrollbar für Firefox */
        #suggestions {
            scrollbar-width: thin;
            scrollbar-color: #28a745 #f4f4f4;
        }

        #selectedIngredients {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }

        .selected-ingredient {
            padding: 10px 15px;
            background-color: #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
        }

        .selected-ingredient.active {
            background-color: #28a745;
            color: #fff;
        }

        .selected-ingredient:hover {
            background-color: #d4d4d4;
        }

        .basic-ingredients {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            border: 1px solid #f5f5f5;
        }

        .basic-ingredients h2 {
            font-size: 22px;
            margin-bottom: 10px;
            color: #333;
            display: flex;
            align-items: center;
            padding-left: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #cfcfcf;
        }

        .basic-ingredients h2::before {
            content: attr(data-emoji);
            margin-right: 10px;
            font-size: 24px;
        }

        .basic-ingredients .ingredient-button {
            padding: 10px 15px;
            background-color: #e0e0e0;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            margin-right: 10px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .basic-ingredients .ingredient-button.active {
            background-color: #28a745;
            color: #fff;
        }

        .basic-ingredients .ingredient-button:hover {
            background-color: #d4d4d4;
        }

        .info-box {
            padding: 15px;
            background-color: #e9f7f6;
            border-left: 4px solid #4BB5C1;
            margin-bottom: 10px;
            margin-top: 20px;
        }


        .basic-ingredients {
            margin-bottom: 20px;
        }

        .ingredient-list {
            padding: 10px;
        }

    .showMoreBtn {
        padding: 10px 15px;
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-top: 10px;
        display: inline-block;
    }

    .showMoreBtn:hover {
        background-color: #218838;   
    }
    </style>
</head>
<body>
    <div class="container">
        <!-- Linke Seite: Zutaten auswählen -->
        <div class="panel left-panel">
            <div class="header-image">
                <div class="header-text">Zutaten wählen</div>
            </div>
            <div class="content">
                <!-- Neues Input-Feld für Zutaten -->
                <input type="text" id="ingredientSearch" placeholder="Neue Zutat eingeben" onkeyup="" autocomplete="off">
                <div id="suggestions"></div>

                <div class="info-box">Wir gehen davon aus, dass du Salz und Pfeffer zu Hause hast.</div>

                {assign var="ingredientCategoryCounter" value=0}
                {foreach from=$ingredients item=category key=key}
                <div class="basic-ingredients">
                        <h2>{$key}</h2>
                        <div class="ingredient-list" id="basicIngredientsList{$ingredientCategoryCounter}">
                            {foreach from=$category item=ingredient}
                                <div class="ingredient-button">{$ingredient}</div>
                            {/foreach}
                            <button class="showMoreBtn" onclick="toggleIngredients('basicIngredientsList{$ingredientCategoryCounter}', this)">mehr</button>
                        </div>
                </div>
                {assign var="ingredientCategoryCounter" value=$ingredientCategoryCounter+1}
                {/foreach}
                <div id="selectedIngredients"></div>
                <div class="basic-ingredients">
                    
                    <div class="ingredient-list" id="basicIngredientsList1">
                        <div class="ingredient-button">Tomaten</div>
                        <div class="ingredient-button">Paprika</div>
                        <div class="ingredient-button">Gurken</div>
                        <div class="ingredient-button">Brokkoli</div>
                        <div class="ingredient-button">Blumenkohl</div>
                        <div class="ingredient-button">Spinat</div>
                        <div class="ingredient-button">Zucchini</div>
                        <div class="ingredient-button">Kürbis</div>
                        <div class="ingredient-button">Sellerie</div>
                        <div class="ingredient-button">Lauch</div>
                        <div class="ingredient-button">Aubergine</div>
                        <div class="ingredient-button">Champignons</div>
                        <div class="ingredient-button">Erbsen</div>
                        <div class="ingredient-button">Bohnen</div>
                        <div class="ingredient-button">Mais</div>
                        <div class="ingredient-button">Süßkartoffeln</div>
                        <div class="ingredient-button">Rote Beete</div>
                        <div class="ingredient-button">Spargel</div>
                        <div class="ingredient-button">Rosenkohl</div>
                        <div class="ingredient-button">Kohlrabi</div>
                        <div class="ingredient-button">Fenchel</div>
                        <div class="ingredient-button">Radieschen</div>
                        <div class="ingredient-button">Rucola</div>
                        <div class="ingredient-button">Mangold</div>
                    </div>
                    
                </div>
                <div class="basic-ingredients">
                    <h2 data-emoji=""> Obst</h2>
                    <div class="ingredient-list" id="basicIngredientsList2">
                        <div class="ingredient-button">Äpfel</div>
                        <div class="ingredient-button">Bananen</div>
                        <div class="ingredient-button">Orangen</div>
                        <div class="ingredient-button">Zitronen</div>
                        <div class="ingredient-button">Erdbeeren</div>
                        <div class="ingredient-button">Himbeeren</div>
                        <div class="ingredient-button">Blaubeeren</div>
                        <div class="ingredient-button">Kirschen</div>
                        <div class="ingredient-button">Trauben</div>
                        <div class="ingredient-button">Pfirsiche</div>
                        <div class="ingredient-button">Birnen</div>
                        <div class="ingredient-button">Ananas</div>
                        <div class="ingredient-button">Mangos</div>
                        <div class="ingredient-button">Melonen</div>
                        <div class="ingredient-button">Kiwis</div>
                        <div class="ingredient-button">Pflaumen</div>
                        <div class="ingredient-button">Aprikosen</div>
                        <div class="ingredient-button">Granatäpfel</div>
                        <div class="ingredient-button">Feigen</div>
                        <div class="ingredient-button">Papaya</div>
                        <div class="ingredient-button">Maracuja</div>
                        <div class="ingredient-button">Litschi</div>
                        <div class="ingredient-button">Guave</div>
                        <div class="ingredient-button">Johannisbeeren</div>
                        <div class="ingredient-button">Stachelbeeren</div>
                        <div class="ingredient-button">Brombeeren</div>
                        <button class="showMoreBtn" onclick="toggleIngredients('basicIngredientsList2', this)">mehr</button>
                    </div>
                </div>
            </div> 
        </div>

        <script>
    const maxVisibleIngredients = 10;

    function toggleIngredients(listId, button) {
        const ingredientList = document.getElementById(listId);
        const ingredients = Array.from(ingredientList.querySelectorAll('.ingredient-button'));
        const showMoreBtn = ingredientList.querySelector('.showMoreBtn');
        let isExpanded = showMoreBtn.getAttribute('data-expanded') === 'true';

        if (isExpanded) {
            maxVisibles = Math.floor(Math.random() * (14 - 8 + 1)) + 8;
            ingredients.forEach((ingredient, index) => {
                if (index >= maxVisibles) {
                    ingredient.style.display = 'none';
                }
            });
            showMoreBtn.innerText = 'mehr';
        } else {
            ingredients.forEach(ingredient => {
                ingredient.style.display = 'inline-block';
            });
            showMoreBtn.innerText = 'weniger';
        }
        showMoreBtn.setAttribute('data-expanded', !isExpanded);
    }
    document.addEventListener('DOMContentLoaded', function() {
        const ingredientLists = document.querySelectorAll('.ingredient-list');
        ingredientLists.forEach(list => {
            const ingredients = Array.from(list.querySelectorAll('.ingredient-button'));
            maxVisibles = Math.floor(Math.random() * (14 - 8 + 1)) + 8;
            ingredients.forEach((ingredient, index) => {
                if (index >= maxVisibles) {
                    ingredient.style.display = 'none';
                }
            });
        });
    });
</script>

        <!-- Rechte Seite: Ergebnisse -->
        <div class="panel right-panel">
            <div class="header-image">
                <div class="header-text">Rezeptergebnisse</div>
            </div>
            <div class="content">
                <div class="results" id="recipeResults">
                    <p class="noResults">Keine Zutaten ausgewählt.<br/>
                    <small>Wähle jetzt Zutaten aus und erhalte ausgwählte Rezepte.</small></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedIngredients = [];

        window.onload = function() {
            document.querySelectorAll('.ingredient-button').forEach(button => {
                button.addEventListener('click', () => toggleIngredient(button, button.innerText));
            });
        }

        function toggleIngredient(element, ingredient) {
            if (selectedIngredients.includes(ingredient)) {
                selectedIngredients = selectedIngredients.filter(item => item !== ingredient);
                element.classList.remove('active');
            } else {
                selectedIngredients.push(ingredient);
                element.classList.add('active');
            }
            console.log(selectedIngredients);
            getRecipes();
        }
        
        function fetchSuggestions() {
            const input = document.getElementById('ingredientSearch').value;
        
            if (input.length < 1) {
                document.getElementById('suggestions').innerHTML = '';
                return;
            }
        
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/dev/fetch_ingredients.php?query=' + encodeURIComponent(input), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    document.getElementById('suggestions').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
        
        function selectIngredient(ingredient) {
            if (!selectedIngredients.includes(ingredient)) {
                selectedIngredients.push(ingredient);
                updateSelectedIngredients();
            }
            document.getElementById('ingredientSearch').value = '';
            document.getElementById('suggestions').innerHTML = '';
        }
        
        function updateSelectedIngredients() {
            const container = document.getElementById('selectedIngredients');
            container.innerHTML = '';
        
            selectedIngredients.forEach(ingredient => {
                const ingredientDiv = document.createElement('div');
                ingredientDiv.className = 'selected-ingredient';
                ingredientDiv.innerText = ingredient;
                ingredientDiv.onclick = () => removeIngredient(ingredient);
                container.appendChild(ingredientDiv);
            });
        }
        
        function removeIngredient(ingredient) {
            selectedIngredients = selectedIngredients.filter(i => i !== ingredredient);
            updateSelectedIngredients();
        }

        function getRecipes() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', '/dev/fetch_recipes.php?ingredients=' + encodeURIComponent(JSON.stringify(selectedIngredients)), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    response = JSON.parse(xhr.responseText);
                    document.getElementById('recipeResults').innerHTML = "";
                    if(response['recipes'] !== null && response['recipes'].length > 0) {
                        for(let i = 0; i < response['recipes'].length; i++) {
                            let recipe = document.createElement('div');
                            recipe.classList.add('recipe');
                            let img = document.createElement('img');
                            img.src = response['recipes'][i]['image_url'];
                            recipe.appendChild(img);
                            let recipeContent = document.createElement('div');
                            recipeContent.classList.add('recipe-content');
                            let h2 = document.createElement('h2');
                            h2.innerText = response['recipes'][i]['title'];
                            recipeContent.appendChild(h2);
                            let p = document.createElement('p');
                            p.innerText = response['recipes'][i]['description'];
                            recipeContent.appendChild(p);
                            let source = document.createElement('div');
                            source.classList.add('source');
                            source.innerText = 'Quelle: ' + response['recipes'][i]['source_url'].replace('https://', '');
                            recipeContent.appendChild(source);
                            let a = document.createElement('a');
                            a.href = response['recipes'][i]['url'];
                            a.innerText = 'Zum Rezept';
                            recipeContent.appendChild(a);
                            recipe.appendChild(recipeContent);
                            document.getElementById('recipeResults').appendChild(recipe);
                        }
                    } else {
                        let p = document.createElement('p');
                        p.classList.add('noResults');
                        p.innerHTML = 'Keine Zutaten ausgewählt.<br/><small>Wähle jetzt Zutaten aus und erhalte ausgwählte Rezepte.</small>';
                        document.getElementById('recipeResults').appendChild(p);
                    }
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>