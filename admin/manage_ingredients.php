<?php
// Datenbankverbindung herstellen (passen Sie die Zugangsdaten an)
$conn = new mysqli("localhost", "root", "", "rezept");

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

// Funktion zum Abrufen aller Zutaten mit ihren Kategorien
function getIngredients($conn) {
    $sql = "SELECT bi.id, bi.name, GROUP_CONCAT(bic.name SEPARATOR ', ') as categories
            FROM fmr_basic_ingredients bi
            LEFT JOIN fmr_basic_ingredients_link bil ON bi.id = bil.ingredient_id
            LEFT JOIN fmr_basic_ingredients_category bic ON bil.category_id = bic.id
            GROUP BY bi.id
            ORDER BY bi.name";

    $result = $conn->query($sql);
    $ingredients = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $ingredients[] = $row;
        }
    }

    return $ingredients;
}

// Funktion zum Abrufen aller Kategorien
function getCategories($conn) {
    $sql = "SELECT id, name FROM fmr_basic_ingredients_category ORDER BY name";
    $result = $conn->query($sql);
    $categories = [];

    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
    }

    return $categories;
}

// Funktion zum Überprüfen, ob eine Zutat zu einer Kategorie gehört
function ingredientHasCategory($ingredient, $category) {
    $ingredientCategories = explode(', ', $ingredient['categories'] ?? '');
    return in_array($category['name'], $ingredientCategories);
}

// Ajax-Anfrage zum Aktualisieren der Kategorie
if (isset($_POST['action']) && $_POST['action'] == 'updateCategory') {
    $ingredient_id = $_POST['ingredient_id'];
    $category_id = $_POST['category_id'];
    $is_checked = $_POST['is_checked'];

    if ($is_checked == 'true') {
        $sql = "INSERT INTO fmr_basic_ingredients_link (ingredient_id, category_id) VALUES (?, ?)";
    } else {
        $sql = "DELETE FROM fmr_basic_ingredients_link WHERE ingredient_id = ? AND category_id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $ingredient_id, $category_id);
    $result = $stmt->execute();

    if ($result) {
        // Hole aktualisierte Kategorien für die Zutat
        $sql = "SELECT GROUP_CONCAT(bic.name SEPARATOR ', ') as categories
                FROM fmr_basic_ingredients bi
                LEFT JOIN fmr_basic_ingredients_link bil ON bi.id = bil.ingredient_id
                LEFT JOIN fmr_basic_ingredients_category bic ON bil.category_id = bic.id
                WHERE bi.id = ?
                GROUP BY bi.id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $ingredient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $updatedCategories = $row['categories'] ?? 'Keine Kategorie';
        
        echo json_encode(['success' => true, 'categories' => $updatedCategories]);
    } else {
        echo json_encode(['success' => false]);
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

$ingredients = getIngredients($conn);
$categories = getCategories($conn);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zutaten-Verwaltung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen max-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-6">

                <h1 class="text-3xl font-bold text-gray-800">Zutaten-Verwaltung</h1>
                <a href="index.php" class="text-blue-500 hover:text-blue-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>              
            </div>
            <div class="mb-4">
                <input type="text" id="searchInput" placeholder="Suche nach Zutaten..." class="w-full p-2 border rounded">
                <select id="categoryFilter" class="w-full p-2 border rounded mt-2">
                    <option value="">Alle Kategorien</option>
                    <option value="no-category">Keine Kategorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['name']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="overflow-y-auto max-h-screen">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-200 sticky top-0">
                            <th class="text-left">Zutat</th>
                            <?php foreach ($categories as $category): ?>
                                <th class="p-2 text-center"><?php echo htmlspecialchars($category['name']); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredients as $ingredient): ?>
                            <tr data-ingredient-id="<?php echo $ingredient['id']; ?>" class="border-b <?php echo empty($ingredient['categories']) ? 'bg-red-100' : ''; ?>">
                                <td class="p-2">
                                    <span class="ingredient-name cursor-pointer hover:underline" data-ingredient-id="<?php echo $ingredient['id']; ?>">
                                        <?php echo htmlspecialchars($ingredient['name']); ?>
                                    </span>
                                </td>
                                <?php foreach ($categories as $category): ?>
                                    <td class="p-2 text-center">
                                        <input type="checkbox" 
                                               class="category-checkbox" 
                                               data-ingredient-id="<?php echo $ingredient['id']; ?>" 
                                               data-category-id="<?php echo $category['id']; ?>"
                                               <?php echo ingredientHasCategory($ingredient, $category) ? 'checked' : ''; ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('.category-checkbox').change(function() {
                var checkbox = $(this);
                var ingredient_id = checkbox.data('ingredient-id');
                var category_id = checkbox.data('category-id');
                var is_checked = checkbox.prop('checked');
                
                $.ajax({
                    url: 'manage_ingredients.php',
                    method: 'POST',
                    data: {
                        action: 'updateCategory',
                        ingredient_id: ingredient_id,
                        category_id: category_id,
                        is_checked: is_checked
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            var row = $('tr[data-ingredient-id="' + ingredient_id + '"]');
                            row.find('.categories-cell').text(result.categories);
                            if (result.categories === 'Keine Kategorie') {
                                row.addClass('bg-red-100');
                            } else {
                                row.removeClass('bg-red-100');
                            }
                        } else {
                            alert('Fehler beim Aktualisieren der Kategorie');
                            checkbox.prop('checked', !is_checked);
                        }
                    },
                    error: function() {
                        alert('Ein Fehler ist aufgetreten');
                        checkbox.prop('checked', !is_checked);
                    }
                });
            });

            $('.ingredient-name').click(function() {
                var span = $(this);
                var ingredient_id = span.data('ingredient-id');
                var current_name = span.text();
                var input = $('<input type="text" class="border p-1 w-full">').val(current_name);
                
                span.hide().after(input);
                input.focus();

                input.blur(function() {
                    updateIngredientName(ingredient_id, input.val(), span, input);
                });

                input.keypress(function(e) {
                    if (e.which == 13) {
                        updateIngredientName(ingredient_id, input.val(), span, input);
                    }
                });
            });

            function updateIngredientName(ingredient_id, new_name, span, input) {
                $.ajax({
                    url: 'manage_ingredients.php',
                    method: 'POST',
                    data: {
                        action: 'updateIngredientName',
                        ingredient_id: ingredient_id,
                        new_name: new_name
                    },
                    success: function(response) {
                        var result = JSON.parse(response);
                        if (result.success) {
                            span.text(new_name).show();
                            input.remove();
                        } else {
                            alert('Fehler beim Aktualisieren des Zutatennamen');
                            span.show();
                            input.remove();
                        }
                    },
                    error: function() {
                        alert('Ein Fehler ist aufgetreten');
                        span.show();
                        input.remove();
                    }
                });
            }

            // Suchfunktion
            $('#searchInput').on('input', function() {
                filterTable();
            });

            // Kategorie-Filter
            $('#categoryFilter').change(function() {
                filterTable();
            });

            function filterTable() {
                var searchText = $('#searchInput').val().toLowerCase();
                var categoryFilter = $('#categoryFilter').val();

                $('tbody tr').each(function() {
                    var row = $(this);
                    var ingredientName = row.find('.ingredient-name').text().toLowerCase();
                    var categories = row.find('.categories-cell').text();

                    var nameMatch = ingredientName.includes(searchText);
                    var categoryMatch = true;

                    if (categoryFilter === 'no-category') {
                        categoryMatch = categories === 'Keine Kategorie';
                    } else if (categoryFilter !== '') {
                        categoryMatch = categories.includes(categoryFilter);
                    }

                    if (nameMatch && categoryMatch) {
                        row.show();
                    } else {
                        row.hide();
                    }
                });
            }
        });
    </script>
</body>
</html>