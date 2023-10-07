<?php
// Configurar la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mylogin";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error en la conexión a la base de datos: " . $conn->connect_error);
}

// Acción para agregar un sensor a la base de datos
if ($_POST['action'] == 'add_sensor') {
    $nombreSensor = $_POST['nombre_sensor'];
    $sql = "INSERT INTO sensores (nombre_sensor) VALUES (?)"; // Usar una consulta parametrizada
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nombreSensor); // Vincular el parámetro

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}

// Acción para obtener todos los sensores disponibles
if ($_GET['action'] == 'get_sensors') {
    $sql = "SELECT id, nombre_sensor FROM sensores";
    $result = $conn->query($sql);

    $sensors = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sensors[] = $row;
        }
    }

    echo json_encode($sensors);
}

// Acción para obtener datos de un sensor ficticio personalizado
if ($_GET['action'] == 'get_sensor_data') {
    $sensorId = $_GET['sensor_id']; // Obtén el ID del sensor ficticio personalizado desde la solicitud

    // Convierte $sensorData en un formato JSON para enviarlo al frontend
    echo json_encode($sensorData);
}

$stmt->close();
$conn->close();
?>
