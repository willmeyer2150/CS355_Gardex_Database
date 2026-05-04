<?php
include "db.php";

// ==========================
// TABLE DEFINITIONS
// ==========================
$entity_tables = [
    "plants",
    "users",
    "garden",
    "climate_area",
    "soil_type",
    "resources",
    "gardening_supplies",
    "supplier"
];

$junction_tables = [
    "supplier_material",
    "user_supplier",
    "garden_plant",
    "plant_climate",
    "plant_material",
    "plant_resources",
    "plant_soil"
];

$allowed_tables = array_merge($entity_tables, $junction_tables);

// ==========================
// DEMO QUERIES
// ==========================
$demo_queries = [
    "phase1_mediterranean" => [
        "title" => "Phase 1 Query: Mediterranean Low-Water Plants",
        "sql" => "
SELECT p.common_name, p.growth_type, p.water_req
FROM plants p
JOIN plant_climate pc ON p.plant_id = pc.plant_id
JOIN climate_area c ON pc.climate_area_id = c.climate_area_id
WHERE c.type = 'Csb' AND p.water_req = 'low';
        ",
        "explanation" => "Finds plants that are low-water and compatible with a Mediterranean climate."
    ],

    "phase1_tomato" => [
        "title" => "Phase 1 Query: Tomato Water Requirement",
        "sql" => "
SELECT p.common_name, p.water_req
FROM plants p
WHERE p.common_name = 'Tomato';
        ",
        "explanation" => "Shows the water requirement for a specific plant."
    ],

    "advanced_multiclimate" => [
        "title" => "Advanced Query: Plants in Multiple Climates",
        "sql" => "
SELECT p.common_name, COUNT(pc.climate_area_id) AS climate_count
FROM plants p
JOIN plant_climate pc ON p.plant_id = pc.plant_id
GROUP BY p.common_name
HAVING COUNT(pc.climate_area_id) > 1;
        ",
        "explanation" => "Uses JOIN, GROUP BY, COUNT, and HAVING to identify adaptable plants."
    ],

    "advanced_suppliers" => [
        "title" => "Advanced Query: Suppliers for Low-Water Plants",
        "sql" => "
SELECT p.common_name, gs.material_name, s.supplier_name
FROM plants p
JOIN plant_material pm ON p.plant_id = pm.plant_id
JOIN gardening_supplies gs ON pm.material_id = gs.material_id
JOIN supplier_material sm ON gs.material_id = sm.material_id
JOIN supplier s ON sm.supplier_id = s.supplier_id
WHERE p.water_req = 'low';
        ",
        "explanation" => "Connects plants, materials, and suppliers to support drought-resistant gardening."
    ],

    "advanced_resources" => [
        "title" => "Advanced Query: Plant Resource Summary",
        "sql" => "
SELECT p.common_name,
       GROUP_CONCAT(r.resource_name SEPARATOR ', ') AS required_resources
FROM plants p
JOIN plant_resources pr ON p.plant_id = pr.plant_id
JOIN resources r ON pr.resource_id = r.resource_id
GROUP BY p.common_name;
        ",
        "explanation" => "Uses GROUP_CONCAT to combine all required resources for each plant into one readable list."
    ],

    "advanced_resource_schedule" => [
        "title" => "Advanced Query: Plant Resource Schedule",
        "sql" => "
SELECT * FROM plant_resource_requirements;
        ",
        "explanation" => "Shows each plant’s required resources along with amount, unit, and frequency."
    ],

    "view_edibility" => [
        "title" => "View: Plant Edibility View",
        "sql" => "
SELECT * FROM plant_edibility_view;
        ",
        "explanation" => "Shows a readable Yes/No label instead of raw 1/0 edibility values."
    ],

    "view_safety" => [
        "title" => "View: Plant Safety View",
        "sql" => "
SELECT * FROM plant_safety_view;
        ",
        "explanation" => "Summarizes edibility, toxicity, and safety interpretation in one reusable view."
    ]
];

// ==========================
// INPUT HANDLING
// ==========================
$table = isset($_GET["table"]) ? $_GET["table"] : null;
$demo = isset($_GET["demo"]) ? $_GET["demo"] : null;

if ($table && !in_array($table, $allowed_tables)) {
    die("Invalid table selected.");
}

if ($demo && !array_key_exists($demo, $demo_queries) && $demo !== "trigger") {
    die("Invalid demo selected.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gardex Database Demo</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7f2;
            padding: 20px;
            color: #222;
        }

        h1 {
            color: #2f5d3a;
            margin-bottom: 5px;
        }

        h2 {
            margin-top: 30px;
            color: #2f5d3a;
        }

        h3 {
            margin-bottom: 8px;
        }

        a {
            text-decoration: none;
            color: #2f5d3a;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        .home-button {
            display: inline-block;
            padding: 8px 12px;
            background: #2f5d3a;
            color: white;
            border-radius: 5px;
            margin: 10px 0 20px 0;
        }

        .home-button:hover {
            text-decoration: none;
            background: #24482d;
        }

        .dashboard-container {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }

        .left-panel {
            flex: 2;
            min-width: 0;
        }

        .right-panel {
            flex: 1;
            position: sticky;
            top: 20px;
        }

        .image-panel-title {
            text-align: center;
            color: #2f5d3a;
            margin-top: 0;
            margin-bottom: 12px;
        }

        .image-block {
            background: white;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
        }

        .image-block img {
            width: 100%;
            display: block;
            border-radius: 8px;
        }

        .caption {
            font-size: 0.85rem;
            color: #555;
            text-align: center;
            margin-top: 6px;
        }

        .demo-box {
            background: white;
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 6px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background: white;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        th {
            background: #dcebd6;
        }

        pre {
            background: #222;
            color: #f1f1f1;
            padding: 15px;
            overflow-x: auto;
            border-radius: 6px;
            white-space: pre-wrap;
        }

        ul {
            background: white;
            padding: 15px 25px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        li {
            margin: 6px 0;
        }

        @media (max-width: 900px) {
            .dashboard-container {
                flex-direction: column;
            }

            .right-panel {
                position: static;
                width: 100%;
            }
        }
    </style>
</head>

<body>

<h1>Gardex Database Dashboard</h1>

<a class="home-button" href="index.php">← Home</a>

<div class="dashboard-container">

    <!-- LEFT SIDE -->
    <div class="left-panel">

        <h2>Entity Tables</h2>
        <ul>
            <?php
            foreach ($entity_tables as $t) {
                echo "<li><a href='?table=" . urlencode($t) . "'>" . htmlspecialchars($t) . "</a></li>";
            }
            ?>
        </ul>

        <h2>Junction Tables</h2>
        <ul>
            <?php
            foreach ($junction_tables as $t) {
                echo "<li><a href='?table=" . urlencode($t) . "'>" . htmlspecialchars($t) . "</a></li>";
            }
            ?>
        </ul>

        <h2>Demo Queries</h2>

        <?php
        foreach ($demo_queries as $key => $query_info) {
            echo "<div class='demo-box'>";
            echo "<h3>" . htmlspecialchars($query_info["title"]) . "</h3>";
            echo "<p><a href='?demo=" . urlencode($key) . "'>Run Query</a></p>";
            echo "<pre>" . htmlspecialchars(trim($query_info["sql"])) . "</pre>";
            echo "<p><strong>Explanation:</strong> " . htmlspecialchars($query_info["explanation"]) . "</p>";
            echo "</div>";
        }
        ?>

        <div class="demo-box">
            <h3>Trigger Demo: Plant Safety Rule</h3>
            <p><a href="?demo=trigger">Show Trigger Test</a></p>
            <pre>UPDATE plants
SET edibility = 1, toxicity = 1
WHERE common_name = 'Tomato';</pre>
            <p><strong>Explanation:</strong> Demonstrates that the database rejects a plant being both edible and toxic.</p>
        </div>

        <?php
        if ($table) {
            echo "<h2>Viewing Table: " . htmlspecialchars($table) . "</h2>";

            $result = $conn->query("SELECT * FROM `$table` LIMIT 50");

            if ($result) {
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
            } else {
                echo "<p><strong>Query Error:</strong> " . htmlspecialchars($conn->error) . "</p>";
            }
        }
        ?>

        <?php
        if ($demo && $demo !== "trigger") {
            $query_info = $demo_queries[$demo];

            echo "<h2>Demo Result: " . htmlspecialchars($query_info["title"]) . "</h2>";

            $result = $conn->query($query_info["sql"]);

            if ($result) {
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
            } else {
                echo "<p><strong>Query Error:</strong> " . htmlspecialchars($conn->error) . "</p>";
            }
        }
        ?>

        <?php
        if ($demo === "trigger") {
            echo "<h2>Trigger Demo: Plant Safety Rule</h2>";

            echo "<pre>";
            echo htmlspecialchars("
UPDATE plants
SET edibility = 1, toxicity = 1
WHERE common_name = 'Tomato';
            ");
            echo "</pre>";

            echo "<p><strong>Expected Result:</strong> The trigger rejects this update and returns an error message:</p>";

            echo "<pre>";
            echo htmlspecialchars("Plant cannot be both edible and toxic");
            echo "</pre>";

            echo "<p>This protects the database from invalid plant safety data.</p>";
        }
        ?>

    </div>

    <!-- RIGHT SIDE -->
    <div class="right-panel">

        <h2 class="image-panel-title">System Visualization</h2>

        <div class="image-block">
            <img src="images/Home%20Garden.jpeg" alt="Home garden design">
            <div class="caption">Garden design outcome</div>
        </div>

        <div class="image-block">
            <img src="images/Fresh%20Plants.jpeg" alt="Fresh plants growing in rows">
            <div class="caption">Plant arrangement and growth</div>
        </div>

        <div class="image-block">
            <img src="images/Patio%20Garden.jpeg" alt="Patio garden irrigation system">
            <div class="caption">Resource management</div>
        </div>

    </div>

</div>

<?php
$conn->close();
?>

</body>
</html>