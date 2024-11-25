<?php
include 'access.php';
session_start();

// Check if the user is logged in and has the role of "secretariat"
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'secretaries') {
    header("Location: login.php?block=1");
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
?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προβολή Διπλωματικών Εργασιών</title>
    <link rel="stylesheet" href="dipl.css">
    <style>
        .thesis-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 1em;
            text-align: left;
        }

        .thesis-table th, .thesis-table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }

        .thesis-table th {
            background-color: #f4f4f9;
            font-weight: bold;
        }

        .thesis-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .thesis-table tr:nth-child(odd) {
            background-color: #fff;
        }

        .no-thesis {
            font-size: 1.2em;
            color: #333;
            text-align: center;
            margin: 20px 0;
        }

        .add-topic-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-topic-button:hover {
            background-color: #0056b3;
        }

        .filter-form {
            margin-bottom: 20px;
        }

        /* Modal styles */
        .details-modal {
            display: none; /* Initially hidden */
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5); /* Overlay with transparency */
            justify-content: center; /* Center the content vertically and horizontally */
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            margin: 0 auto;
            width: 50%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        .modal-close {
            font-size: 20px;
            color: #aaa;
            cursor: pointer;
            float: right;
        }

        .modal-close:hover {
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Προβολή Διπλωματικών Εργασιών</h1>

        <!-- Filters -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <label for="status">Κατάσταση:</label>
            <select name="status" id="status">
                <option value="">Όλες</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Ενεργή</option>
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
                        </tr>
                      </thead>';
                echo '<tbody>';

                while ($row = $result->fetch_assoc()) {
                    echo '<tr onclick="showModal(' . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . ')">';
                    echo '<td>' . htmlspecialchars($row['title']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['description']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['status']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['student_name'] . ' ' . $row['student_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['supervisor_name'] . ' ' . $row['supervisor_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['member1_name'] . ' ' . $row['member1_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['member2_name'] . ' ' . $row['member2_surname']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['assignmentDate']) . '</td>';
                    echo '</tr>';
                }

                echo '</tbody></table>';
            } else {
                echo '<p class="no-thesis">Δεν βρέθηκαν αποτελέσματα για τα φίλτρα σας.</p>';
            }
            ?>
        </div>

        <button class="add-topic-button" onclick="window.location.href = 'secretary.php';">Επιστροφή</button>
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
    </script>
</body>
</html>
