<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Business Management App</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Adjusted path for root level -->
    <style>
        /* Additional styles for the landing page */
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-color: #f0f2f5; /* Light Pinkish/Blueish tint */
        }
        .landing-container {
            text-align: center;
            padding: 40px;
            background-color: #ffffff; /* White */
            border-radius: 10px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .landing-container h1 {
            color: #007bff; /* Blue */
            margin-bottom: 20px;
            font-size: 2.5em;
        }
        .landing-container p {
            color: #555;
            font-size: 1.1em;
            margin-bottom: 30px;
        }
        .landing-actions a {
            margin: 0 10px;
            padding: 15px 30px;
            font-size: 1.1em;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .action-login {
            background-color: #007bff; /* Blue */
            color: white;
        }
        .action-login:hover {
            background-color: #0056b3; /* Darker Blue */
        }
        .action-submit-request {
            background-color: #28a745; /* Green */
            color: white;
        }
        .action-submit-request:hover {
            background-color: #1e7e34; /* Darker Green */
        }
        .action-register {
            background-color: #6c757d; /* Secondary/Grey */
            color: white;
        }
        .action-register:hover {
            background-color: #545b62; /* Darker Grey */
        }
    </style>
</head>
<body>
    <div class="landing-container">
        <h1>Welcome to the Business Management App</h1>
        <p>Your one-stop solution for managing customer requests and business operations efficiently.</p>
        <div class="landing-actions">
            <a href="php/login.php" class="action-login">Admin Login</a>
            <a href="php/submit_request.php" class="action-submit-request">Submit a Request</a>
            <a href="php/register.php" class="action-register">Register (Admin)</a>
        </div>
    </div>
</body>
</html>
