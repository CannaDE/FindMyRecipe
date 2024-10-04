define(["require", "exports"], function(require, exports) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.setup = void 0;

    let _ingredientList;
    function setup() {
        _ingredientList = document.querySelectorAll(".ingredient-list").forEach(list => {
            let ingredients = Array.from(list.querySelectorAll(".ingredient-button"));
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
});