<!DOCTYPE html>
<html>
<head>
    <title>Vista_Administracion</title>
    <link rel="stylesheet" type="text/css" href="estiloadmin.css"> 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Agrega la librería html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.5.0-beta4/html2canvas.min.js"></script>
</head>
<body>
    <h1>ADMINISTRACION</h1>

    <!-- Selector para el tipo de sensor -->
    <select id="sensorType">
        <option value="real">Sensor Real</option>
        <option value="ficticio">Sensor Ficticio</option>
    </select>

    <!-- Agregar un nuevo sensor -->
    <div id="agregarSensor">
        <input type="text" id="nombreSensor" placeholder="Nombre del Sensor">
        <button id="btnAgregarSensor">Agregar Sensor</button>
    </div>

    <canvas id="co2Chart"></canvas>
    <form>
        <label for="fechaInicio">Fecha de inicio:</label>
        <input type="date" id="fechaInicio" name="fechaInicio">
        <label for="fechaFin">Fecha de fin:</label>
        <input type="date" id="fechaFin" name="fechaFin">
        <input type="submit" value="Mostrar Datos">
    </form>

    <!-- Botón para descargar reporte -->
    <button id="descargarReporte">Descargar Reporte</button>

    <!-- Botón para descargar imagen -->
    <button id="descargarImagen">Descargar Imagen</button>

    <!-- Botón para cerrar sesión -->
    <button id="cerrarSesion">Cerrar Sesion</button>

    <div id="tablaContainer" style="display: none;">
        <table id="co2Table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>CO2</th>
                </tr>
            </thead>
            <tbody>
                <!-- Datos de la tabla se cargarán aquí -->
            </tbody>
        </table>
    </div>

    <script>
        // Variables con los datos de tu canal y clave de lectura de ThingSpeak
        var channelId = 2061125;
        var readApiKey = "7Q49OISGCTDWLNAV";

        // Variable para almacenar los sensores ficticios y sus datos
        var sensoresFicticios = {};

        // Evento para agregar un sensor cuando se hace clic en el botón "Agregar Sensor"
        $('#btnAgregarSensor').click(function(event) {
            event.preventDefault();
            var nombreSensor = $('#nombreSensor').val();

            if (nombreSensor.trim() === '') {
                alert('Ingresa un nombre válido para el sensor.');
                return;
            }

            agregarSensor(nombreSensor);
        });

        // Función para agregar un sensor al servidor y al desplegable
        function agregarSensor(nombreSensor) {
            $.ajax({
                url: 'backend.php',
                method: 'POST',
                data: { action: 'add_sensor', nombre_sensor: nombreSensor },
                success: function(response) {
                    if (response === 'success') {
                        // Recargar el desplegable con sensores disponibles
                        cargarSensoresDisponibles();
                    } else {
                        alert('Error al agregar el sensor.');
                    }
                }
            });
        }

        // Función para cargar los sensores disponibles en el desplegable
        function cargarSensoresDisponibles() {
            $.ajax({
                url: 'backend.php',
                method: 'GET',
                data: { action: 'get_sensors' },
                dataType: 'json',
                success: function(response) {
                    if (response && response.length > 0) {
                        // Limpiar y cargar opciones de sensores en el desplegable
                        $('#sensorType').empty();
                        $('#sensorType').append($('<option>', {
                            value: 'real',
                            text: 'Sensor Real'
                        }));

                        $.each(response, function(index, sensor) {
                            $('#sensorType').append($('<option>', {
                                value: sensor.id,
                                text: sensor.nombre_sensor
                            }));
                        });
                    }
                }
            });
        }

        // Función para obtener datos del sensor real desde ThingSpeak
        function obtenerDatosSensorReal(fechaInicio, fechaFin) {
            var url = "https://api.thingspeak.com/channels/" + channelId + "/feeds.json?api_key=" + readApiKey + "&start=" + fechaInicio + "&end=" + fechaFin;

            $.getJSON(url, function(data) {
                if (data.feeds.length > 0) {
                    var labels = [];
                    var values = [];

                    $.each(data.feeds, function(index, value) {
                        labels.push(value.created_at);
                        values.push(value.field1);
                    });

                    // Crear la gráfica con Chart.js
                    crearGrafica(labels, values);
                } else {
                    alert('No hay datos disponibles para el rango de fechas seleccionado.');
                }
            });
        }

        // Función para simular datos del sensor ficticio
        function simularDatosSensorFicticio(sensorId, fechaInicio, fechaFin) {
            if (!sensoresFicticios[sensorId]) {
                sensoresFicticios[sensorId] = {
                    labels: [],
                    values: []
                };
            }

            var sensor = sensoresFicticios[sensorId];
            var startDate = new Date(fechaInicio);
            var endDate = new Date(fechaFin);

            // Simula datos aleatorios dentro del rango de fechas seleccionado
            while (startDate <= endDate) {
                var fecha = startDate.toISOString().slice(0, 10);
                var co2 = Math.random() * 1000; // Datos aleatorios entre 0 y 1000 ppm
                sensor.labels.push(fecha);
                sensor.values.push(co2);
                startDate.setDate(startDate.getDate() + 1); // Avanza un día
            }

            // Crea la gráfica con los datos aleatorios
            crearGrafica(sensor.labels, sensor.values);
        }

        // Función para crear la gráfica
        function crearGrafica(labels, values) {
            var ctx = document.getElementById('co2Chart').getContext('2d');
            var chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'CO2',
                        data: values,
                        backgroundColor: 'rgba(255, 0, 0, 0.2)',
                        borderColor: 'rgba(255, 0, 0, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });

            // Mostrar tabla al hacer clic en el botón "Descargar Reporte"
            $('#descargarReporte').click(function(event) {
                event.preventDefault();
                descargarReporte(labels, values);
            });
        }

        // Función para descargar el reporte en formato CSV
        function descargarReporte(labels, values) {
            var csvContent = "Fecha,CO2\n";
            for (var i = 0; i < labels.length; i++) {
                csvContent += labels[i] + "," + values[i] + "\n";
            }

            var blob = new Blob([csvContent], { type: 'text/csv' });
            var url = URL.createObjectURL(blob);
            var link = document.createElement('a');
            link.href = url;
            link.download = 'reporte_co2.csv';
            link.click();
        }

        // Evento para mostrar datos cuando se hace clic en el botón "Mostrar Datos"
        $('form').submit(function(event) {
            event.preventDefault();
            var sensorType = $('#sensorType').val();
            var fechaInicio = $('#fechaInicio').val();
            var fechaFin = $('#fechaFin').val();

            if (sensorType === 'real') {
                obtenerDatosSensorReal(fechaInicio, fechaFin);
            } else if (sensorType === 'ficticio') {
                simularDatosSensorFicticio("ficticio", fechaInicio, fechaFin);
            } else {
                // Obtener el ID del sensor ficticio personalizado
                var sensorId = $('#sensorType').val();
                simularDatosSensorFicticio(sensorId, fechaInicio, fechaFin);
            }
        });

        // Evento para descargar la imagen de la gráfica
        $('#descargarImagen').click(function() {
            // Obtener el canvas de la gráfica
            var canvas = document.getElementById('co2Chart');

            // Utilizar html2canvas para capturar la imagen del canvas
            html2canvas(canvas).then(function(canvasImage) {
                // Crear un enlace temporal para la descarga
                var a = document.createElement('a');
                a.href = canvasImage.toDataURL('image/png'); // Convertir el canvas en una imagen PNG
                a.download = 'grafica_co2.png'; // Nombre del archivo a descargar
                a.style.display = 'none';

                // Agregar el enlace temporal al documento y hacer clic para descargar
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            });
        });

        // Evento para cerrar sesión al hacer clic en el botón "Cerrar Sesión"
        $('#cerrarSesion').click(function() {
            // Redirigir al usuario a la página de inicio de sesión
            window.location.href = 'login_register.html'; 
    });

        // Inicialmente, cargar sensores disponibles en el desplegable
        cargarSensoresDisponibles();
    </script>
</body>
</html>

