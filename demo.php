<?php
include 'access.php';

// Connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch filter inputs from GET parameters
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;

// Build the base SQL query
$query = "SELECT thesisID, title, description, examinationDate FROM thesis WHERE examinationDate IS NOT NULL";

// Add date range filters if provided
$params = [];
if ($start) {
    $query .= " AND examinationDate >= :start";
    $params[':start'] = $start;
}
if ($end) {
    $query .= " AND examinationDate <= :end";
    $params[':end'] = $end;
}

// Order by examinationDate, closest to the current date first
$query .= " ORDER BY examinationDate ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="demo.css">
    <title>Thesis Presentations</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .login-btn {
            text-decoration: none;
            color: #fff;
            background-color: #007BFF;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-section {
            text-align: center;
            margin-bottom: 20px;
        }

        .filter-section button {
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .no-results {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <a href="login.php" class="login-btn">Login</a>

    <div class="container">
        <h1>Thesis Presentations</h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="">
                <label for="start">From:</label>
                <input type="date" id="start" name="start" value="<?= htmlspecialchars($start) ?>">

                <label for="end">To:</label>
                <input type="date" id="end" name="end" value="<?= htmlspecialchars($end) ?>">

                <button type="submit">Filter</button>
                <a href="demo.php"><button type="button">Reset</button></a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=xml">
                    <button type="button">Export to XML</button>
                </a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=json">
                    <button type="button">Export to JSON</button>
                </a>
            </form>
        </div>

        <!-- Theses Table -->
        <?php if ($theses): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Examination Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($theses as $thesis): ?>
                        <tr>
                            <td><?= htmlspecialchars($thesis['title']) ?></td>
                            <td><?= nl2br(htmlspecialchars($thesis['description'])) ?></td>
                            <td><?= htmlspecialchars($thesis['examinationDate']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">No theses found for the selected date range.</p>
        <?php endif; ?>
    </div>
</body>
</html>
