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

$query4 = "SELECT AVG(finalGrade) as avg_grade 
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
    <!--<link rel="stylesheet" href="dipl.css">-->
    <title>Στατιστικά Διδασκόντων</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href= "dipl.css">

</head>
<body>
    <h1>Στατιστικά για Διδάσκοντες</h1>

    <h2>Μέσος Χρόνος Περάτωσης (σε ημέρες)</h2>
    <canvas id="completionTimeChart"></canvas>

    <h2>Μέσος Βαθμός</h2>
    <canvas id="gradeChart"></canvas>

    <h2>Συνολικό Πλήθος Διπλωματικών</h2>
    <canvas id="totalThesesChart"></canvas>

    <script>
        // PHP δεδομένα στο JS
        const avgCompletionTimeSupervised = <?php echo $avg_completion_time_supervised; ?>;
        const avgCompletionTimeCommittee = <?php echo $avg_completion_time_committee; ?>;
        const avgGradeSupervised = <?php echo $avg_grade_supervised; ?>;
        const avgGradeCommittee = <?php echo $avg_grade_committee; ?>;
        const totalSupervised = <?php echo $total_supervised; ?>;
        const totalCommittee = <?php echo $total_committee; ?>;

        // Γράφημα: Μέσος Χρόνος Περάτωσης
        new Chart(document.getElementById('completionTimeChart'), {
            type: 'bar',
            data: {
                labels: ['Επιβλέποντας', 'Μέλος Τριμελούς'],
                datasets: [{
                    label: 'Μέσος Χρόνος (Ημέρες)',
                    data: [avgCompletionTimeSupervised, avgCompletionTimeCommittee],
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
                    data: [avgGradeSupervised, avgGradeCommittee],
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
                    data: [totalSupervised, totalCommittee],
                    backgroundColor: ['#EF5350', '#29B6F6']
                }]
            }
        });
    </script>
    
</body>
</html>
