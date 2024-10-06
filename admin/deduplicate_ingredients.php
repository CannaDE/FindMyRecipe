<?php
// Datenbankverbindung herstellen (passen Sie die Zugangsdaten an)
$conn = new mysqli("localhost", "root", "", "rezept");

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Funktion zum Abrufen aller Zutaten
function getAllIngredients($conn, $offset = 0, $limit = 50, $search = '', $sort = 'name', $order = 'ASC') {
    $search = $conn->real_escape_string($search);
    $sort = $conn->real_escape_string($sort);
    $order = $conn->real_escape_string($order);
    
    $sql = "SELECT id, name FROM fmr_basic_ingredients 
            WHERE name LIKE '%$search%'
            ORDER BY $sort $order 
            LIMIT ?, ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $ingredients = [];
    
    while($row = $result->fetch_assoc()) {
        $ingredients[] = $row;
    }
    
    return $ingredients;
}

// Funktion zum Zählen aller Zutaten
function countAllIngredients($conn, $search = '') {
    $search = $conn->real_escape_string($search);
    $sql = "SELECT COUNT(*) as count FROM fmr_basic_ingredients WHERE name LIKE '%$search%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Ajax-Anfrage zum Nachladen von Zutaten
if (isset($_GET['action']) && $_GET['action'] == 'loadMoreIngredients') {
    $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $ingredients = getAllIngredients($conn, $offset, 50, $search, $sort, $order);
    echo json_encode($ingredients);
    exit;
}

// Ajax-Anfrage zum Zusammenführen von Zutaten
if (isset($_POST['action']) && $_POST['action'] == 'mergeIngredients') {
    $keep_id = $_POST['keep_id'];
    $remove_id = $_POST['remove_id'];
    
    $conn->begin_transaction();
    
    try {
        // Aktualisiere die Rezept-Zutaten-Verknüpfungen
        $update_sql = "UPDATE fmr_recipe_ingredients SET ingredient_id = ? WHERE ingredient_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ii", $keep_id, $remove_id);
        $stmt->execute();
        
        // Lösche die zusammengeführte Zutat
        $delete_sql = "DELETE FROM fmr_basic_ingredients WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $remove_id);
        $stmt->execute();
        
        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    
    exit;
}

// Ajax-Anfrage zum Aktualisieren des Zutatennamen
if (isset($_POST['action']) && $_POST['action'] == 'updateIngredientName') {
    $ingredient_id = $_POST['ingredient_id'];
    $new_name = $_POST['new_name'];
    
    $sql = "UPDATE fmr_basic_ingredients SET name = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_name, $ingredient_id);
    $result = $stmt->execute();
    
    echo json_encode(['success' => $result]);
    exit;
}

$initialIngredients = getAllIngredients($conn);
$totalIngredients = countAllIngredients($conn);
?>

<?php
// PHP-Code bleibt unverändert
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zutaten Deduplizierung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

        .ingredient-name {
            cursor: pointer;
        }

        .ingredient-name:hover {
            text-decoration: underline;
        }

        .merge-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Anpassung der Select2-Breite */
        .select2-container {
            width: 350px !important;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Zutaten Deduplizierung</h1>
                <a href="index.php" class="text-blue-500 hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
            </div>
            <div class="flex justify-between mb-4">
                <input type="text" id="searchInput" placeholder="Suche nach Zutaten..." class="w-1/3 p-2 border rounded">
                <select id="sortSelect" class="w-1/3 p-2 border rounded ml-2">
                    <option value="name">Name (A-Z)</option>
                    <option value="name DESC">Name (Z-A)</option>
                    <option value="id">ID (aufsteigend)</option>
                    <option value="id DESC">ID (absteigend)</option>
                </select>
            </div>
            <div class="overflow-x-auto">
                <table id="ingredientsTable" class="w-full">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="p-2 text-left">ID</th>
                            <th class="p-2 text-left">Zutat</th>
                            <th class="p-2 text-left">Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($initialIngredients as $ingredient): ?>
                            <tr data-id="<?php echo $ingredient['id']; ?>" class="border-b">
                                <td class="p-2"><?php echo $ingredient['id']; ?></td>
                                <td class="p-2">
                                    <span class="ingredient-name" data-id="<?php echo $ingredient['id']; ?>">
                                        <?php echo htmlspecialchars(trim($ingredient['name'])); ?>
                                    </span>
                                </td>
                                <td class="p-2">
                                    <div class="flex items-center">
                                        <select class="ingredient-select mr-2">
                                            <option value="">Zutat zum Zusammenführen auswählen</option>
                                        </select>
                                        <button class="merge-button bg-green-500 text-white px-2 py-1 rounded mr-2 ml-2" disabled>Zusammenführen</button>
                                    </div>
                                    <span class="error text-red-500 font-bold mt-1" style="display: none;"></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-4">
                <button id="loadMore" class="bg-blue-500 text-white px-4 py-2 rounded">Mehr laden</button>
            </div>
        </div>
    </div>

    <script src="modal.js"></script>
    <script>
    $(document).ready(function() {
        var offset = <?php echo count($initialIngredients); ?>;
        var totalIngredients = <?php echo $totalIngredients; ?>;
        var currentSearch = '';
        var currentSort = 'name';
        var currentOrder = 'ASC';
        var isLoading = false;

        function initSelect2() {
            $('.ingredient-select').select2({
                ajax: {
                    url: 'deduplicate_ingredients.php',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            action: 'loadMoreIngredients',
                            offset: 0,
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: $.map(data, function (item) {
                                return {
                                    text: item.name.trim() + ' (ID: ' + item.id + ')',
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });
        }

        function initEventHandlers() {
            $('.ingredient-select').off('change').on('change', function() {
                var mergeButton = $(this).siblings('.merge-button');
                mergeButton.prop('disabled', !$(this).val());
                if ($(this).val()) {
                    mergeButton.removeClass('opacity-50 cursor-not-allowed');
                } else {
                    mergeButton.addClass('opacity-50 cursor-not-allowed');
                }
            });

            $('.merge-button').off('click').on('click', function() {
                var row = $(this).closest('tr');
                var remove_id = row.data('id');
                var keep_id = row.find('.ingredient-select').val();
                var remove_name = row.find('.ingredient-name').text();
                var keep_name = row.find('.ingredient-select option:selected').text();

                modal.show(
                    'Zutaten zusammenführen',
                    `Sind Sie sicher, dass Sie "${remove_name}" in "${keep_name}" zusammenführen möchten?`,
                    function() {
                        $.ajax({
                            url: 'deduplicate_ingredients.php',
                            method: 'POST',
                            data: {
                                action: 'mergeIngredients',
                                keep_id: keep_id,
                                remove_id: remove_id
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
                );
            });

            $('.ingredient-name').off('click').on('click', function() {
                var span = $(this);
                var currentName = span.text().trim();
                var input = $('<input type="text" class="border p-1 w-full">').val(currentName);
                
                span.hide().after(input);
                input.focus();

                input.blur(function() {
                    updateIngredientName(span.data('id'), input.val().trim(), span, input);
                });

                input.keypress(function(e) {
                    if (e.which == 13) {
                        updateIngredientName(span.data('id'), input.val().trim(), span, input);
                    }
                });
            });
        }

        initSelect2();
        initEventHandlers();

        $('#searchInput').on('input', function() {
            currentSearch = $(this).val();
            offset = 0;
            $('#ingredientsTable tbody').empty();
            loadIngredients();
        });

        function loadIngredients() {
            if (isLoading) return;
            isLoading = true;
            $.ajax({
                url: 'deduplicate_ingredients.php',
                method: 'GET',
                data: {
                    action: 'loadMoreIngredients',
                    offset: offset,
                    search: currentSearch,
                    sort: currentSort,
                    order: currentOrder
                },
                success: function(response) {
                    var ingredients = JSON.parse(response);
                    ingredients.forEach(function(ingredient) {
                        var newRow = $('<tr data-id="' + ingredient.id + '" class="border-b">' +
                            '<td class="p-2">' + ingredient.id + '</td>' +
                            '<td class="p-2"><span class="ingredient-name" data-id="' + ingredient.id + '">' + ingredient.name.trim() + '</span></td>' +
                            '<td class="p-2">' +
                            '<div class="flex items-center">' +
                            '<select class="ingredient-select mr-2">' +
                            '<option value="">Zutat zum Zusammenführen auswählen</option>' +
                            '</select>' +
                            '<button class="merge-button bg-green-500 text-white px-2 py-1 rounded mr-2 opacity-50 cursor-not-allowed" disabled>Zusammenführen</button>' +
                            '</div>' +
                            '<span class="error text-red-500 font-bold mt-1" style="display: none;"></span>' +
                            '</td>' +
                            '</tr>');
                        $('#ingredientsTable tbody').append(newRow);
                    });
                    initSelect2();
                    initEventHandlers();
                    offset += ingredients.length;
                    if (offset >= totalIngredients) {
                        $('#loadMore').hide();
                    } else {
                        $('#loadMore').show();
                    }
                    isLoading = false;
                },
                error: function() {
                    showToast('Fehler beim Laden weiterer Zutaten', false);
                    isLoading = false;
                }
            });
        }

        $('#loadMore').click(loadIngredients);

        $(window).scroll(function() {
            if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
                loadIngredients();
            }
        });

        $('#sortSelect').change(function() {
            var sortValue = $(this).val();
            if (sortValue.includes('DESC')) {
                currentSort = sortValue.replace(' DESC', '');
                currentOrder = 'DESC';
            } else {
                currentSort = sortValue;
                currentOrder = 'ASC';
            }
            offset = 0;
            $('#ingredientsTable tbody').empty();
            loadIngredients();
        });

        function updateIngredientName(ingredient_id, new_name, span, input) {
            $.ajax({
                url: 'deduplicate_ingredients.php',
                method: 'POST',
                data: {
                    action: 'updateIngredientName',
                    ingredient_id: ingredient_id,
                    new_name: new_name
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.success) {
                        showToast('Zutatennamen geändert');
                        span.text(new_name).show();
                        input.remove();
                    } else {
                        showToast('Fehler beim Aktualisieren des Zutatennamen', false);
                        span.show();
                        input.remove();
                    }
                },
                error: function() {
                    showToast('Ein Fehler ist aufgetreten', false);
                    span.show();
                    input.remove();
                }
            });
        }

        function showToast(message, isSuccess = true) {
            var toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.backgroundColor = isSuccess ? '#4CAF50' : '#f44336';
            toast.innerText = message;
            document.body.appendChild(toast);

            toast.className += ' show';
            setTimeout(function() {
                toast.className = toast.className.replace(' show', '');
                document.body.removeChild(toast);
            }, 3000);
        }
    });
    </script>
</body>
</html>