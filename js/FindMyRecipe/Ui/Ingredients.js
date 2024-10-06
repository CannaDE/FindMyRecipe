define(["require", "exports"], function(require, exports) {
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
});