<?php
// Database connection settings
$host = 'localhost';
$dbname = 'db'; // Replace with your database name
$db_username = 'root'; // Replace with your database username
$db_password = ''; // Replace with your database password

// Establish the database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Retrieve user data using the user_id passed in the URL
$user_id = $_GET['user_id'] ?? null;
if ($user_id) {
    $query = "SELECT * FROM users WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} else {
    die("User ID not provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Next Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: #f4f4f9;
        }

        .content {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 50px;
        }

        .content img, .content video, .content canvas {
            width: 300px;
            height: 300px;
            border: 2px solid #ccc;
            border-radius: 10px;
            object-fit: cover;
        }

        .button-container {
            margin-top: 20px;
            display: flex;
            gap: 20px;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Welcome, <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h1>
    <div class="content">
        <!-- Display the user image stored in the database -->
        <img id="userImage" src="<?php echo htmlspecialchars($user['photo']); ?>" alt="User Image">
        <!-- Live camera feed -->
        <video id="camera" autoplay></video>
        <!-- Canvas for the captured image -->
        <canvas id="snapshot" hidden></canvas>
    </div>
    <div class="button-container">
        <button id="captureBtn">Capture Image</button>
        <button id="verifyBtn">Verify</button>
    </div>

    <form id="verificationForm" method="POST" action="compare.php" style="display: none;">
        <input type="hidden" name="capturedImageData" id="capturedImageData">
        <!-- Send the user image as base64 -->
        <input type="hidden" name="userImageBase64" id="userImageBase64" value="<?php echo base64_encode(file_get_contents($user['photo'])); ?>">
    </form>

    <script>
        const video = document.getElementById('camera');
        const canvas = document.getElementById('snapshot');
        const captureBtn = document.getElementById('captureBtn');
        const verifyBtn = document.getElementById('verifyBtn');
        const capturedImageData = document.getElementById('capturedImageData');
        const verificationForm = document.getElementById('verificationForm');

        // Access the camera and stream it into the video element
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    video.srcObject = stream;
                })
                .catch(error => {
                    console.error("Error accessing the camera: ", error);
                });
        } else {
            alert("Camera not supported on this device.");
        }

        // Capture the image when the button is clicked
        captureBtn.addEventListener('click', () => {
            const context = canvas.getContext('2d');
            // Set canvas dimensions equal to the video frame
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            // Draw the current frame from the video onto the canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Convert the canvas image to a base64 string and store it in the hidden input field
            const capturedImageBase64 = canvas.toDataURL('image/png');
            capturedImageData.value = capturedImageBase64;
            alert("Image captured successfully!");
        });

        // Handle the verification process when the Verify button is clicked
        verifyBtn.addEventListener('click', () => {
            // Submit the form to perform face comparison
            verificationForm.submit();
        });
    </script>
</body>
</html>
