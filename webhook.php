<?php
// Solo aceptar solicitudes POST (las que envía GitHub)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    shell_exec('/bin/bash /home/descubrecartagena.com/public_html/botes/deploy.sh');
    http_response_code(200);
    echo "Despliegue ejecutado.";
} else {
    http_response_code(403);
    echo "Acceso denegado.";
}
?>
