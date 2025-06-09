<?php
session_start();
require_once 'db_connect.php';


$email = $password = '';
$login_error='';


if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)){
        $login_error = "Both email and password are required!";
    }else {
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows === 1){
            $user = $result->fetch_assoc();

            if(password_verify($password, $user['password'])){

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['logged_in'] = true;

                header("Location: dashboard.php");
                exit();
            } else{
                $login_error = "Incorrect Password";
            }
        }else {
            $login_error = "No Account found with this email!";

        }
        $stmt->close();
    }
}
require_once 'logreg.php';
?>