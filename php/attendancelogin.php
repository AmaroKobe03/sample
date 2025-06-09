<?php 
    session_start();
    require_once('db_connect.php');

    $login_error = '';

    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])){
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if(empty($username) || empty($password)){
            $login_error = "All fields are required";
        }else{
            $stmt = $conn -> prepare("SELECT adminid, username, password, role FROM users WHERE username = ?");
            $stmt -> bind_param("s", $username);
            $stmt -> execute();
            $result = $stmt -> get_result();
            $user = $result->fetch_assoc();

            if($user){
                if($password === $user['password']){ 
                    header("Location: attendancedash.php");
                    exit();
                } else {
                    $login_error = "Incorrect Password";
                }
            } else {
                $login_error = "Username not found";
            }
        }
    }

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="../src/output.css" rel="stylesheet">
</head>
<body class="h-screen w-screen flex flex-row items-center justify-center">
    <form action="" id="login-modal" method="POST" class="w-[50vh] h-[40vh] bg-green-200 flex flex-col items-center justify-center gap-4">
        <h1 class="text-2xl font-bold">Login</h1>

        <?php if (!empty($login_error)): ?>
            <div class="text-red-500"><?php echo $login_error ?></div>
        <?php endif; ?>

        <div class="flex flex-col">
            <label for="username">Username</label>
            <input type="text" name="username" id="email" class="px-4 py-2 border" required>
        </div>
        <div class="flex flex-col">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="py-2 px-4 border" required>
        </div>
        <button type="submit" name="login" class="bg-blue-200 py-2 px-4">Login</button>
        <p>don't have an account yet?<a href="" id="registerbtn" class="underline">Register</a></p>
    </form>
</body>
</html>