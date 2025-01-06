<?php
include 'access.php';

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

$professor_id = $_SESSION['user_id'];




// Μέσος χρόνος περάτωσης
$query1 = "SELECT AVG(DATEDIFF(completionDate, assignmentDate)) as avg_completion_time 
           FROM thesis 
           WHERE supervisorID = $professor_id AND completionDate IS NOT NULL";
$result1 = $con->query($query1);
$avg_completion_time_supervised = $result1->fetch_assoc()['avg_completion_time'] ?? 0;

$query2 = "SELECT AVG(DATEDIFF(completionDate, assignmentDate)) as avg_completion_time 
           FROM thesis 
           WHERE (member1ID = $professor_id OR member2ID = $professor_id) AND completionDate IS NOT NULL";
$result2 = $con->query($query2);
$avg_completion_time_committee = $result2->fetch_assoc()['avg_completion_time'] ?? 0;

// Μέσος βαθμός
$query3 = "SELECT AVG(finalGrade + member1Grade + member2Grade)/3 as avg_grade 
           FROM thesis 
           WHERE supervisorID = $professor_id AND finalGrade IS NOT NULL";
$result3 = $con->query($query3);
$avg_grade_supervised = $result3->fetch_assoc()['avg_grade'] ?? 0;

$query4 = "SELECT AVG(finalGrade + member1Grade + member2Grade)/3  as avg_grade 
           FROM thesis 
           WHERE (member1ID = $professor_id OR member2ID = $professor_id) AND finalGrade IS NOT NULL";
$result4 = $con->query($query4);
$avg_grade_committee = $result4->fetch_assoc()['avg_grade'] ?? 0;

// Συνολικό πλήθος
$query5 = "SELECT COUNT(*) as total_supervised 
           FROM thesis 
           WHERE supervisorID = $professor_id";
$result5 = $con->query($query5);
$total_supervised = $result5->fetch_assoc()['total_supervised'] ?? 0;

$query6 = "SELECT COUNT(*) as total_committee 
           FROM thesis 
           WHERE member1ID = $professor_id OR member2ID = $professor_id";
$result6 = $con->query($query6);
$total_committee = $result6->fetch_assoc()['total_committee'] ?? 0;

// Include the global menu
include 'menus/menu.php';

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Στατιστικά Διδασκόντων</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="dipl.css">
</head>
<body>
    <h1>Στατιστικά για Διδάσκοντες</h1>

    <h2>Μέσος Χρόνος Περάτωσης (σε ημέρες)</h2>
    <canvas id="completionTimeChart"></canvas>

    <h2>Μέσος Βαθμός</h2>
    <canvas id="gradeChart"></canvas>

    <h2>Συνολικό Πλήθος Διπλωματικών</h2>
    <canvas id="totalThesesChart"></canvas>

    <!-- Ενσωμάτωση δεδομένων ως JSON -->
    <script id="statistics-data" type="application/json">
        <?php
        echo json_encode([
            'avgCompletionTimeSupervised' => $avg_completion_time_supervised,
            'avgCompletionTimeCommittee' => $avg_completion_time_committee,
            'avgGradeSupervised' => $avg_grade_supervised,
            'avgGradeCommittee' => $avg_grade_committee,
            'totalSupervised' => $total_supervised,
            'totalCommittee' => $total_committee,
        ]);
        ?>
    </script>

    <script>
        // Ανάκτηση δεδομένων από το JSON script tag
        const statisticsData = JSON.parse(document.getElementById('statistics-data').textContent);

        // Γράφημα: Μέσος Χρόνος Περάτωσης
        new Chart(document.getElementById('completionTimeChart'), {
            type: 'bar',
            data: {
                labels: ['Επιβλέποντας', 'Μέλος Τριμελούς'],
                datasets: [{
                    label: 'Μέσος Χρόνος (Ημέρες)',
                    data: [
                        statisticsData.avgCompletionTimeSupervised,
                        statisticsData.avgCompletionTimeCommittee
                    ],
                    backgroundColor: ['#42A5F5', '#66BB6A']
                }]
            }
        });

        // Γράφημα: Μέσος Βαθμός
        new Chart(document.getElementById('gradeChart'), {
            type: 'bar',
            data: {
                labels: ['Επιβλέποντας', 'Μέλος Τριμελούς'],
                datasets: [{
                    label: 'Μέσος Βαθμός',
                    data: [
                        statisticsData.avgGradeSupervised,
                        statisticsData.avgGradeCommittee
                    ],
                    backgroundColor: ['#FFA726', '#AB47BC']
                }]
            }
        });

        // Γράφημα: Συνολικό Πλήθος
        new Chart(document.getElementById('totalThesesChart'), {
            type: 'pie',
            data: {
                labels: ['Επιβλέποντας', 'Μέλος Τριμελούς'],
                datasets: [{
                    label: 'Συνολικό Πλήθος',
                    data: [
                        statisticsData.totalSupervised,
                        statisticsData.totalCommittee
                    ],
                    backgroundColor: ['#EF5350', '#29B6F6']
                }]
            }
        });
    </script>
</body>
</html>

