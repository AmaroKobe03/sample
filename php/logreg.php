<?php
require_once 'db_connect.php';

$name = $email = $password = '';
$error = '';
$login_error = '';

// Handle Registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $hashed_password);

        if ($stmt->execute()) {
            header("Location: success.php");
            exit();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle Login
if ($_SERVER ['REQUEST_METHOD'] == 'POST' && isset ($_POST['login'])){
    $email = $_POST ['email'] ?? '' ;
    $password = $_POST ['password'] ?? '' ;

    if(empty($email) || empty($password)){
        $login_error = "All Fields must be filled";
    }else {
        $stmt = $conn -> prepare ("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if($user && password_verify($password, $user['password'])){
            $_SESSION = [
                'user_id'=>  $user['id'],
                'user_name' => $user['name'],
                'logged_in' => true
            ];
            header("Location: dashboard.php");
            exit();
        }else{
            $login_error = $user ? "Incorrect Password" : "No account found with this email";
        }
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../src/output.css" rel="stylesheet">
    <title>Login</title>
</head>

<body class="flex items-center justify-center h-screen w-screen">


    <!-- Login Form -->
    <form id="login-modal" method="POST" class="bg-blue-200 w-[50vh] h-[40vh] flex flex-col items-center gap-4 p-4">
        <h1 class="text-3xl font-bold">LOGIN</h1>

        <?php if (!empty($login_error)): ?>
            <div class="text-red-500"><?php echo $login_error ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label for="login-email">Enter Email</label>
            <input type="email" name="email" class="p-2 border rounded border-gray-200"
                placeholder="Enter Email" required >
        </div>

        <div class="input-group">
            <label for="login-password">Enter Password</label>
            <input type="password" name="password" class="p-2 border rounded border-gray-200"
                placeholder="Enter Password" required >
        </div>

        <button type="submit" name="login" class="px-4 py-2 bg-blue-500 text-white rounded">
            Login
        </button>
        <a id="regbtn" href="#" class="underline cursor-pointer">Don't have an account? Register</a>
    </form>

    <!-- Registration Form -->
    <form id="register-modal" method="POST" class="hidden bg-blue-200 w-[50vh] h-[40vh] flex flex-col items-center gap-4 p-4">
        <h1 class="text-3xl font-bold">REGISTER</h1>

        <?php if (!empty($error)): ?>
            <div class="text-red-500"><?php echo $error ?></div>
        <?php endif; ?>

        <div class="input-group">
            <label for="reg-name">Enter Name</label>
            <input type="text" name="name" class="p-2 border rounded border-gray-200"
                placeholder="Enter Name" required autocomplete="off">
        </div>

        <div class="input-group">
            <label for="reg-email">Enter Email</label>
            <input type="email" name="email" class="p-2 border rounded border-gray-200"
                placeholder="Enter Email" required autocomplete="off">
        </div>

        <div class="input-group">
            <label for="reg-password">Enter Password</label>
            <input type="password" name="password" class="p-2 border rounded border-gray-200"
                placeholder="Enter Password" required autocomplete="off">
        </div>

        <button type="submit" name="register" class="px-4 py-2 bg-blue-500 text-white rounded">
            Register
        </button>
        <a id="logbtn" href="#" class="underline cursor-pointer">Already have an account? Login</a>
    </form>
</body>
<script>
    const register = document.getElementById("regbtn");
    const login = document.getElementById("logbtn");
    const logmodal = document.getElementById("login-modal");
    const regmodal = document.getElementById("register-modal");

    register.addEventListener('click', (e) => {
        e.preventDefault();
        regmodal.classList.remove("hidden");
        logmodal.classList.add("hidden");
    });

    login.addEventListener('click', (e) => {
        e.preventDefault();
        logmodal.classList.remove("hidden");
        regmodal.classList.add("hidden");
    })
</script>

</html>