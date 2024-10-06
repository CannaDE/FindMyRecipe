<?php

$conn = new mysqli("localhost", "root", "", "rezept");

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Funktion zum Abrufen aller Zutaten mit Duplikaten
function getDuplicateIngredients($conn) {
    $sql = "SELECT name, GROUP_CONCAT(id) as ids, COUNT(*) as count
            FROM fmr_basic_ingredients
            GROUP BY name
            HAVING count > 1
            ORDER BY name";
    
    $result = $conn->query($sql);
    $duplicates = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $duplicates[] = $row;
        }
    }
    
    return $duplicates;
}

// Funktion zum Abrufen aller Zutaten für die Dropdown-Liste
function getAllIngredients($conn) {
    $sql = "SELECT id, name FROM fmr_basic_ingredients ORDER BY name";
    $result = $conn->query($sql);
    $ingredients = [];
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
    }
    
    return $ingredients;
}

// Ajax-Anfrage zum Zusammenführen von Zutaten
if (isset($_POST['action']) && $_POST['action'] == 'mergeIngredients') {
    $keep_id = $_POST['keep_id'];
    $remove_ids = explode(',', $_POST['remove_ids']);
    
    $conn->begin_transaction();
    
    try {
        // Aktualisiere die Rezept-Zutaten-Verknüpfungen
        $update_sql = "UPDATE fmr_recipe_ingredients SET ingredient_id = ? WHERE ingredient_id IN (" . implode(',', array_fill(0, count($remove_ids), '?')) . ")";
        $stmt = $conn->prepare($update_sql);
        $types = str_repeat('i', count($remove_ids) + 1);
        $params = array_merge([$types, $keep_id], $remove_ids);
        @call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();
        
        // Lösche die zusammengeführten Zutaten
        $delete_sql = "DELETE FROM fmr_basic_ingredients WHERE id IN (" . implode(',', array_fill(0, count($remove_ids), '?')) . ")";
        $stmt = $conn->prepare($delete_sql);
        $types = str_repeat('i', count($remove_ids));
        $params = array_merge([$types], $remove_ids);
        @call_user_func_array([$stmt, 'bind_param'], $params);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

$duplicates = getDuplicateIngredients($conn);
$all_ingredients = getAllIngredients($conn);
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Websites YAML Verwaltung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.5/mode/yaml/yaml.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-yaml/4.1.0/js-yaml.min.js"></script>
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
                <h1 class="text-3xl font-bold text-gray-800">Doppelte Zutaten zusammenführen</h1>
                <a href="index.php" class="text-blue-500 hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
            <div class="overflow-y-auto max-h-screen">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 sticky top-0">
                            <th class="text-left">Zutat</th>
                            <th class="text-left">Anzahl</th>
                            <th class="text-left">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($duplicates as $duplicate): ?>
                            <tr data-name="<?php echo htmlspecialchars($duplicate['name']); ?>">
                                <td class="p-2">
                                    <span class="ingredient-name">
                                        <?php echo htmlspecialchars($duplicate['name']); ?>
                                    </span>
                                </td>
                                <td class="p-2">
                                    <span class="ingredient-count">
                                        <?php echo $duplicate['count']; ?>
                                    </span>
                                </td>
                                <td class="p-2">
                                <select class="keep-select">
                                        <option value="">Zutat zum Behalten auswählen</option>
                                        <?php 
                                        $ids = explode(',', $duplicate['ids']);
                                        foreach ($ids as $id):
                                        ?>
                                            <option value="<?php echo $id; ?>"><?php echo htmlspecialchars($duplicate['name']) . ' (ID: ' . $id . ')'; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="merge-button bg-green-500 text-white px-2 py-1 rounded mr-2" disabled>Zusammenführen</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

    <script>
       $(document).ready(function() {
            $('.keep-select').change(function() {
                $(this).siblings('.merge-button').prop('disabled', !$(this).val());
            });

            $('.merge-button').click(function() {
                var row = $(this).closest('tr');
                var name = row.data('name');
                var keep_id = row.find('.keep-select').val();
                var all_ids = row.find('.keep-select option').map(function() {
                    return $(this).val();
                }).get().filter(Boolean);
                var remove_ids = all_ids.filter(id => id !== keep_id);

                if (confirm('Sind Sie sicher, dass Sie die Duplikate von "' + name + '" zusammenführen möchten?')) {
                    $.ajax({
                        url: 'duplicates_ingredients.php',
                        method: 'POST',
                        data: {
                            action: 'mergeIngredients',
                            keep_id: keep_id,
                            remove_ids: remove_ids.join(',')
                        },
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                showToast('Zutaten erfolgreich zusammengeführt');
                                row.remove();
                            } else {
                                showToast('Fehler: ' + result.error, false);
                            }
                        },
                        error: function() {
                            showToast('Ein Fehler ist aufgetreten', false);
                        }
                    });
                }
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