<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mylogin";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Conexion fallida: " . $conn->connect_error);
    }

    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    $sql = "SELECT * FROM usuarios WHERE username='$username' AND password='$password' AND role='$role'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $_SESSION["username"] = $username;
        $_SESSION["userRole"] = $role;

        if ($role === "cliente") {
            header("Location: sensor_co2.html");
        } elseif ($role === "administrador") {
            header("Location: admin.php");
        }

        exit();
    } else {
        echo "<p>Credenciales invalidas. Intentalo nuevamente.</p>";
    }

    $conn->close();
}
?>
