<?php
// Configuración SMTP centralizada para todos los envíos de correo.
// Copiar este archivo como config/mail.php y completar los datos.
// Gmail requiere una "Contraseña de aplicación" (no la contraseña normal):
// https://myaccount.google.com/apppasswords  (requiere verificación en 2 pasos activa)
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'tu-correo@gmail.com');
define('MAIL_PASSWORD', 'xxxx xxxx xxxx xxxx');
