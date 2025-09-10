<?php
// Configuración para el envío de correos con PHPMailer

// **REEMPLAZA ESTOS VALORES CON LOS DE TU PROVEEDOR DE CORREO**

define('SMTP_HOST', 'smtp.hostinger.com');       // Ej: smtp.hostinger.com, smtp.gmail.com
define('SMTP_USERNAME', 'admin@capjunin.net.pe'); // Tu dirección de correo completa
define('SMTP_PASSWORD', '1324MMa1240$'); // La contraseña de esa cuenta de correo
define('SMTP_PORT', 465);                         // Puerto SMTP. 465 es para SSL (lo más común), 587 es para TLS
define('SMTP_SECURE', 'ssl');                     // Puede ser 'ssl' o 'tls'

?>