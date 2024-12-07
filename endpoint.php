<?php
include 'access.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $user, $password);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch filter inputs from GET parameters
$start = $_GET['start'] ?? null;
$end = $_GET['end'] ?? null;
$format = $_GET['format'] ?? 'json'; // Default to JSON if format is not specified

// Build the base SQL query
$query = "
    SELECT 
        a.announcementID, 
        t.title AS thesisTitle, 
        a.announcementText, 
        a.examinationDate, 
        a.examinationMethod, 
        a.location
    FROM Announcements a
    INNER JOIN Thesis t ON a.thesisID = t.thesisID
    WHERE a.examinationDate IS NOT NULL
";

// Add date range filters if provided
$params = [];
if ($start) {
    $query .= " AND a.examinationDate >= :start";
    $params[':start'] = $start;
}
if ($end) {
    $query .= " AND a.examinationDate <= :end";
    $params[':end'] = $end;
}

// Order by examinationDate
$query .= " ORDER BY a.examinationDate ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return data as XML or JSON
if ($format === 'xml') {
    header('Content-Type: application/xml; charset=utf-8');
    $xml = new SimpleXMLElement('<announcements/>');

    foreach ($announcements as $announcement) {
        $item = $xml->addChild('announcement');
        $item->addChild('announcementID', $announcement['announcementID']);
        $item->addChild('thesisTitle', htmlspecialchars($announcement['thesisTitle']));
        $item->addChild('announcementText', htmlspecialchars($announcement['announcementText']));
        $item->addChild('examinationDate', $announcement['examinationDate']);
        $item->addChild('examinationMethod', htmlspecialchars($announcement['examinationMethod']));
        $item->addChild('location', htmlspecialchars($announcement['location']));
    }

    echo $xml->asXML();
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($announcements, JSON_PRETTY_PRINT);
}
?>
