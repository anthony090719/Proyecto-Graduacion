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
    $raw_password = $_POST["password"];
    $role = $_POST["role"];

    // Validar que la contraseña no esté vacía
    if (empty($raw_password)) {
        echo "La contraseña no puede estar vacía.";
        exit(); // Salir del script si la contraseña está vacía
    }

    // Generar un hash seguro de la contraseña
    $password = password_hash($raw_password, PASSWORD_DEFAULT);

    // Utiliza consultas parametrizadas para evitar la inyección SQL
    $sql = "SELECT * FROM usuarios WHERE username=? AND password=? AND role=?";
    $stmt = $conn->prepare($sql);

    // Vincula los parámetros
    $stmt->bind_param("sss", $username, $password, $role);

    $stmt->execute();
    $result = $stmt->get_result();

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
        echo "<p>Credenciales invalidas. Inténtalo nuevamente.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
