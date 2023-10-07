<?php
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

    $sql = "INSERT INTO usuarios (username, password, role) VALUES ('$username', '$password', '$role')";
    if ($conn->query($sql) === TRUE) {
        echo "Usuario registrado correctamente.";
    } else {
        echo "Error al registrar el usuario: " . $conn->error;
    }

    $conn->close();
}
?>

