<?php
session_start();

require_once 'db_connect.php';

$name = $email = $password = '';
$error = '';
$login_error = '';

//handle register
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset ($_POST['register'])){
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($name) || empty($email) || empty($password)){
        $error = "All fields must be filled";
    }else{
        $hashed_password =  password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if($stmt->execute()){
        header("Location: success.php");
        exit();
    }else{
        $error = "error: " . $stmt->error;
    }
    $stmt->close();
    }
}
//handle login
if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset($_POST ['login'])){
    $email = $_POST ['email'] ?? '';    
    $password = $_POST ['password'] ?? '';
    
    if(empty($email) || empty($password)){
        $login_error = "All fields must be filled";
    }else{
        $stmt = $conn -> prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt -> bind_param("s", $email);
        $stmt -> execute();
        $user = $stmt -> get_result()->fetch_assoc();

        if($user && password_verify($password, $user['password'])){
            $_SESSION = [
                'user_id' => $user ['id'],
                'user_name' => $user ['name'],
                'logged_in' => true
            ];
            header("Location: user_management.php");
            exit();
        }else{
            $login_error = $user ? "Incorrect Password" : "Email does not exist";
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

<body class="h-screen w-screen flex items-center justify-center">
    <form action="" id="login-modal" method="POST" class="w-[50vh] h-[40vh] bg-green-200 flex flex-col items-center justify-center gap-4">
        <h1 class="text-2xl">Login</h1>

        <?php if (!empty($login_error)): ?>
            <div class="text-red-500"><?php echo $login_error ?></div>
        <?php endif; ?>

        <div class="flex flex-col">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" class="px-4 py-2 border" required>
        </div>
        <div class="flex flex-col">
            <label for="email">Password</label>
            <input type="password" name="password" id="password" class="py-2 px-4 border" required>
        </div>
        <button type="submit" name="login" class="bg-blue-200 py-2 px-4">Login</button>
        <p>don't have an account yet?<a href="" id="registerbtn" class="underline">Register</a></p>
    </form>
    
    <form id="register-modal" method="POST" class="hidden w-[50vh] h-[40vh] bg-green-200 flex flex-col items-center justify-center gap-4">
        <h1 class="text-2xl">Register</h1>

            <?php if(!empty($error)): ?>
                <div class="text-red-400"> <?php echo $error ?></div>
            <?php endif; ?>

        <div class="flex flex-col">

            <label for="name">Name</label>
            <input type="text" name="name" id="name" class="px-4 py-2 border" required>
        </div>
        <div class="flex flex-col">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" class="px-4 py-2 border" required>
        </div>
        <div class="flex flex-col">
            <label for="email">Password</label>
            <input type="password" name="password" id="password" class="py-2 px-4 border" required>
        </div>
        <button type="submit" name="register" class="bg-blue-200 py-2 px-4">Register</button>
        <p>Already have an account yet?<a href="" id="loginbtn" class="underline">Login</a></p>
    </form>
</body>
<script>
    const register = document.getElementById("registerbtn");
    const login = document.getElementById("loginbtn");
    const regmodal = document.getElementById("register-modal");
    const logmodal = document.getElementById("login-modal");

    register.addEventListener('click', (e) => {
        e.preventDefault();
        regmodal.classList.remove("hidden");
        logmodal.classList.add("hidden");
    });
    login.addEventListener('click', (e) => {
        e.preventDefault();
        regmodal.classList.add("hidden");
        logmodal.classList.remove("hidden");
    });
</script>
</html>