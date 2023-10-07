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
    $raw_password = $_POST["password"];
    $role = $_POST["role"];

    // Validar que la contraseña no esté vacía
    if (empty($raw_password)) {
        echo "La contraseña no puede estar vacía.";
        exit(); // Salir del script si la contraseña está vacía
    }

    // Generar un hash seguro de la contraseña
    $password = password_hash($raw_password, PASSWORD_BCRYPT);

    // Utiliza una consulta parametrizada
    $sql = "INSERT INTO usuarios (username, password, role) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Vincula los parámetros
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        echo "Usuario registrado correctamente.";
    } else {
        echo "Error al registrar el usuario: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
