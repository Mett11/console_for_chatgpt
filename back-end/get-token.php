<?php
session_start();
header('Content-Type: application/json');

// Controlla se la variabile di sessione 'jwt' è impostata
if (isset($_SESSION['jwt'])) {
    $jwt = $_SESSION['jwt']; // Recupera il JWT dalla sessione
    echo json_encode(['jwt' => $jwt]);
} else {
    // Risposta chiara in caso di assenza del JWT
    echo json_encode(['error' => 'JWT non trovato nella sessione']);
}
?>
