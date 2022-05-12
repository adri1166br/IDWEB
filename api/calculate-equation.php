<?php

header('Content-Type: application/json');
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__ . '/db/index.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $a = $_POST['a'];
  $b = $_POST['b'];
  $c = $_POST['c'];

  $result = checkCoefficients($a, $b, $c, $conn);
  echo json_encode($result);
} else {
  echo "Not a POST method.";
}

function calculateEquation($a, $b, $c) {
  $D = $b * $b - 4 * $a * $c;
  if ($D >= 0) {
    $x1 = (-$b + sqrt($D)) / (2 * $a);
    $x2 = (-$b - sqrt($D)) / (2 * $a);
    return "$x1,$x2";
  } else {
    $x1 = -$b / (2 * $a);
    $x2 = sqrt(-$D) / (2 * $a);
    return "$x1 ± $x2 i";
  }
}

function checkCoefficients($a, $b, $c, $conn) {
  $sql = "SELECT * FROM id_web.coefficients WHERE a = $a AND b = $b AND c = $c";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    // If there are coefficients stored in DB then we get the result stored in the databse.
    while ($row = mysqli_fetch_assoc($result)) {
      $coefficient_id = $row["id"];
      $coefficientResult = checkCoefficientResult($coefficient_id, $conn);
      if (empty($coefficientResult)) {
        return "No roots.";
      } else {
        return $coefficientResult;
      }
    }
  } else {
    return persistCoefficients($a, $b, $c, $conn);
  }
}

function checkCoefficientResult($coefficient_id, $conn) {
  $sql = "SELECT * FROM id_web.results WHERE coefficients_id = $coefficient_id";
  $result = mysqli_query($conn, $sql);

  if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
      return $row['result'];
    }
  } else {
    return null;
  }
}

function persistCoefficients($a, $b, $c, $conn) {
  $sql = "INSERT INTO id_web.coefficients (a, b, c) VALUES ($a, $b, $c)";

  if (mysqli_query($conn, $sql)) {
    $coefficient_id = mysqli_insert_id($conn);
    $result = calculateEquation($a, $b, $c);
    persistResult($result, $coefficient_id, $conn);
    return $result;
  } else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }
}

function persistResult($result, $coefficient_id, $conn) {
  $sql = "INSERT INTO id_web.results (result, coefficients_id) VALUES ('$result', $coefficient_id)";

  if (mysqli_query($conn, $sql)) {
    return $result;
  } else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }
}
