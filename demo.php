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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thesis Presentations</title>
    <style>
        /* General Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #eaf4fc, #ffffff);
            color: #333;
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .container {
            max-width: 1000px;
            margin: 50px auto;
            background: #fff;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px 30px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        h1 {
            color: #0056b3;
            font-size: 2.2rem;
            margin: 0;
        }

        /* Button Styles */
        .button {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin: 5px;
        }

        .button-primary {
            background-color: #0056b3;
            color: #fff;
            transition: background-color 0.3s ease;
        }

        .button-primary:hover {
            background-color: #004080;
        }

        .button-secondary {
            background-color: #f0f4ff;
            color: #0056b3;
            transition: background-color 0.3s ease;
        }

        .button-secondary:hover {
            background-color: #e0ecff;
        }

        /* Filter Section */
        .filter-section {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        input[type="date"] {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f5faff;
            color: #0056b3;
            font-weight: bold;
        }

        tr:hover {
            background-color: #f0f4ff;
        }

        .no-results {
            text-align: center;
            margin: 20px;
            color: #888;
        }

        .login-btn {
        text-decoration: none;
        color: white;
        background-color: #0056b3;
        padding: 10px 15px;
        border-radius: 5px;
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 0.9rem;
        font-weight: bold; /* Add this line */
    }

.login-btn:hover {
    background-color: #004080;
}
    </style>
</head>
<body>
    <a href="login.php" class="login-btn">Login</a>

    <div class="container">
        <div class="header">
            <h1>Thesis Presentations</h1>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <form method="GET" action="">
                <label for="start">From:</label>
                <input type="date" id="start" name="start" value="<?= htmlspecialchars($start) ?>">
                <label for="end">To:</label>
                <input type="date" id="end" name="end" value="<?= htmlspecialchars($end) ?>">
                <button type="submit" class="button button-primary">Filter</button>
                <a href="demo.php" class="button button-secondary">Reset</a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=xml" class="button button-secondary">Export XML</a>
                <a href="endpoint.php?start=<?= htmlspecialchars($start) ?>&end=<?= htmlspecialchars($end) ?>&format=json" class="button button-secondary">Export JSON</a>
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