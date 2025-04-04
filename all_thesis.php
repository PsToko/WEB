<?php
include 'access.php';

// Έναρξη συνεδρίας
session_start();

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος και έχει δικαιώματα διαχειριστή
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'professors') {
    header("Location: login.php?block=1");
    exit();
}

// Ανάκτηση επιλεγμένων φίλτρων από τις παραμέτρους GET
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';

// Ορισμός συνθηκών SQL
$status_condition = ($filter_status !== '') ? "AND status = ?" : "";
$role_condition = "";

if ($filter_role === 'supervisor') {
    $role_condition = "supervisorID = ?";
} elseif ($filter_role === 'member') {
    $role_condition = "(member1ID = ? OR member2ID = ?)";
} else {
    $role_condition = "(supervisorID = ? OR member1ID = ? OR member2ID = ?)";
}

$query = "
    SELECT 
        thesis.thesisID, 
        thesis.title, 
        thesis.description, 
        thesis.pdf, 
        thesis.status, 
        thesis.studentID, 
        thesis.supervisorID, 
        thesis.member1ID, 
        thesis.member2ID, 
        thesis.postedDate, 
        thesis.assignmentDate, 
        thesis.completionDate, 
        thesis.examinationDate, 
        thesis.finalGrade,
        thesis.member1Grade,        
        thesis.member2Grade,
        thesis.withdrawalDate,
        thesis.withdrawn_comment,
        thesis.nemertes,
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
        $role_condition 
        $status_condition
";

$stmt = $con->prepare($query);

// Δυναμική σύνδεση παραμέτρων
if ($filter_status !== '') {
    if ($role_condition === "(supervisorID = ? OR member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iiis', $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id'], $filter_status);
    } elseif ($role_condition === "(member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iis', $_SESSION['user_id'], $_SESSION['user_id'], $filter_status);
    } else {
        $stmt->bind_param('is', $_SESSION['user_id'], $filter_status);
    }
} else {
    if ($role_condition === "(supervisorID = ? OR member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('iii', $_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']);
    } elseif ($role_condition === "(member1ID = ? OR member2ID = ?)") {
        $stmt->bind_param('ii', $_SESSION['user_id'], $_SESSION['user_id']);
    } else {
        $stmt->bind_param('i', $_SESSION['user_id']);
    }
}

$stmt->execute();
$result = $stmt->get_result();

$thesis = $result->fetch_assoc();

// Include the global menu
include 'menus/menu.php';

?>

<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Προβολή Διπλωματικών</title>
    <!--<link rel="stylesheet" href= "dipl.css">-->
   <link rel="stylesheet" href= "AllCss.css">
</head>
<body>

<div class="container">
        <h1>Εμφάνιση Διπλωματικών</h1>

        <!-- Φίλτρα -->
        <form method="GET" action="" style="margin-bottom: 20px;">
            <label for="status">Κατάσταση:</label>
            <select name="status" id="status">
                <option value="">Όλες</option>
                <option value="under assignment" <?= $filter_status === 'under assignment' ? 'selected' : '' ?>>Υπό Ανάθεση</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Ενεργές</option>
                <option value="under review" <?= $filter_status === 'under review' ? 'selected' : '' ?>>Υπό Εξέταση</option>
                <option value="finalized" <?= $filter_status === 'finalized' ? 'selected' : '' ?>>Οριστικοποιημένες</option>
                <option value="withdrawn" <?= $filter_status === 'withdrawn' ? 'selected' : '' ?>>Ακυρωμένες</option>
            </select>

            <label for="role">Ρόλος:</label>
            <select name="role" id="role">
                <option value="">Όλα</option>
                <option value="supervisor" <?= $filter_role === 'supervisor' ? 'selected' : '' ?>>Επιβλέπων</option>
                <option value="member" <?= $filter_role === 'member' ? 'selected' : '' ?>>Μέλος</option>
            </select>

            <button type="submit">Φιλτράρισμα</button>
        </form>

        <!-- Εμφάνιση θεμάτων διπλωματικών -->
        <div class="topic-list">
            <h2>Λίστα Θεμάτων</h2>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="thesis-tbl" onclick=\'showModal(' . json_encode($row, JSON_HEX_APOS | JSON_HEX_QUOT) . ')\'>';
                    echo '<h3>' . htmlspecialchars($row['title']) . '</h3>';
                    echo '<p>Σύνοψη: ' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p>Κατάσταση: ' . htmlspecialchars($row['status']) . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>Δεν βρέθηκαν αποτελέσματα για τα φίλτρα σας.</p>';
            }
            ?>
        
        </div>
        <div>
            <button class="add-topic-button" onclick="exportData('json')">Εξαγωγή σε JSON</button>
            <button class="add-topic-button" onclick="exportData('csv')">Εξαγωγή σε CSV</button>
        </div>

        <!-- Δομή Modal -->
        <div id="detailsModal" class="details-modal">
            <div class="modal-content">
                <span class="modal-close" onclick="closeModal()">&times;</span>
                <h2 id="modalTitle">Τίτλος</h2>
                <p id="modalDescription">Περιγραφή</p>
                <p><strong>Κατάσταση:</strong> <span id="modalStatus"></span></p>
                <button id="statusChangeButton" style="display: none;" onclick="changeStatus()">Μετατροπή σε 'Υπό Εξέταση'</button>
                <button id="withdrawButton" style="display: none;" onclick="withdrawThesis()">Ακύρωση διπλωματικής</button>
                <p><strong>Φοιτητής:</strong> <span id="modalStudent"></span></p>
                <p><strong>Επιβλέπων:</strong> <span id="modalSupervisor"></span></p>
                <p><strong>Μέλος επιτροπής:</strong> <span id="modalMember1"></span></p>
                <p><strong>Μέλος επιτροπής:</strong> <span id="modalMember2"></span></p>
                <p><strong>Ημερομηνίες:</strong></p>
                <ul>
                    <li><strong>Ημερομηνία Ανάθεσης:</strong> <span id="modalAssignDate"></span></li>
                    <li><strong>Ημερομηνία Αξιολόγησης:</strong> <span id="modalSubmitDate"></span></li>
                    <li><strong>Ημερομηνία Ολοκλήρωσης:</strong> <span id="modalReviewDate"></span></li>
                    <li><strong>Ημερομηνία Ακύρωσης:</strong> <span id="modalWithdrawalDate"></span></li>
                </ul>
                <li><strong>Λόγος Ακύρωσης:</strong> <span id="modalWithdrawnComment"></span></li>

                <p><strong>Σύνδεσμος Νημερτή:</strong> <span id="modalNemertes"></span></p>
                <p><strong>Τελικός Βαθμός Επιβλέπων:</strong> <span id="modalFinalGrade"></span></p>
                <p><strong>Βαθμός Καθηγητή Επιτροπής 1:</strong> <span id="modalMember1Grade"></span></p>
                <p><strong>Βαθμός Καθηγητή Επιτροπής 2:</strong> <span id="modalMember2Grade"></span></p>
                <p><strong>Σχόλια:</strong></p>
                <div id="commentsSection"></div>
                <textarea id="newComment" maxlength="300" placeholder="Γράψτε το σχόλιό σας (μέγιστο 300 λέξεις)..."></textarea>
                <button id="addCommentButton" onclick="addComment()">Προσθήκη Σχολίου</button>


                <div id="examinationDetails" style="display: none;">
                    <h3>Λεπτομέρειες Εξέτασης</h3>
                    <p><strong>Μέθοδος Εξέτασης:</strong> <span id="modalExaminationMethod"></span></p>
                    <p><strong>Τοποθεσία:</strong> <span id="modalLocation"></span></p>
                    <p><strong>Κατάσταση Διπλωματικής:</strong> <span id="modalStThesis"></span></p>
                    
                    <button class="add-topic-button" onclick="window.location.href = 'review.php';">Διαδικασία βαθμολόγησης</button>

                </div>
               

                
 <!-- Invitation History -->
 <h2>Ιστορικό Προσκλήσεων</h2>
                <div id="modalInvitationHistory"></div>

            </div>
        </div>
    </div>

    <script>

    const userId = <?= json_encode($_SESSION['user_id']); ?>;

    function showModal(data) {
        document.getElementById('detailsModal').setAttribute('data-thesis-id', data.thesisID);
        document.getElementById('modalTitle').innerText = data.title;
        document.getElementById('modalDescription').innerText = data.description;
        document.getElementById('modalStatus').innerText = data.status;


         // Έλεγχος για εμφάνιση της δυνατότητας σχολιασμού
        const commentSection = document.getElementById('commentsSection');
        const newCommentField = document.getElementById('newComment');
        const addCommentButton = document.getElementById('addCommentButton');

        if (data.status === 'active') {
            commentSection.style.display = 'block';
            newCommentField.style.display = 'block';
            addCommentButton.style.display = 'block';
        } else {
            commentSection.style.display = 'none';
            newCommentField.style.display = 'none';
            addCommentButton.style.display = 'none';
        }

        const statusChangeButton = document.getElementById('statusChangeButton');
        const withdrawButton = document.getElementById('withdrawButton');

        const assignmentDate = new Date(data.assignmentDate);
        const currentDate = new Date();
        const twoYearsAgo = new Date();
        twoYearsAgo.setFullYear(currentDate.getFullYear() - 2);

        // Εμφάνιση κουμπιού "Μετατροπή σε Under Review" (μόνο για supervisors)
        if (data.status === 'active' && data.supervisorID == userId) {
            statusChangeButton.style.display = 'block';
            statusChangeButton.setAttribute('data-thesis-id', data.thesisID);
        } else {
            statusChangeButton.style.display = 'none';
        }

        // Εμφάνιση κουμπιού "Αλλαγή σε Withdrawn" αν έχουν περάσει 2 χρόνια
        if (data.status === 'active' && assignmentDate <= twoYearsAgo && data.supervisorID == userId) {
            withdrawButton.style.display = 'block';
            withdrawButton.setAttribute('data-thesis-id', data.thesisID);
        } else {
            withdrawButton.style.display = 'none';
        }

        document.getElementById('modalStudent').innerText = data.student_name 
            ? `${data.student_name} ${data.student_surname}` 
            : 'Δεν έχει ανατεθεί';

        document.getElementById('modalSupervisor').innerText = data.supervisor_name 
            ? `${data.supervisor_name} ${data.supervisor_surname}` 
            : 'Δεν έχει οριστεί';

        document.getElementById('modalMember1').innerText = data.member1_name 
            ? `${data.member1_name} ${data.member1_surname}` 
            : 'Δεν έχει οριστεί';

        document.getElementById('modalMember2').innerText = data.member2_name 
            ? `${data.member2_name} ${data.member2_surname}` 
            : 'Δεν έχει οριστεί';

        document.getElementById('modalAssignDate').innerText = data.assignmentDate || 'Δεν υπάρχει';
        document.getElementById('modalSubmitDate').innerText = data.examinationDate || 'Δεν υπάρχει';
        document.getElementById('modalReviewDate').innerText = data.completionDate || 'Δεν υπάρχει';

        document.getElementById('modalWithdrawalDate').innerText = data.withdrawalDate || 'Δεν υπάρχει';
        document.getElementById('modalWithdrawnComment').innerText = data.withdrawn_comment || 'Δεν υπάρχει';

        document.getElementById('modalFinalGrade').innerText = data.finalGrade || 'Δεν έχει βαθμολογηθεί';
        document.getElementById('modalMember1Grade').innerText = data.member1Grade || 'Δεν έχει βαθμολογηθεί';
        document.getElementById('modalMember2Grade').innerText = data.member2Grade || 'Δεν έχει βαθμολογηθεί';
        document.getElementById('modalNemertes').innerText = data.nemertes || 'Δεν υπάρχει';


        // Εμφάνιση δεδομένων για εξέταση (μόνο αν status είναι under review)
        if (data.status === 'under review') {
            fetchExaminationDetails(data.thesisID);
        } else {
            document.getElementById('examinationDetails').style.display = 'none';
        }

        // Fetch and display invitation history only if the status is 'under assignment'
        const invitationHistoryDiv = document.getElementById('modalInvitationHistory');
        if (data.status === 'under assignment') {
            fetch('fetch_invitations.php?thesis_id=' + data.thesisID)
                .then(response => response.text())
                .then(invitationHTML => {
                    invitationHistoryDiv.innerHTML = invitationHTML;
                })
                .catch(error => {
                    console.error("Error fetching invitation history:", error);
                    invitationHistoryDiv.innerHTML = "<p>Error loading invitation history.</p>";
                });
        } else {
            invitationHistoryDiv.innerHTML = "<p>Invitation history is not available for this status.</p>";
        }


        document.getElementById('detailsModal').style.display = 'block';

        loadComments(data.thesisID);


        const reviewButton = document.getElementById('reviewButton');
        if (data.supervisorID === <?= $_SESSION['user_id'] ?>) {
            reviewButton.style.display = 'block';
        } else {
            reviewButton.style.display = 'none';
        }

    }

    function activateReview() {
        const thesisID = document.getElementById('detailsModal').getAttribute('data-thesis-id');
        if (confirm('Είστε σίγουροι ότι θέλετε να ενεργοποιήσετε τη δυνατότητα βαθμολογησης;')) {
            window.location.href = 'enable_review.php?thesisID=' + thesisID;
        }
    }


        function changeStatus() {
            const thesisID = document.getElementById('statusChangeButton').getAttribute('data-thesis-id');
            
            fetch('change_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ thesisID, newStatus: 'under review' }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Η κατάσταση άλλαξε σε "Under Review".');
                    document.getElementById('modalStatus').innerText = 'under review';
                    document.getElementById('statusChangeButton').style.display = 'none';
                    location.reload(); // Ανανέωση της σελίδας

                } else {
                    alert('Η αλλαγή απέτυχε: ' + data.error);
                }
            })
            .catch(error => {
                alert('Σφάλμα: ' + error.message);
            });
        }

        function addComment() {
            const thesisID = document.getElementById('detailsModal').getAttribute('data-thesis-id');
            const newComment = document.getElementById('newComment').value;

            if (newComment.trim() === '') {
                alert('Το σχόλιο δεν μπορεί να είναι κενό.');
                return;
            }

            console.log({ thesisID, comment: newComment });  // Ελέγχει τι αποστέλλεται


            fetch('add_comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ thesisID, comment: newComment }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Το σχόλιο προστέθηκε επιτυχώς.');
                    loadComments(thesisID); // Φόρτωση ανανεωμένων σχολίων
                    document.getElementById('newComment').value = ''; // Καθαρισμός πεδίου
                } else {
                    alert('Η προσθήκη του σχολίου απέτυχε: ' + data.error);
                }
            })
            .catch(error => {
                alert('Σφάλμα: ' + error.message);
            });
        }

        function loadComments(thesisID) {
            fetch(`get_comments.php?thesisID=${thesisID}`)
                .then(response => response.json())
                .then(data => {
                    const commentsSection = document.getElementById('commentsSection');
                    commentsSection.innerHTML = ''; // Καθαρισμός προηγούμενων σχολίων

                    if (data.success) {
                        data.comments.forEach(comment => {
                            const commentDiv = document.createElement('div');
                            commentDiv.classList.add('comment');
                            commentDiv.innerHTML = `<p>${comment.comment}</p>`;  // Δείχνει μόνο το σχόλιο
                            commentsSection.appendChild(commentDiv);
                        });
                    } else {
                        commentsSection.innerHTML = '<p>Δεν υπάρχουν σχόλια.</p>';
                    }
                })
                .catch(error => {
                    console.error('Σφάλμα φόρτωσης σχολίων:', error);
                });
        }

        function withdrawThesis() {
            const thesisID = document.getElementById('withdrawButton').getAttribute('data-thesis-id');

                // Ζήτηση από τον χρήστη να εισάγει τον αριθμό πρωτοκόλλου
                const protocolNumber = prompt("Εισάγετε τον αριθμό πρωτοκόλλου:", "");
                if (!protocolNumber || isNaN(protocolNumber) || protocolNumber <= 0) {
                    alert("Η ακύρωση δεν ολοκληρώθηκε. Ο αριθμός πρωτοκόλλου πρέπει να είναι ένας θετικός ακέραιος.");
                    return;
                }

                // Ζήτηση από τον χρήστη να εισάγει το έτος
                const year = prompt("Εισάγετε το έτος:", "");
                if (!year || isNaN(year) || year <= 0) {
                    alert("Η απόσυρση ακυρώθηκε. Το έτος πρέπει να είναι ένας θετικός ακέραιος.");
                    return;
                }

                // Δημιουργία της τιμής για το πεδίο `general_assembly`
                const generalAssemblyValue = `${protocolNumber}/${year}`;

                const requestData = {
                    thesisID: thesisID,
                    newStatus: 'withdrawn',
                    generalAssembly: generalAssemblyValue,
                };

            console.log('Sending request:', requestData); // Debugging

            fetch('withdrawn_thesis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData),
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Response received:', data); // Debugging
                    if (data.success) {
                        alert(`Η κατάσταση άλλαξε σε "Withdrawn". Καταχωρήθηκε: ${generalAssemblyValue}`);
                        document.getElementById('modalStatus').innerText = 'withdrawn';
                        document.getElementById('withdrawButton').style.display = 'none';
                        location.reload(); // Ανανέωση της σελίδας
                    } else {
                        alert('Η αλλαγή απέτυχε: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error); // Debugging
                    alert('Σφάλμα: ' + error.message);
                });
        }

        function exportData(format) {
            window.location.href = `export.php?format=${format}`;
        }

        function fetchExaminationDetails(thesisID) {
            fetch('fetch_examination.php?thesis_id=' + thesisID)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('modalExaminationMethod').innerText = data.examinationMethod || 'Δεν έχει οριστεί';
                    document.getElementById('modalLocation').innerText = data.location || 'Δεν έχει οριστεί';
                   // document.getElementById('modalStThesis').innerText = data.st_thesis || 'Δεν έχει οριστεί';
                   if (data.st_thesis_url) {
                        const pdfEmbed = `<embed src="${data.st_thesis_url}" type="application/pdf" width="600" height="400">`;
                        document.getElementById('modalStThesis').innerHTML = pdfEmbed;
                    } else {
                        document.getElementById('modalStThesis').innerHTML = 'Δεν υπάρχει αρχείο.';
                    }

                    document.getElementById('modalLocation').innerText = data.location || 'Δεν έχει οριστεί';
                   
                   document.getElementById('examinationDetails').style.display = 'block';
                })
                .catch(error => {
                    console.error("Error fetching examination details:", error);
                });
        }


        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
    </script>
</body>
</html>