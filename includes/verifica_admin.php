<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["id_usuario"])) {
    header("Location: ../login.php");
    exit;
}

if ($_SESSION["tipo"] !== "admin") {
    header("Location: ../index.php");
    exit;
}