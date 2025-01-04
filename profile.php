<?php
// profile.php
include 'access.php';

// Ξεκινήστε τη συνεδρία
session_start();

// Ελέγξτε αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα φοιτητή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'students') {
    header("Location: login.php?block=1");
    exit();
}

// Λάβετε το δυναμικό Student ID από τη συνεδρία
$studentID = $_SESSION['user_id'];

// Ανάκτηση των τρεχουσών πληροφοριών του φοιτητή
$query = "SELECT AM, Name, Surname, Address, email, mobile, landline, Has_Thesis FROM Students WHERE Student_ID = ?";
$stmt = $con->prepare($query);
$stmt->bind_param('i', $studentID);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Αρχικοποίηση μηνυμάτων επιτυχίας και σφάλματος
$successMessage = $errorMessage = "";

// Διαχείριση υποβολής φόρμας
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $landline = $_POST['landline'];

    // Ενημέρωση των πληροφοριών του φοιτητή
    $updateQuery = "UPDATE Students SET Address = ?, email = ?, mobile = ?, landline = ? WHERE Student_ID = ?";
    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param('sssii', $address, $email, $mobile, $landline, $studentID);

    if ($stmt->execute()) {
        $successMessage = "Το προφίλ ενημερώθηκε με επιτυχία!";
        // Ανάκτηση των ενημερωμένων πληροφοριών
        $stmt = $con->prepare($query);
        $stmt->bind_param('i', $studentID);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
    } else {
        $errorMessage = "Σφάλμα κατά την ενημέρωση του προφίλ: " . $con->error;
    }
}

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προφίλ Φοιτητή</title>
    <!--<link rel="stylesheet" href="lobby.css">-->
    <link rel="stylesheet" href="AllCss.css">

    <script>
        function toggleEdit(fieldId, button) {
            const inputField = document.getElementById(fieldId);
            if (inputField.readOnly) {
                inputField.readOnly = false;
                inputField.classList.remove('readonly');
                button.textContent = "Αποθήκευση";
            } else {
                document.getElementById('profileForm').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        
        <h1>Το Προφίλ σας</h1>

        <?php if (!empty($successMessage)): ?>
            <p class="success"><?= htmlspecialchars($successMessage) ?></p>
        <?php elseif (!empty($errorMessage)): ?>
            <p class="error"><?= htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>

        <form id="profileForm" method="POST">
            <div class="form-row">
                <label for="am">Αριθμός Μητρώου (AM) :</label>
                <input type="text" id="am" value="<?= htmlspecialchars($student['AM']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="name">Όνομα :</label>
                <input type="text" id="name" value="<?= htmlspecialchars($student['Name']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="surname">Επώνυμο :</label>
                <input type="text" id="surname" value="<?= htmlspecialchars($student['Surname']) ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="has_thesis">Έχει Διπλωματική; :</label>
                <input type="text" id="has_thesis" value="<?= $student['Has_Thesis'] ? 'Ναι' : 'Όχι' ?>" class="readonly" readonly>
            </div>

            <div class="form-row">
                <label for="address">Διεύθυνση Κατοικίας :</label>
                <input type="text" id="address" name="address" value="<?= htmlspecialchars($student['Address']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('address', this)">Επεξεργασία</button>
            </div>

            <div class="form-row">
                <label for="email">Ηλεκτρονικό Ταχυδρομείο :</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('email', this)">Επεξεργασία</button>
            </div>

            <div class="form-row">
                <label for="mobile">Κινητό τηλέφωνο :</label>
                <input type="text" id="mobile" name="mobile" value="<?= htmlspecialchars($student['mobile']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('mobile', this)">Επεξεργασία</button>
            </div>

            <div class="form-row">
                <label for="landline">Σταθερό τηλέφωνο :</label>
                <input type="text" id="landline" name="landline" value="<?= htmlspecialchars($student['landline']) ?>" class="readonly" readonly>
                <button type="button" class="edit-button" onclick="toggleEdit('landline', this)">Επεξεργασία</button>
            </div>
        </form>
    </div>
</body>
</html>