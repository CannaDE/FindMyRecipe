{include file="header"}
<div class="container">
        <div class="image-container {if MAINTENANCE_MODE}maintenance{else}error{/if} "></div>
        <div class="content-container">
            <h1 class="page-title">{PAGE_TITLE}</h1>
            <div class="divider"></div>
            {if MAINTENANCE_MODE}
                <div class="">{@$message}</div>
            
            {else}<div class="error-box">{@$message}</div>{/if}
             <footer>
                &copy; 2024 Finde-Mein-Rezept.de
            </footer>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let inputSequence = '';
            const secretCodes = {
                'larsstinkt': function() {
                    document.querySelectorAll('.page-title')[0].innerHTML = "Entschuldige, was soll das? 🧐";
                    document.querySelectorAll('.page-title')[0].style.fontSize = "25px";
                    document.querySelectorAll('.image-container')[0].style.backgroundImage = "url('../images/lars.png')";
                    document.querySelectorAll('.image-container')[0].style.backgroundSize = "auto";
                },
                'lightmode': function() {
                    const htmlElement = document.documentElement;
                    htmlElement.dataset.colorSheme = "light";
                },
                'darkmode': function() {
                    const htmlElement = document.documentElement;
                    htmlElement.dataset.colorSheme = "dark";
                },
                'rezeptmeister': () => document.body.style.backgroundColor = 'lightgreen'
            };

            document.addEventListener('keydown', function(event) {
                inputSequence += event.key.toLowerCase();

                for (const [code, action] of Object.entries(secretCodes)) {
                    if (inputSequence.includes(code)) {
                        action();
                        inputSequence = '';
                        break;
                    }
                }

                const maxCodeLength = Math.max(...Object.keys(secretCodes).map(code => code.length));
                if (inputSequence.length > maxCodeLength) {
                    inputSequence = inputSequence.slice(-maxCodeLength);
                }
            });
        });
    </script>
</body>
</html>