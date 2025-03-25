<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proctored Exam with Face Detection</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f0f0f0;
        }
        #exam-container {
            display: flex;
            flex-direction: row;
            width: 80%;
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        #webcam-box {
            width: 30%;
            text-align: center;
        }
        #question-box {
            width: 50%;
            padding-left: 20px;
        }
        #question-numbers {
            width: 20%;
            padding-left: 20px;
            text-align: left;
        }
        #timer {
            font-size: 18px;
            color: red;
            margin-bottom: 20px;
        }
        video {
            width: 100%;
            border: 2px solid black;
        }
        .question {
            margin-bottom: 10px;
        }
        .answered {
            color: green;
        }
        .unanswered {
            color: red;
        }
        .question-number {
            font-size: 20px;
            margin-bottom: 10px;
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            background-color: red;
            color: white;
            font-weight: bold;
        }
        .question-number.answered {
            background-color: green;
        }
        .question-number.unanswered {
            background-color: red;
        }

        #exam-form{
    max-height: 500px; /* Adjust this based on your screen size */
    overflow-y: auto; /* Enables scrolling */
    border: 1px solid #ccc;
    padding: 10px;
    width: 80%; /* Adjust width as needed */
    margin: auto; /* Centers the container */
    background-color: #f9f9f9;
}
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>
</head>
<body>

<div id="exam-container">
    <!-- Webcam box -->
    <div id="webcam-box">
        <h3>Webcam Feed</h3>
        <video id="webcam" autoplay></video>
        <p id="status">Detecting face...</p>
    </div>

    <!-- Questions and Timer -->
    <div id="question-box">
        <div id="timer">Time Remaining: <span id="time">10:00</span></div>
        
        <h3>Exam Questions</h3>
        <form id="exam-form">
            <!-- Dynamically loaded questions will appear here -->
        </form>
        <br>
        <div id="answered-status">
            <p>Questions Answered: <span id="answered-count">0</span></p>
        </div>
        <button type="button" onclick="submitExam()">Submit Exam</button>
    </div>

    <!-- Question numbers (right side) -->
    <div id="question-numbers">
        <h3>Questions</h3>
        <div id="question-list">
            <!-- Dynamically loaded question numbers will appear here -->
        </div>
    </div>
</div>

<!-- Audio for alarm -->
<audio id="alarm-sound" src="alarm.mp3" preload="auto"></audio>

<script>
    let examDuration = 10 * 60; // 10 minutes in seconds
    const timerDisplay = document.getElementById('time');
    const webcam = document.getElementById('webcam');
    const statusDisplay = document.getElementById('status');
    const alarmSound = document.getElementById('alarm-sound');
    let faceDetected = false;
    let answeredCount = 0;
    let totalQuestions = 0;
    let intervalID = null;
    let alarmPlayed = false; // Flag to ensure alarm plays only once per alert scenario
    let model;
    let verificationInterval;

       // Load questions from the database
    async function loadQuestions() {
        const response = await fetch('get_question.php'); // PHP script that fetches questions from the DB
        const questions = await response.json(); // Assume questions are returned in JSON format

        const form = document.getElementById('exam-form');
        const questionList = document.getElementById('question-list');
        totalQuestions = questions.length;

        questions.forEach((question, index) => {
            // Create question HTML for the form
            const questionDiv = document.createElement('div');
            questionDiv.classList.add('question');
            questionDiv.innerHTML = `
                <label>${index + 1}. ${question.question_text}</label><br>
                ${question.options.map(option => `
                    <input type="radio" name="q${index + 1}" value="${option}" onclick="updateAnsweredCount()"> ${option}<br>
                `).join('')}
            `;
            form.appendChild(questionDiv);

            // Create question number for the right side (initially red)
            const questionNumberDiv = document.createElement('div');
            questionNumberDiv.classList.add('question-number');
            questionNumberDiv.id = `q${index + 1}`;
            questionNumberDiv.classList.add('unanswered'); // Red color initially
            questionNumberDiv.innerHTML = `${index + 1}`;
            questionList.appendChild(questionNumberDiv);
        });
    }

    

    // Start the webcam and load the face detection model
    async function startExam() {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        webcam.srcObject = stream;

        try {
            // Load the BlazeFace face detection model
            model = await blazeface.load();
            console.log("BlazeFace model loaded", model);

            startFaceDetection(model);
            startCountdown();
            startFaceVerification(); // Start verification process
        } catch (error) {
            console.error('Error loading BlazeFace model:', error);
        }
    }

    // Real-time face detection using BlazeFace
    async function startFaceDetection(model) {
        const video = document.getElementById('webcam');
        const statusDisplay = document.getElementById('status');
        setInterval(async () => {
            const predictions = await model.estimateFaces(video);

            if (predictions.length === 1) {
                // One face detected
                faceDetected = true;
                statusDisplay.textContent = "Face detected!";
                console.log(predictions);
                alarmSound.pause(); // Ensure the sound is paused
                alarmSound.currentTime = 0; // Reset sound position
            } else if (predictions.length > 1) {
                // More than one face detected
                faceDetected = false;
                statusDisplay.textContent = "Multiple faces detected!";
                alarmSound.play(); // Play the alarm sound
                alert('Multiple faces detected! Please ensure only one person is in the frame.');
            } else {
                // No face detected
                faceDetected = false;
                statusDisplay.textContent = "No face detected!";
                alarmSound.play(); // Play the alarm sound
                alert('Face not detected! Please stay in front of the camera.');
            }
        }, 1000); // Check every second
    }

    // Function to start face verification every 2 minutes
    function startFaceVerification() {
        verificationInterval = setInterval(async () => {
            if (faceDetected) {
                // Capture a frame from the video feed
                const capturedImage = captureFrameFromVideo();
                
                // Send the captured frame to the server for comparison
                verifyCapturedImage(capturedImage);
            }
        }, 1 * 60 * 1000); // Every 2 minutes
    }

    // Capture a frame from the webcam feed
    function captureFrameFromVideo() {
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const video = document.getElementById('webcam');

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        return canvas.toDataURL('image/png');
    }

    // Function to send captured image to the server for verification
    function verifyCapturedImage(capturedImageData) {
        // Get the stored captured image path from the session
        $.ajax({
            url: 'verify_captured_image.php', // Create this PHP file to handle verification
            type: 'POST',
            data: {
                capturedImageData: capturedImageData
            },
            success: function(response) {
                console.log("Verification response:", response);
                if (response.trim() !== "match") {
                    alert("Face verification failed! The Registered student need to be in front of the camera.");
                    stopExam();
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                alert("An error occurred during face verification. Please stay in front of the camera.");
            }
        });
    }
     // Countdown timer
     function startCountdown() {
        const countdownInterval = setInterval(() => {
            let minutes = Math.floor(examDuration / 60);
            let seconds = examDuration % 60;

            // Display the time
            timerDisplay.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            
            // If time runs out, auto-submit the exam
            if (examDuration <= 0) {
                clearInterval(countdownInterval);
                alert('Time is up! Submit your Vote.');
                submitExam();
            } else {
                examDuration--;
            }
        }, 1000);
    }
     // Tab switching detection
     document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            alert('You switched tabs! This is not allowed during the Exam Process.');
        }
    });

    // Update the answered question count and color
    function updateAnsweredCount() {
        answeredCount = 0;
        const allQuestions = document.querySelectorAll('.question');
        allQuestions.forEach((question, index) => {
            const options = question.querySelectorAll('input[type="radio"]');
            const answered = Array.from(options).some(option => option.checked);

            // Update the color of the question number on the right
            const questionNumberDiv = document.getElementById(`q${index + 1}`);
            if (answered) {
                answeredCount++;
                questionNumberDiv.classList.add('answered');
                questionNumberDiv.classList.remove('unanswered');
            } else {
                questionNumberDiv.classList.add('unanswered');
                questionNumberDiv.classList.remove('answered');
            }
        });

        // Update answered count in the UI
        document.getElementById('answered-count').textContent = answeredCount;
    }

    // Submit exam function (this should send encrypted data)
    function submitExam() {
        const formData = new FormData(document.getElementById('exam-form'));
        formData.append('face_detected', faceDetected ? 'Yes' : 'No');

        // Send encrypted data via HTTPS (this example assumes SSL is enabled on your server)
        fetch('submit_exam.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            alert('Exam submitted successfully!');
            window.location.href = 'thank_you.html';
            console.log(data); // Server response
        });
    }

    // Alert when the user switches tabs
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            alarmSound.play(); // Play alarm if the user switches tabs
            alert('You switched tabs! Please stay focused on the exam.');
        }
    });

   // Initialize the exam  
document.addEventListener('DOMContentLoaded', () => {  
    startExam();  
    loadQuestions();  
});;
</script>
</body>
</html>
