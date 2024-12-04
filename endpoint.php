<?php
include 'access.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch parameters from the URL
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$format = $_GET['format'] ?? 'json';

// Build the query
$query = "SELECT thesisID, title, description, examinationDate FROM thesis WHERE examinationDate IS NOT NULL";
$params = [];

if ($start) {
    $query .= " AND examinationDate >= :start";
    $params[':start'] = $start;
}
if ($end) {
    $query .= " AND examinationDate <= :end";
    $params[':end'] = $end;
}

$query .= " ORDER BY examinationDate ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$theses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate output
if ($format === 'xml') {
    header("Content-Type: application/xml; charset=utf-8");
    header("Content-Disposition: attachment; filename=theses.xml");
    $xml = new SimpleXMLElement('<theses/>');
    foreach ($theses as $thesis) {
        $item = $xml->addChild('thesis');
        $item->addChild('id', $thesis['thesisID']);
        $item->addChild('title', $thesis['title']);
        $item->addChild('description', $thesis['description']);
        $item->addChild('examinationDate', $thesis['examinationDate']);
    }
    echo $xml->asXML();
} else { // JSON
    header("Content-Type: application/json; charset=utf-8");
    header("Content-Disposition: attachment; filename=theses.json");
    echo json_encode($theses, JSON_PRETTY_PRINT);
}
