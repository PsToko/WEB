<?php
include 'access.php';
session_start();

// Check if the user is logged in and has the role of "secretariat"
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    header("Location: login.php?block=1");
    exit();
}

// Handle AJAX request for withdrawal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['generalAssembly'], $_POST['withdrawComment'])) {
    $thesisID = $_POST['thesisID'];
    $generalAssembly = $_POST['generalAssembly'];
    $withdrawComment = $_POST['withdrawComment'];
    $withdrawalDate = date('Y-m-d'); // Current date

    // Begin a transaction
    $con->begin_transaction();

    try {
        // Update the thesis table
        $updateThesis = "UPDATE thesis SET status = 'withdrawn', withdrawalDate = ?, withdrawn_comment = ?, general_assembly = ? WHERE thesisID = ?";
        $stmt1 = $con->prepare($updateThesis);
        $stmt1->bind_param('sssi', $withdrawalDate, $withdrawComment, $generalAssembly, $thesisID);
        $stmt1->execute();

        // Update the students table
        $updateStudent = "UPDATE students SET has_thesis = 0 WHERE Student_ID = (SELECT studentID FROM thesis WHERE thesisID = ?)";
        $stmt2 = $con->prepare($updateStudent);
        $stmt2->bind_param('i', $thesisID);
        $stmt2->execute();

        // Commit transaction
        $con->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $con->rollback(); // Rollback on failure
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

    exit();
}

// Handle AJAX request to update general_assembly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['generalAssembly'])) {
    $thesisID = $_POST['thesisID'];
    $generalAssembly = $_POST['generalAssembly'];

    $updateQuery = "UPDATE thesis SET general_assembly = ? WHERE thesisID = ?";
    $stmt = $con->prepare($updateQuery);

    if ($stmt) {
        $stmt->bind_param('si', $generalAssembly, $thesisID);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => $con->error]);
    }
    exit();
}

// Handle AJAX request to finalize a thesis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thesisID'], $_POST['action']) && $_POST['action'] === 'finalize') {
    $thesisID = $_POST['thesisID'];

    // Check if the thesis can be finalized
    $checkQuery = "
        SELECT finalGrade, member1Grade, member2Grade, nemertes
        FROM thesis 
        WHERE thesisID = ?
    ";
    $stmt = $con->prepare($checkQuery);
    $stmt->bind_param('i', $thesisID);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentDate = new DateTime();


    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (!is_null($row['finalGrade']) && !is_null($row['member1Grade']) && !is_null($row['member2Grade']) && !is_null($row['nemertes'])) {
            // Update the thesis status to finalized and set completionDate
            $currentDate = date('Y-m-d'); // Current date
            $updateQuery = "UPDATE thesis SET status = 'finalized', completionDate = ? WHERE thesisID = ?";
            $updateStmt = $con->prepare($updateQuery);
            $updateStmt->bind_param('si', $currentDate, $thesisID);
        
            if ($updateStmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $updateStmt->error]);
            }
        }        
    } else {
        echo json_encode(['success' => false, 'error' => 'Examination data not found.']);
    }
    exit();
}


// Retrieve selected filters from GET parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

// Define SQL conditions based on filters
$status_condition = "";
$params = []; // Initialize params array

// Apply filter for 'active' or 'under review' statuses
if ($filter_status === 'active' || $filter_status === 'under review') {
    $status_condition = "AND thesis.status = ?";
    $params[] = $filter_status; // Add the filter status to the params array
}

// Construct the SQL query to fetch theses based on the filter
$query = "
    SELECT 
        thesis.thesisID, 
        thesis.title, 
        thesis.description, 
        thesis.status, 
        thesis.assignmentDate,
        thesis.general_assembly, 
        students.name AS student_name, 
        students.surname AS student_surname, 
        supervisor.name AS supervisor_name, 
        supervisor.surname AS supervisor_surname,
        member1.name AS member1_name, 
        member1.surname AS member1_surname,
        member2.name AS member2_name, 
        member2.surname AS member2_surname
    FROM 
        thesis
    LEFT JOIN students ON thesis.studentID = students.Student_ID
    LEFT JOIN professors AS supervisor ON thesis.supervisorID = supervisor.Professor_ID
    LEFT JOIN professors AS member1 ON thesis.member1ID = member1.Professor_ID
    LEFT JOIN professors AS member2 ON thesis.member2ID = member2.Professor_ID
    WHERE 
        thesis.status IN ('active', 'under review')
        $status_condition
    ORDER BY 
        thesis.assignmentDate DESC
";

// Prepare the query
$stmt = $con->prepare($query);

// Debugging: Check if query preparation fails
if ($stmt === false) {
    die('Error preparing the query: ' . $con->error);  // Output error message if query preparation fails
}

// Bind parameter dynamically based on the filter
if (!empty($params)) {
    // Bind the parameters (status filter in this case)
    $stmt->bind_param('s', $params[0]);
} else {
    // No filter applied, just execute the query without parameters
    $stmt->execute();
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προβολή Διπλωματικών Εργασιών</title>
    <!--<link rel="stylesheet" href="dipl.css">-->
    <link rel="stylesheet" href= "AllCss.css">
    
</head>
<body>
    <div class="container">
        <h1>Προβολή Διπλωματικών Εργασιών</h1>

        <!-- Filters -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <label for="status">Κατάσταση:</label>
            <select name="status" id="status">
                <option value="">Όλες</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Ενεργές</option>
                <option value="under review" <?= $filter_status === 'under review' ? 'selected' : '' ?>>Υπό Εξέταση</option>
            </select>

            <button type="submit">Φιλτράρισμα</button>
        </form>

        <!-- Display thesis topics -->
        <div class="topic-list">
            <h2>Λίστα Θεμάτων</h2>
            <?php
            if ($result->num_rows > 0) {
                echo '<table class="thesis-table">';
                echo '<thead>
                        <tr>
                            <th>Θέμα</th>
                            <th>Περιγραφή</th>
                            <th>Κατάσταση</th>
                            <th>Φοιτητής</th>
                            <th>Επιβλέπων</th>
                            <th>Μέλος Επιτροπής 1</th>
                            <th>Μέλος Επιτροπής 2</th>
                            <th>Ημερομηνία Ανάθεσης</th>
                            <th>Γενική Συνέλευση</th>
                            <th>Ενέργειες</th>
                        </tr>
                      </thead>';
                echo '<tbody>';

                while ($row = $result->fetch_assoc()) {
                    echo '<tr onclick=\'showModal(' . json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) . ')\'>';
                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['student_name'] . ' ' . $row['student_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['supervisor_name'] . ' ' . $row['supervisor_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['member1_name'] . ' ' . $row['member1_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['member2_name'] . ' ' . $row['member2_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['assignmentDate']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['general_assembly'] ?? 'Δεν έχει οριστεί') . '</td>';
                    if ($row['status'] === 'active') {
                        echo '<td><button onclick="addGeneralAssembly(' . $row['thesisID'] . ')">Προσθήκη Συνέλευσης</button>
                        <button onclick="withdrawThesis(' . $row['thesisID'] . ')">Ακύρωση</button></td>';

                    } else if ($row['status'] === 'under review') {
                        echo '<td>
                                <button onclick="finalizeThesis(' . $row['thesisID'] . ')">Οριστικοποίηση</button>
                              </td>';
                    } else {
                        echo '<td>-</td>';
                    }                    
                    echo '</tr>';
                }

                echo '</tbody></table>';
            } else {
                echo '<p class="no-thesis">Δεν βρέθηκαν αποτελέσματα για τα φίλτρα σας.</p>';
            }
            ?>
        </div>

    </div>

    <!-- Modal for thesis details -->
    <div id="detailsModal" class="details-modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle"></h2>
            <p><strong>Περιγραφή:</strong> <span id="modalDescription"></span></p>
            <p><strong>Κατάσταση:</strong> <span id="modalStatus"></span></p>
            <p><strong>Φοιτητής:</strong> <span id="modalStudent"></span></p>
            <p><strong>Επιβλέπων:</strong> <span id="modalSupervisor"></span></p>
            <p><strong>Μέλη Επιτροπής:</strong> <span id="modalMembers"></span></p>
            <p><strong>Ημερομηνία Ανάθεσης:</strong> <span id="modalAssignDate"></span></p>
            <p><strong>Χρόνος από την Ανάθεση:</strong> <span id="modalTimePassed"></span></p>
            <p><strong>Γενικη Συνέλευση Έγκρισης:</strong> <span id="modalGeneralAssembly"></span></p>
        </div>
    </div>
    
    <script>
        function showModal(data) {
            // Display the modal with thesis details
            document.getElementById('modalTitle').innerText = data.title;
            document.getElementById('modalDescription').innerText = data.description;
            document.getElementById('modalStatus').innerText = data.status;
            document.getElementById('modalStudent').innerText = data.student_name + ' ' + data.student_surname;
            document.getElementById('modalSupervisor').innerText = data.supervisor_name + ' ' + data.supervisor_surname;
            document.getElementById('modalMembers').innerText = (data.member1_name || 'Vacant') + ', ' + (data.member2_name || 'Vacant');
            document.getElementById('modalAssignDate').innerText = data.assignmentDate;
            document.getElementById('modalGeneralAssembly').innerText = data.general_assembly;

            // Calculate time passed since assignment
            const assignmentDate = new Date(data.assignmentDate);
            const currentDate = new Date();
            const timeDifference = currentDate - assignmentDate;
            const daysPassed = Math.floor(timeDifference / (1000 * 3600 * 24));
            document.getElementById('modalTimePassed').innerText = daysPassed + ' days';

            // Show the modal
            document.getElementById('detailsModal').style.display = 'flex'; // Use 'flex' for centering
        }

        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        function withdrawThesis(thesisID) {
            const assemblyNumber = prompt("Εισάγετε αριθμό συνέλευσης:");
            if (!assemblyNumber) return;

            const year = prompt("Εισάγετε έτος:");
            if (!year) return;

            const generalAssembly = `${assemblyNumber}/${year}`;
            const withdrawComment = "request from student";

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `thesisID=${thesisID}&withdrawComment=${encodeURIComponent(withdrawComment)}&generalAssembly=${encodeURIComponent(generalAssembly)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Η διπλωματική ακυρώθηκε επιτυχώς.");
                    location.reload();
                } else {
                    alert(`Σφάλμα: ${data.error}`);
                }
            })
            .catch(error => alert("Σφάλμα δικτύου."));
        }

        function addGeneralAssembly(thesisID) {
            const assemblyNumber = prompt("Εισάγετε αριθμό συνέλευσης:");
            if (!assemblyNumber) return;

            const year = prompt("Εισάγετε έτος:");
            if (!year) return;

            const generalAssembly = `${assemblyNumber}/${year}`;

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `thesisID=${thesisID}&generalAssembly=${encodeURIComponent(generalAssembly)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Η συνέλευση ενημερώθηκε επιτυχώς.");
                    location.reload();
                } else {
                    alert(`Σφάλμα: ${data.error}`);
                }
            })
            .catch(error => alert("Σφάλμα δικτύου."));
        }

        function finalizeThesis(thesisID) {
            if (!confirm("Είστε σίγουροι ότι θέλετε να οριστικοποιήσετε τη διπλωματική;")) return;

            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `thesisID=${thesisID}&action=finalize`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Η διπλωματική οριστικοποιήθηκε επιτυχώς.");
                    location.reload();
                } else {
                    alert(`Σφάλμα: ${data.error}`);
                }
            })
            .catch(error => alert("Σφάλμα δικτύου."));
        }

    </script>
</body>
</html>