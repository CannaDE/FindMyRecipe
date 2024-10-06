<?php
$logFilePath = __DIR__ . '/../cronjob/recipecrawler/crawler_log.txt';

// Funktion zum Laden der Log-Datei
function loadLogFile($filePath) {
    if (file_exists($filePath)) {
        return file_get_contents($filePath);
    }
    return '';
}

// Funktion zum Leeren der Log-Datei
function clearLogFile($filePath) {
    return file_put_contents($filePath, '');
}

// AJAX-Anfrage zum Laden der Log-Datei
if (isset($_GET['action']) && $_GET['action'] == 'loadLog') {
    echo json_encode(['content' => loadLogFile($logFilePath)]);
    exit;
}

// AJAX-Anfrage zum Leeren der Log-Datei
if (isset($_POST['action']) && $_POST['action'] == 'clearLog') {
    $success = clearLogFile($logFilePath);
    echo json_encode(['success' => $success !== false]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Datei Verwaltung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/yaml/yaml.min.js"></script>
    <style>
        .toast {
            visibility: hidden;
            max-width: 50%;
            margin: 0 auto;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 5px;
            padding: 16px;
            position: fixed;
            z-index: 1;
            left: 50%;
            bottom: 30px;
            font-size: 17px;
            transform: translateX(-50%);
        }

        .toast.show {
            visibility: visible;
            animation: fadein 0.5s, fadeout 0.5s 2.5s;
        }

        @keyframes fadein {
            from {bottom: 0; opacity: 0;}
            to {bottom: 30px; opacity: 1;}
        }

        @keyframes fadeout {
            from {bottom: 30px; opacity: 1;}
            to {bottom: 0; opacity: 0;}
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <div class="container mx-auto px-4 py-8 flex-grow flex flex-col h-screen">
        <div class="bg-white rounded-lg shadow-md p-6 flex-grow flex flex-col">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">crawler.log</h1>
                <a href="index.php" class="text-blue-500 hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
            <div class="flex-grow overflow-hidden">
                <textarea id="logEditor" class="w-full h-full border rounded"></textarea>
            </div>
            <button id="clearButton" class="mt-4 bg-red-500 text-white px-4 py-2 rounded">Leeren</button>
            <span id="statusMessage" class="text-red-500 font-bold mt-2" style="display: none;"></span>
        </div>
    </div>

    <script>
        var editor;

        $(document).ready(function() {
            // Initialisiere CodeMirror
            editor = CodeMirror.fromTextArea(document.getElementById('logEditor'), {
                mode: 'text',
                lineNumbers: true,
                theme: 'default',
                readOnly: true
            });

            // Setze die Größe von CodeMirror
            editor.setSize('100%', 'calc(100vh - 200px)'); // 200px für Header, Button und Padding

            // Lade die Log-Datei
            $.ajax({
                url: 'crawler_log.php',
                method: 'GET',
                data: { action: 'loadLog' },
                success: function(response) {
                    var result = JSON.parse(response);
                    editor.setValue(result.content);
                },
                error: function() {
                   showToast('Fehler beim Laden der Log-Datei', false);
                }
            });

            // Leeren der Log-Datei
            $('#clearButton').click(function() {
                $.ajax({
                    url: 'crawler_log.php',
                    method: 'POST',
                    data: { action: 'clearLog' },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            editor.setValue('');
                            showToast('crawler_log.txt geleert', true);
                        } else {
                            showToast('Fehler beim Leeren der Log-Datei', false);
                        }
                    },
                    error: function() {
                        showToast('Ein Fehler ist aufgetreten', false);
                    }
                });
            });
        });

        function showToast(message, isSuccess = true) {
                var toast = document.createElement('div');
                toast.className = 'toast';
                toast.style.backgroundColor = isSuccess ? '#4CAF50' : '#f44336'; // Grün für Erfolg, Rot für Fehler
                toast.innerText = message;
                document.body.appendChild(toast);

                toast.className += ' show';
                setTimeout(function() {
                    toast.className = toast.className.replace(' show', '');
                    document.body.removeChild(toast);
                }, 3000);
            }
    </script>
</body>
</html>