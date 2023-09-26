<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Add your custom CSS styles or link to an external stylesheet here -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            background-color: #ffffff;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }

        .container h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .container label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #777;
        }

        .container input[type="text"],
        .container input[type="password"],
        .container input[type="submit"] {
            width: 100%;
            padding: 12px 20px;
            margin: 8px 0;
            display: inline-block;
            border: 1px solid #ccc;
            box-sizing: border-box;
            border-radius: 3px;
        }

        .container input[type="submit"] {
            background-color: #007BFF;
            color: #fff;
            cursor: pointer;
            font-weight: bold;
        }

        .container input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        
        
        <?php if (session()->getFlashdata('error')): ?>
            <div style="color: red; font-weight: bold; margin-bottom: 10px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= site_url('/login') ?>">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
