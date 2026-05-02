<?php
include "db.php";

$gardex_tables = [
    "plants",
    "users",
    "garden",
    "climate_area",
    "soil_type",
    "resources",
    "gardening_supplies",
    "supplier",
    "supplier_material",
    "user_supplier",
    "garden_plant",
    "plant_climate",
    "plant_material",
    "plant_resources",
    "plant_soil"
];

$table = isset($_GET['table']) ? $_GET['table'] : null;

// Security check: only allow tables in our approved list
if ($table && !in_array($table, $gardex_tables)) {
    die("Invalid table selected.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gardex Database Demo</title>
    <style>
        body { font-family: Arial; background: #f4f7f2; padding: 20px; }
        h1 { color: #2f5d3a; }
        a { text-decoration: none; color: #2f5d3a; }
        a:hover { text-decoration: underline; }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
        }

        th { background: #dcebd6; }
    </style>
</head>
<body>

<h1>Gardex Database Dashboard</h1>

<h2>Tables</h2>
<ul>
<?php
foreach ($gardex_tables as $t) {
    echo "<li><a href='?table=$t'>" . htmlspecialchars($t) . "</a></li>";
}
?>
</ul>

<?php
if ($table) {
    echo "<h2>Viewing: " . htmlspecialchars($table) . "</h2>";

    $result = $conn->query("SELECT * FROM `$table` LIMIT 50");

    echo "<table>";

    echo "<tr>";
    $fields = $result->fetch_fields();
    foreach ($fields as $field) {
        echo "<th>" . htmlspecialchars($field->name) . "</th>";
    }
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        foreach ($row as $value) {
            echo "<td>" . htmlspecialchars($value ?? "") . "</td>";
        }
        echo "</tr>";
    }

    echo "</table>";
}

$conn->close();
?>

</body>
</html>
