<?php
$servername = "localhost"; // Your database server
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "db"; // Your database name

// Create a database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $subject = $_POST['subject'];
    $question = $_POST['question'];
    $type = $_POST['type'];
    $marks = $_POST['marks'];

    // For MCQ questions, we expect options
    $option1 = $_POST['option1'] ?? null;
    $option2 = $_POST['option2'] ?? null;
    $option3 = $_POST['option3'] ?? null;
    $option4 = $_POST['option4'] ?? null;

    // Prepare the SQL query to insert the question into the database
    if ($type === 'MCQ') {
        $stmt = $conn->prepare("INSERT INTO questions (subject, question, type, option1, option2, option3, option4, marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $subject, $question, $type, $option1, $option2, $option3, $option4, $marks);
    } else {
        // Handle other types, without options
        $stmt = $conn->prepare("INSERT INTO questions (subject, question, type, marks) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $subject, $question, $type, $marks);
    }

    // Execute the query
    if ($stmt->execute()) {
        $message = "Question created successfully!";
    } else {
        $message = "Error: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch all questions from the database to show on the page
$questions_result = $conn->query("SELECT * FROM questions");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Create Questions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 600px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 15px 0 5px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        .question-list {
            margin-top: 40px;
        }

        .question-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .question-list table, th, td {
            border: 1px solid #ddd;
        }

        .question-list th, td {
            padding: 8px;
            text-align: left;
        }

    </style>
</head>
<body>

<div class="container">
    <h2>Create Question</h2>

    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <form id="create-question-form" action="create_question.php" method="POST">
        <label for="subject">Subject:</label>
        <select id="subject" name="subject" required>
            <option value="">Select Subject</option>
            <option value="Artificial Intelligence(AI)">Artificial Intelligence(AI)</option>
            <option value="Internet of Things(IOT)">Internet of Things(IOT)</option>
            <option value="Cybersecurity">Cybersecurity</option>
            <option value="Blockchain">Blockchain</option>
        </select>

        <label for="question">Question:</label>
        <textarea id="question" name="question" rows="3" required></textarea>

        <label for="type">Question Type:</label>
        <select id="type" name="type" required>
            <option value="">Select Type</option>
            <option value="MCQ">Multiple Choice</option>
        </select>

        <div id="options-container" style="display: none;">
            <label for="option1">Option 1:</label>
            <input type="text" id="option1" name="option1">

            <label for="option2">Option 2:</label>
            <input type="text" id="option2" name="option2">

            <label for="option3">Option 3:</label>
            <input type="text" id="option3" name="option3">

            <label for="option4">Option 4:</label>
            <input type="text" id="option4" name="option4">
        </div>

        <label for="marks">Marks:</label>
        <input type="number" id="marks" name="marks" required min="1">

        <button type="submit">Create Next Question</button>
        <a href="upload.php">
    <button type="button">Upload Doc</button>
</a>
    </form>

    <a href="view_questions.php" style="margin-top: 20px; display: block; text-align: center; color: blue;">Proceed to Question Display Page</a>


<script>
    const typeSelect = document.getElementById('type');
    const optionsContainer = document.getElementById('options-container');

    typeSelect.addEventListener('change', () => {
        if (typeSelect.value === 'MCQ') {
            optionsContainer.style.display = 'block';
        } else {
            optionsContainer.style.display = 'none';
        }
    });
</script>

</body>
</html>
