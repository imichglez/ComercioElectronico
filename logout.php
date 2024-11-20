<?php
session_start();
session_unset();
session_destroy();
header('Location: index.html'); // Redirigir a la página principal
exit;
?>
