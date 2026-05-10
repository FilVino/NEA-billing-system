<?php
session_start();

// connect to database
$db = mysqli_connect('localhost', 'root', '', 'billing_system');

// variable declaration
$username = "";
$email = "";
$errors = array();

// call the register() function if register_btn is clicked
if (isset($_POST['register_btn'])) {
    register();
}

// call the login() function if register_btn is clicked
if (isset($_POST['login_btn'])) {
    login();
}

if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['user']);
    header("location: ../login.php");
}

function callApi($path, $data)
{
    $url = "http://localhost:3000" . $path;
    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
        return ['success' => false, 'message' => 'API connection failed: ' . $error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        return ['success' => false, 'message' => 'Invalid API response'];
    }

    return $result;
}

// REGISTER USER
function register()
{
    global $db, $errors, $username, $email;

    // receive all input values from the form
    $username = e($_POST['username']);
    $email = e($_POST['email']);
    $password_1 = e($_POST['password_1']);
    $password_2 = e($_POST['password_2']);

    // form validation: ensure that the form is correctly filled
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($email)) {
        array_push($errors, "Email is required");
    }
    if (empty($password_1)) {
        array_push($errors, "Password is required");
    }
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }

    // register user if there are no errors in the form
    if (count($errors) == 0) {
        $apiResponse = callApi('/register', [
            'username' => $username,
            'email' => $email,
            'password' => $password_1,
            'user_type' => 'user'
        ]);

        if ($apiResponse['success']) {
            $_SESSION['user'] = $apiResponse['user'];
            header('location: index.php');
            exit();
        }

        array_push($errors, $apiResponse['message']);
    }
}

// LOGIN USER
function login()
{
    global $db, $username, $errors;

    // grab form values
    $username = e($_POST['username']);
    $password = e($_POST['password']);

    // make sure form is filled properly
    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    // attempt login if no errors on form
    if (count($errors) == 0) {
        $apiResponse = callApi('/login', [
            'username' => $username,
            'password' => $password
        ]);

        if ($apiResponse['success']) {
            $logged_in_user = $apiResponse['user'];
            $_SESSION['user'] = $logged_in_user;

            if ($logged_in_user['user_type'] == 'admin') {
                header('location: admin/home.php');
            } else {
                header('location: index.php');
            }
            exit();
        }

        array_push($errors, $apiResponse['message']);
    }
}

function isLoggedIn()
{
    if (isset($_SESSION['user'])) {
        return true;
    } else {
        return false;
    }
}

function isAdmin()
{
    if (isset($_SESSION['user']) && $_SESSION['user']['user_type'] == 'admin') {
        return true;
    } else {
        return false;
    }
}

// escape string
function e($val)
{
    global $db;
    return mysqli_real_escape_string($db, trim($val));
}

function display_error()
{
    global $errors;

    if (count($errors) > 0) {
        echo '<div class="error">';
        foreach ($errors as $error) {
            echo $error . '<br>';
        }
        echo '</div>';
    }
}

?>