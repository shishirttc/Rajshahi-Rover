<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>অ্যাডমিন লগইন</title>
    <style>
        body {
            font-family: 'SolaimanLipi', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #e0f2f7;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 400px;
            width: 90%;
            margin: 20px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            box-sizing: border-box;
            text-align: center;
        }
        h1 {
            color: #00796b;
            margin-bottom: 30px;
            font-size: 2em;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #004d40;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #b2dfdb;
            border-radius: 6px;
            font-size: 1.1em;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #00796b;
            outline: none;
            box-shadow: 0 0 5px rgba(0,121,107,0.3);
        }
        button {
            background-color: #00796b;
            color: white;
            border: none;
            padding: 12px 25px;
            cursor: pointer;
            border-radius: 6px;
            font-size: 1.1em;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        button:hover {
            background-color: #004d40;
            transform: translateY(-2px);
        }
        button:active {
            transform: translateY(0);
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1em;
            font-weight: bold;
            text-align: center;
        }
        .error {
            background-color: #ffebee;
            border: 1px solid #f44336;
            color: #d32f2f;
        }

        /* Responsive adjustments */
        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
            h1 {
                font-size: 1.8em;
            }
            input, button {
                font-size: 1em;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>অ্যাডমিন লগইন</h1>
        <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo '<div class="message error"><p>' . htmlspecialchars($_SESSION['login_error']) . '</p></div>';
            unset($_SESSION['login_error']);
        }
        ?>
        <form action="authenticate.php" method="POST">
            <div class="form-group">
                <label for="username">ইউজারনেম:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">পাসওয়ার্ড:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">লগইন করুন</button>
        </form>
    </div>
</body>
</html>