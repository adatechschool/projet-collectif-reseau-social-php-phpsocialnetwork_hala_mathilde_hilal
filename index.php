<?php include("config.php")?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome on Sky</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=YourFontFamily:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="sky">
    <div class="cloud small right cloud1"></div>
    <div class="cloud small left cloud2"></div>
    <div class="cloud small large cloud3"></div>
    <div class="cloud small left cloud4"></div>
    <div class="cloud small left cloud5"></div>
    <div class="cloud small large cloud6"></div>
    <div class="cloud small right cloud7"></div>
    <div class="cloud small left cloud8"></div>
    <div class="cloud small left cloud9"></div>
    <div class="cloud small right cloud10"></div>
    <div class="title">Welcome on Sky</div>
     <!-- Form login -->
     <div class="login-form">
        <form action="process_login.php" method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
    <!-- Form registration -->
    <div class="registration-form">
    <form action="process_registration.php" method="POST">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <button type="submit">Subscribe</button>
        </form>
    </div>
  </div>
</body>
</html>
