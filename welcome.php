<?php
// --- 1. The "Bouncer" ---
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header("Location: index.html");
    exit;
}

// --- 2. The "LOAD" LOGIC ---
$server_name = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "login_project";

$saved_gpa = "";
$saved_credits = "";

$conn = new mysqli($server_name, $db_username, $db_password, $db_name);

if (!$conn->connect_error) {
    $user_id = $_SESSION['id'];
    $sql = "SELECT current_cgpa, credits_completed FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $data = $result->fetch_assoc();
        $saved_gpa = $data['current_cgpa'] ?? "";
        $saved_credits = $data['credits_completed'] ?? "";
    }
    $stmt->close();
    $conn->close();
}

$username = htmlspecialchars($_SESSION['name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Your Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        .navbar { background: white; padding: 1rem 2rem; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center; }
        .navbar h2 { margin: 0; color: #764ba2; }
        .navbar a { text-decoration: none; color: white; background: #c62828; padding: 8px 15px; border-radius: 5px; font-weight: bold; }
        .content { padding: 2rem; max-width: 800px; margin: 20px auto; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        
        /* Styles for the calculator */
        .gpa-form { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; color: #666; font-size: 0.9rem; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        
        #gpa-calculator button {
            grid-column: auto; /* Buttons side-by-side */
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            color: white;
        }
        #calcBtn {
            background: #28a745; /* Green */
        }
        #saveBtn {
            background-color: #007bff; /* Blue */
        }
        #gpa-result { 
            margin-top: 20px; 
            padding: 15px; 
            border-radius: 5px; 
            font-size: 1.1rem; 
            font-weight: bold; 
            text-align: center; 
            display: none; 
            /* This will span the full 2 columns */
            grid-column: 1 / -1; 
        }
    </style>
</head>
<body>

    <div class="navbar">
        <h2>Welcome, <?php echo $username; ?>!</h2>
        <a href="logout.php">Logout</a>
    </div>

    <div class="content">
        <h3>Your CGPA Goal Calculator</h3>
        <p>Your saved data is loaded below. Just enter your target and current semester credits to see your goal!</p>
        
        <div id="gpa-calculator">
            <hr>
            
            <div class="gpa-form">
                <div class="form-group">
                    <label>Current CGPA:</label>
                    <input type="number" id="current_cgpa" placeholder="e.g., 7.8" step="0.01" value="<?php echo $saved_gpa; ?>">
                </div>
                
                <div class="form-group">
                    <label>Total Credits Completed (So Far):</label>
                    <input type="number" id="credits_completed" placeholder="e.g., 90" value="<?php echo $saved_credits; ?>">
                </div>
                
                <div class="form-group">
                    <label>Target CGPA (What you want):</label>
                    <input type="number" id="target_cgpa" placeholder="e.g., 8.0" step="0.01">
                </div>
                
                <div class="form-group">
                    <label>Current Semester Credits (What you're taking):</label>
                    <input type="number" id="current_credits" placeholder="e.g., 20">
                </div>
                
                <!-- The two buttons, side-by-side -->
                <button onclick="calculateGoalGPA()" id="calcBtn">Calculate Goal</button>
                <button onclick="saveNewGPA()" id="saveBtn">Save My New GPA</button>

                <!-- The result div is now INSIDE the grid -->
                <div id="gpa-result"></div>
            </div>
            
        </div>
        
    </div>

    <!-- ================================== -->
    <!--  BOTH Javascript functions go here -->
    <!-- ================================== -->
    <script>
        function calculateGoalGPA() {
            // --- 1. Get all the values ---
            const currentCGPA = parseFloat(document.getElementById('current_cgpa').value);
            const creditsCompleted = parseFloat(document.getElementById('credits_completed').value);
            const targetCGPA = parseFloat(document.getElementById('target_cgpa').value);
            const currentCredits = parseFloat(document.getElementById('current_credits').value);

            // --- 2. Check for missing data ---
            if (isNaN(currentCGPA) || isNaN(creditsCompleted) || isNaN(targetCGPA) || isNaN(currentCredits)) {
                alert("Please fill in all four fields with valid numbers.");
                return;
            }

            // --- 3. The "Goal-Seeking" Math ---
            const totalCredits = creditsCompleted + currentCredits;
            const requiredTotalPoints = targetCGPA * totalCredits;
            const currentPoints = currentCGPA * creditsCompleted;
            const pointsNeededThisSemester = requiredTotalPoints - currentPoints;
            const requiredSemesterGPA = pointsNeededThisSemester / currentCredits;
            
            // --- 4. Show the result ---
            const resultDiv = document.getElementById('gpa-result');
            resultDiv.style.display = 'block';
            const gpaFormatted = requiredSemesterGPA.toFixed(2);

            if (requiredSemesterGPA > 10.0) {
                resultDiv.style.backgroundColor = '#ffebee'; // Red
                resultDiv.style.color = '#c62828';
                resultDiv.innerHTML = `To reach a ${targetCGPA} CGPA, you would need a GPA of **${gpaFormatted}** this semester. This is likely impossible.`;
            } else if (requiredSemesterGPA < 0) {
                resultDiv.style.backgroundColor = '#e8f5e9'; // Green
                resultDiv.style.color = '#2e7d32';
                resultDiv.innerHTML = `Your target is already met! You just need to score above a **0.00** this semester.`;
            } else {
                resultDiv.style.backgroundColor = '#e8f5e9'; // Green
                resultDiv.style.color = '#2e7d32';
                resultDiv.innerHTML = `To reach your goal of a ${targetCGPA} CGPA, you need to score an average GPA of **${gpaFormatted}** this semester.`;
            }
        }

        // --- The "Save" function ---
        function saveNewGPA() {
            const currentCGPA = parseFloat(document.getElementById('current_cgpa').value);
            const creditsCompleted = parseFloat(document.getElementById('credits_completed').value);
            const targetCGPA = parseFloat(document.getElementById('target_cgpa').value);
            const currentCredits = parseFloat(document.getElementById('current_credits').value);
            
            if (isNaN(targetCGPA) || isNaN(currentCredits) || isNaN(currentCGPA) || isNaN(creditsCompleted)) {
                alert("Please fill in all fields before saving.");
                return;
            }

            // Calculate the GPA needed this semester
            const pointsNeeded = (targetCGPA * (creditsCompleted + currentCredits)) - (currentCGPA * creditsCompleted);
            const targetSemesterGPA = pointsNeeded / currentCredits;
            
            // Calculate the NEW overall CGPA (if they hit their target)
            const newTotalPoints = (currentCGPA * creditsCompleted) + (targetSemesterGPA * currentCredits);
            const newTotalCredits = creditsCompleted + currentCredits;
            const newCGPA = newTotalPoints / newTotalCredits;

            const formData = new FormData();
            formData.append('new_gpa', newCGPA.toFixed(2));
            formData.append('new_credits', newTotalCredits);
            
            const resultDiv = document.getElementById('gpa-result');

            fetch('save_gpa.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                resultDiv.style.display = 'block';
                if (data.status === 'success') {
                    resultDiv.style.backgroundColor = '#e8f5e9';
                    resultDiv.style.color = '#2e7d32';
                    
                    // Update the form on the page!
                    document.getElementById('current_cgpa').value = newCGPA.toFixed(2);
                    document.getElementById('credits_completed').value = newTotalCredits;
                    // Clear the target fields
                    document.getElementById('target_cgpa').value = '';
                    document.getElementById('current_credits').value = '';

                } else {
                    resultDiv.style.backgroundColor = '#ffebee';
                    resultDiv.style.color = '#c62828';
                }
                resultDiv.innerText = data.message;
            })
            .catch(error => {
                console.error('Error:', error);
                resultDiv.innerText = "An error occurred while saving.";
            });
        }
    </script>

</body>
</html>