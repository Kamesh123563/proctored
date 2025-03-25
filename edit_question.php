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

// Fetch question details for editing
if (isset($_GET['id'])) {
    $question_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM questions WHERE id = $question_id");
    $question = $result->fetch_assoc();
}

// Update question functionality
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $question_text = $_POST['question'];
    $type = $_POST['type'];
    $marks = $_POST['marks'];

    // For MCQ questions, we expect options
    $option1 = $_POST['option1'] ?? null;
    $option2 = $_POST['option2'] ?? null;
    $option3 = $_POST['option3'] ?? null;
    $option4 = $_POST['option4'] ?? null;

    // Update the question in the database
    if ($type === 'MCQ') {
        $stmt = $conn->prepare("UPDATE questions SET subject = ?, question = ?, type = ?, option1 = ?, option2 = ?, option3 = ?, option4 = ?, marks = ? WHERE id = ?");
        $stmt->bind_param("ssssssssi", $subject, $question_text, $type, $option1, $option2, $option3, $option4, $marks, $question_id);
    } else {
        $stmt = $conn->prepare("UPDATE questions SET subject = ?, question = ?, type = ?, marks = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $subject, $question_text, $type, $marks, $question_id);
    }

    if ($stmt->execute()) {
        $message = "Question updated successfully!";
    } else {
        $message = "Error updating question: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
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

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color:#4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Question</h2>

    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>

    <form action="edit_question.php?id=<?php echo $question['id']; ?>" method="POST">
        <label for="subject">Subject:</label>
        <select id="subject" name="subject" required>
            <option value="AI" <?php echo ($question['subject'] == 'AI') ? 'selected' : ''; ?>>Artificial Intelligence (AI)</option>
            <option value="IOT" <?php echo ($question['subject'] == 'IOT') ? 'selected' : ''; ?>>Internet of Things (IOT)</option>
            <option value="Cybersecurity" <?php echo ($question['subject'] == 'Cybersecurity') ? 'selected' : ''; ?>>Cybersecurity</option>
            <option value="Blockchain" <?php echo ($question['subject'] == 'Blockchain') ? 'selected' : ''; ?>>Blockchain</option>
        </select>

        <label for="question">Question:</label>
        <textarea id="question" name="question" rows="4" required><?php echo $question['question']; ?></textarea>

        <label for="type">Type:</label>
        <select id="type" name="type" required>
            <option value="MCQ" <?php echo ($question['type'] == 'MCQ') ? 'selected' : ''; ?>>MCQ</option>
            <option value="True/False" <?php echo ($question['type'] == 'True/False') ? 'selected' : ''; ?>>True/False</option>
        </select>

        <label for="marks">Marks:</label>
        <input type="number" id="marks" name="marks" value="<?php echo $question['marks']; ?>" required>

        <?php if ($question['type'] == 'MCQ') { ?>
            <label for="option1">Option 1:</label>
            <input type="text" id="option1" name="option1" value="<?php echo $question['option1']; ?>">

            <label for="option2">Option 2:</label>
            <input type="text" id="option2" name="option2" value="<?php echo $question['option2']; ?>">

            <label for="option3">Option 3:</label>
            <input type="text" id="option3" name="option3" value="<?php echo $question['option3']; ?>">

            <label for="option4">Option 4:</label>
            <input type="text" id="option4" name="option4" value="<?php echo $question['option4']; ?>">
        <?php } ?>

        <button type="submit">Save Changes</button>
    </form>

    <a href="view_questions.php" class="back-button">Back to View Questions</a>
</div>

</body>
</html>
