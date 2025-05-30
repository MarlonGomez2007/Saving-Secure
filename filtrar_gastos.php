<?php

include 'db.php'; // Conexión a la base de datos

$id_usuario = $_SESSION['id_usuario']; 



// Obtener filtros

$nombre = $_POST['nombre'] ?? '';

$fechaDesde = $_POST['fecha_desde'] ?? '';

$fechaHasta = $_POST['fecha_hasta'] ?? '';

$categoria = $_POST['categoria'] ?? '';

$montoMin = $_POST['monto_min'] ?? '';

$montoMax = $_POST['monto_max'] ?? '';



// Construir consulta SQL dinámica

$query = "SELECT * FROM gastos WHERE id_usuario = $id_usuario";

if (!empty($nombre)) {

    $query .= " AND nombre LIKE '%$nombre%'";

}

if (!empty($fechaDesde)) {

    $query .= " AND fecha >= '$fechaDesde'";

}

if (!empty($fechaHasta)) {

    $query .= " AND fecha <= '$fechaHasta'";

}

if (!empty($categoria)) {

    $query .= " AND categoria = '$categoria'";

}

if (!empty($montoMin)) {

    $query .= " AND monto >= $montoMin";

}

if (!empty($montoMax)) {

    $query .= " AND monto <= $montoMax";

}



$result = mysqli_query($conn, $query);

$output = "";



while ($row = mysqli_fetch_assoc($result)) {

    $output .= "<tr>

        <td>" . htmlspecialchars($row['nombre']) . "</td>

        <td>" . htmlspecialchars($row['descripcion']) . "</td>

        <td>" . date("d-m-Y", strtotime($row['fecha'])) . "</td>

        <td>" . number_format($row['monto'], 0) . " COP</td>

        <td>" . htmlspecialchars($row['categoria']) . "</td>

        <td>

            <button class='btn-editar' data-id='" . $row['id'] . "'>Editar</button>

            <button class='btn-borrar' onclick='confirmarBorrado(" . $row['id'] . ")'>Borrar</button>

        </td>

    </tr>";

}



echo $output;

?>

