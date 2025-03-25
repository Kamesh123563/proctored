<?php
if (isset($_POST['capturedImageData']) && isset($_POST['userImageBase64'])) {
    $capturedImageData = $_POST['capturedImageData'];
    $userImageBase64 = $_POST['userImageBase64'];

    // Save base64 images as temporary files
    function saveBase64ImageToFile($base64Image, $filename) {
        $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        file_put_contents($filename, $imageData);
    }

    // Save user image and captured images to temporary files
    $user_image_path = 'temp_user_image.png';  // Renaming to user_image
    $captured_image_path = 'temp_captured_image.png';

    // Save images
    saveBase64ImageToFile($userImageBase64, $user_image_path);
    saveBase64ImageToFile($capturedImageData, $captured_image_path);

    // Store the captured image path in a session for future use
    session_start();
    $_SESSION['temp_captured_image_path'] = $captured_image_path;

    // Call the Python script to compare the images
    $command = escapeshellcmd("python compare_faces.py " . escapeshellarg($user_image_path) . " " . escapeshellarg($captured_image_path));
    $output = shell_exec($command);
    
    // Optionally, you can delete the user image if you no longer need it, but keep the captured image
    if (file_exists($user_image_path)) {
        if (!unlink($user_image_path)) {
            error_log("Error deleting user image at: " . $user_image_path);
        }
    }
    
    // Display the result and handle redirection
    if (strpos($output, "Match") !== false) {
        // Redirect to wardpage.html on match
        header("Location: exam.php");
        exit();
    } else {
        // Redirect to error_face.html if faces do not match
        header("Location: error_face.html");
        exit();
    }
} else {
    echo "Error: Missing image data.";
}
?>
