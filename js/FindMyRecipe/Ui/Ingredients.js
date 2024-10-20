define(["require", "exports", "Ajax", "Ui/Overlay"], function (require, exports, Ajax, UiOverlay) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;


    let _selectedIngredients = [];

    function setup() {
        document.querySelectorAll(".ingredient-list").forEach(list => {
            let ingredients_btn = list.querySelectorAll(".ingredient-button");
            ingredients_btn.forEach(btn => {
                btn.addEventListener("click", () => {
                    if(_selectedIngredients.includes(btn.innerText)) {
                        removeIngredient(btn.innerText);
                        btn.classList.remove("active");
                    } else {
                        addIngredient(btn.innerText);
                        btn.classList.add("active");
                    }
                })
            })
            let ingredients = Array.from(ingredients_btn);
            let maxVisibles = Math.floor(Math.random() * (14 - 8 + 1)) + 8;
            let disableIngredients = 0;
            ingredients.forEach((ingredient, index) => {
                if(index >= maxVisibles) {
                    disableIngredients++;
                    ingredient.style.display = "none";
                }
            })

            let _btn = list.querySelector(".showMoreBtn");
            _btn.dataset.listId = list.id;
            _btn.innerText = "+" + disableIngredients;
            _btn.addEventListener("click", (event) => {
                const btn = event.currentTarget;
                _toggleIngredients(btn.dataset.listId);
            });
        })
        loadSelectedIngredients();
        getRecipes();
    }
    exports.setup = setup;

    function _toggleIngredients(listId) {
        let list = document.getElementById(listId);
        let ingredients = Array.from(list.querySelectorAll(".ingredient-button"));
        let showMoreBtn = list.querySelector(".showMoreBtn");
        let isExpanded = showMoreBtn.getAttribute("data-expanded") === "true";

        if(isExpanded) {
            let maxVisibles = Math.floor(Math.random() * (14 - 8 + 1)) + 8;
            let disableIngredients = 0;
            ingredients.forEach((ingredient, index) => {
                if(index >= maxVisibles) {
                    disableIngredients++;
                    ingredient.style.display = "none";
                }
            })
            showMoreBtn.innerText = "+" + disableIngredients;
        }
        else {
            ingredients.forEach(ingredient => {
                ingredient.style.display = "inline-block";
            });
            showMoreBtn.innerText = "weniger";
        }
        showMoreBtn.setAttribute("data-expanded", !isExpanded);
    }

    function updateSelectedIngredients() {
        const container = document.getElementById("selectedIngredients");
        container.innerHTML = "";

        _selectedIngredients.forEach(ingredient => {
            const ingredientDiv = document.createElement('div');
            ingredientDiv.className = 'selected-ingredient';
            ingredientDiv.innerText = ingredient;
            ingredientDiv.onclick = () => removeIngredient(ingredient);
            container.appendChild(ingredientDiv);
            document.querySelectorAll('.ingredient-button').forEach(element => {
                if (element.innerText === ingredient) {
                    element.classList.add("active");
                }
            })
        })
    }

    function addIngredient(ingredient) {
        _selectedIngredients.push(ingredient);
        document.querySelectorAll('.ingredient-button').forEach(element => {
            if (element.innerText === ingredient) {
                element.classList.add("active");
            }
        })
        saveSelectedIngredients();
        updateSelectedIngredients();

        getRecipes();
    }

    function getRecipes() {
        const ingredients = _selectedIngredients.join(",");
        Ajax.apiOnce({
            data: {
                actionName: "getRecipes",
                className: "fmr\\RecipeFinder",
                objectIds: [ingredients],
            },
            success: function (data) {
                let response = data['returnValues'][0];
                const container = document.getElementById("recipeResults");
                container.innerHTML = "";

                const recipeContainer = document.createElement('div');
                recipeContainer.className = 'recipe-container';

                let matchedTitle = document.querySelectorAll('.founded')[0];
                if (!matchedTitle) {
                    matchedTitle = document.createElement('h2');
                    matchedTitle.className = 'founded';
                }
                matchedTitle.innerText = "Du kannst " + response.length + " Rezepte kochen";

                const content = container.closest('.content');

                let pageOverlay = document.querySelectorAll(".dialogPageOverlay")[0];
                pageOverlay.innerHTML = "";

                response.forEach(recipe => {
                    let recipeDiv = document.createElement('div');
                    recipeDiv.classList.add("recipe");
                    recipeDiv.dataset.recipeTitle = recipe.title;
                    recipeDiv.dataset.recipeId = recipe.id;
                    recipeDiv.addEventListener("click", (element) => {
                        showRecipeCard(recipe.id);
                    })

                    let img = document.createElement('img');
                    img.src = recipe.image
                    img.alt = recipe.title;
                    img.classList.add("recipe-image");
                    recipeDiv.appendChild(img);

                    let recipeContent = document.createElement('div');
                    recipeContent.classList.add("recipe-content");
                    recipeDiv.appendChild(recipeContent);

                    let title = document.createElement('h2');
                    title.innerText = recipe.title;
                    title.classList.add("color-brand-orange");
                    recipeContent.appendChild(title);

                    let source = document.createElement('small');
                    source.innerText = "Quelle: " + getDomainFromUrl(recipe.url);
                    source.classList.add("source");
                    recipeContent.appendChild(source);

                    source = document.createElement('small');
                    source.innerText = "Du hast alle " + recipe.ingredients.length + " Zutaten";
                    source.classList.add("source");
                    recipeContent.appendChild(source);

                    container.appendChild(recipeDiv);
                    getRecipeCardOverlay(recipe);

                })

                let hr = content.querySelectorAll('.divider')[0]
                if (!hr) {
                    hr = document.createElement('div');
                    hr.classList.add("divider");
                }
                content.insertBefore(matchedTitle, container);


                getMissedIngredientRecipes(data['returnValues'][1], container);
            }
        })
    }

    function showRecipeCard(recipeId) {
        let pageOverlay = document.querySelectorAll(".dialogPageOverlay")[0];
        pageOverlay.ariaHidden = "false";
        pageOverlay.addEventListener("click", (event) => {
            event.stopPropagation();
            hideRecipeCard(recipeId);
        })

        pageOverlay.querySelectorAll(".recipeCardOverlay").forEach(element => {
            element.addEventListener("click", (event) => {
                event.stopPropagation();
            })
            if (element.dataset.recipeId === recipeId) {
                element.dataset.ariaHidden = "false";
            }
        })
    }

    function hideRecipeCard(recipeId) {
        let pageOverlay = document.querySelectorAll(".dialogPageOverlay")[0];
        pageOverlay.ariaHidden = "true";
        pageOverlay.querySelectorAll(".recipeCardOverlay").forEach(element => {
            if (element.dataset.recipeId === recipeId) {
                element.dataset.ariaHidden = "true";
            }
        })
    }

    function getMissedIngredientRecipes(missingIngredients, container) {
        // 1 Fehlende Zutat
        let recipeDiv;
        missingIngredients.forEach(recipe => {
            recipeDiv = document.createElement('div');
            recipeDiv.classList.add("recipe");
            recipeDiv.dataset.recipeTitle = recipe.recipe.title;
            recipeDiv.dataset.recipeId = recipe.recipe.id;
            recipeDiv.addEventListener("click", (element) => {
                showRecipeCard(recipe.recipe.id);
            })

            let img = document.createElement('img');
            img.src = recipe.recipe.image
            img.alt = recipe.recipe.title;
            img.classList.add("recipe-image");
            recipeDiv.appendChild(img);

            let recipeContent = document.createElement('div');
            recipeContent.classList.add("recipe-content");
            recipeDiv.appendChild(recipeContent);

            let title = document.createElement('h2');
            title.innerHTML = recipe.recipe.title;
            title.classList.add("color-brand-orange");
            recipeContent.appendChild(title);

            let source = document.createElement('small');
            source.innerText = "Quelle: " + getDomainFromUrl(recipe.recipe.url);
            source.classList.add("source");
            recipeContent.appendChild(source);

            source = document.createElement('small');
            source.classList.add("text-color-red", "text-small");
            source.innerText = "Dir fehlt " + recipe.missingIngredients;
            source.classList.add("source");
            recipeContent.appendChild(source);

            getRecipeCardOverlay(recipe.recipe);

            //let link = document.createElement('a');
            // link.href = recipe.recipe.url;
            // link.innerText = "Zum Rezept";
            // link.classList.add("recipe-link");
            //  recipeContent.appendChild(link);

            container.appendChild(recipeDiv);
            //missingIngredientContainer.appendChild(recipeDiv);
        })
        return recipeDiv;
    }

    function getRecipeCardOverlay(recipe) {
        let pageOverlay = document.querySelectorAll(".dialogPageOverlay")[0]

        let overlay = document.createElement('div');
        overlay.classList.add("recipeCardOverlay");
        overlay.dataset.ariaHidden = "true";
        overlay.dataset.recipeId = recipe.id;
        overlay.role = "card";

        let img = document.createElement('img');
        img.src = recipe.image;
        img.alt = recipe.title;
        img.classList.add("recipe-image");

        let imgMenue = document.createElement('div');
        imgMenue.classList.add("imgMenue");
        let close = document.createElement('div');
        close.textContent = "x"
        close.onclick = () => hideRecipeCard(recipe.id);
        imgMenue.appendChild(close);

        overlay.appendChild(img);
        overlay.appendChild(imgMenue);
        pageOverlay.appendChild(overlay);
        return overlay;
    }

    function removeIngredient(ingredient) {
        _selectedIngredients = _selectedIngredients.filter(item => item !== ingredient);
        document.querySelectorAll('.ingredient-button').forEach(btn => {
            if (btn.innerText === ingredient) {
                btn.classList.remove("active");
            }
        })
        saveSelectedIngredients();
        updateSelectedIngredients();
        getRecipes();
    }

    function saveSelectedIngredients() {
        localStorage.setItem("selectedIngredients", JSON.stringify(_selectedIngredients));
    }

    function loadSelectedIngredients() {
        const storedIngredients = localStorage.getItem("selectedIngredients");
        if(storedIngredients) {
            _selectedIngredients = JSON.parse(storedIngredients);
            updateSelectedIngredients();
        }
    }

    function getDomainFromUrl(url) {
        try {
            const {hostname} = new URL(url);
            const parts = hostname.split('.');
            if (parts.length > 2) {
                parts.shift();
            }
            return parts.join('.');
        } catch (e) {
            console.error('Invalid URL:', e);
            return null;
        }
    }
});