<!DOCTYPE html>
<html lang="de" data-color-sheme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{PAGE_TITLE}</title>

    <script>
        setTimeout(function(){
            let preloader = document.getElementById('preloader')
            if(preloader){literal}{preloader.classList.add('preloader-hide');}{/literal}
                },300);

        //Don't jump on Empty Links
        const emptyHref = document.querySelectorAll('a[href="#"]')
        emptyHref.forEach(el => el.addEventListener('click', e => {
            e.preventDefault();
            return false;
        }));
    </script>

    {js lib='require' bundle='TimeMonitoring.Core' core='true' hasTiny=false}
    {js file='require.config' bundle='TimeMonitoring.Core' core='true' hasTiny=false}
    {js file='require.linearExecution' bundle='TimeMonitoring.Core' core='true' hasTiny=false}

    <script>
    requirejs.config({
        baseUrl: 'js/',
        urlArgs: 't={@LAST_UPDATE_TIME}'
    });

    </script>

    <script data-relocate="true">
        __require_define_amd = define.amd;
        define.amd = undefined;
    </script>

    {js lib='jquery' hasTiny=true}
    {js lib='jquery-ui' hasTiny=false}
    {js lib='bootstrap' hasTiny=false}

    {literal}
    <script data-relocate="true">
        define.amd = __require_define_amd;
        $.holdReady(true);

        //Fix Scroll for AJAX pages.
        if ('scrollRestoration' in window.history) window.history.scrollRestoration = 'manual';

        require(['Bootstrap', 'Ui/Ingredients'], function(Bootstrap, Ingredients) {

            Bootstrap.setup();
            Ingredients.setup();

        });
    </script>
    {/literal}
    <link rel="stylesheet" href="style/main_style.css">
</head>
<body id="page">
    <div id="preloader"><div class="spinner-border color-highlight" role="status"></div></div>
    <div class="container">
        <!-- Linke Seite: Zutaten auswählen -->
        <div class="panel left-panel">
            <div class="header-image">
                <div class="header-text jsTooltip" data-tooltip="Zutaten wählen">Zutaten wählen</div>
            </div>
            <div class="content">
                <!-- Neues Input-Feld für Zutaten -->
                <input type="text" id="ingredientSearch" placeholder="Neue Zutat eingeben" onkeyup="" autocomplete="off">
                <div id="suggestions"></div>
                <div id="selectedIngredients"></div>
                <div class="alert info"><span class="fa fa-info-circle"></span>Wir gehen davon aus, dass du Salz und Pfeffer zu Hause hast.</div>

                <div id="selectedIngredients"></div>

                {assign var="ingredientCategoryCounter" value=0}
                {foreach from=$ingredients item=category key=key}
                <div class="basic-ingredients">
                        <h2>{$key}</h2>
                        <div class="ingredient-list" id="basicIngredientsList{$ingredientCategoryCounter}">
                            {foreach from=$category item=ingredient}
                                <div class="ingredient-button">{$ingredient}</div>
                            {/foreach}
                            <button class="showMoreBtn" >mehr</button>
                        </div>
                </div>
                {assign var="ingredientCategoryCounter" value=$ingredientCategoryCounter+1}
                {/foreach}
                
            </div> 
        </div>
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