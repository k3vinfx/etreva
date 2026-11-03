<?php
date_default_timezone_set('America/La_Paz');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class usuarios
{

  private $pdo;

    public function __CONSTRUCT()
  {
    try
    {
      $this->pdo = Database::Conectar();
    }
    catch(Exception $e)
    {
      die($e->getMessage());
    }
  }
 
public function ActivarCuenta($token)
{
    try {

        $sql = "SELECT * FROM usuarios.usuarios
                WHERE configuracion_json->'token_verificacion_correo'->>'token' = ?
                AND estado = 'pendiente_correo'";

        $stm = $this->pdo->prepare($sql);
        $stm->execute([$token]);
        $usuario = $stm->fetch(PDO::FETCH_OBJ);

        if ($usuario) {

            $config = json_decode($usuario->configuracion_json, true);

            $config["token_verificacion_correo"]["estado"] = "verificado";
            $config["token_verificacion_correo"]["fecha_verificacion"] = date("Y-m-d H:i:s");

            $nuevo_json = json_encode($config);

            $update = "UPDATE usuarios.usuarios
                       SET estado = 'activo',
                           configuracion_json = ?
                       WHERE id = ?";

            $this->pdo->prepare($update)->execute([$nuevo_json, $usuario->id]);

            return true;
        }

        return false;

    } catch (Exception $e) {
        error_log("Error ActivarCuenta: " . $e->getMessage());
        return false;
    }
}


public function actualizarConfiguracion($id, $json)
{
    $sql = "UPDATE usuarios.usuarios
            SET configuracion_json = :json,
                estado = 'restauracion_cuenta'
            WHERE id = :id";

    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':json', $json);
    $stmt->bindParam(':id', $id);

    return $stmt->execute();
}

public function buscarPorTokenRestauracion($token) {
    // Buscamos en el JSON el token que coincida y que esté pendiente
    $sql = "SELECT id, email, configuracion_json 
            FROM usuarios.usuarios 
            WHERE configuracion_json->'restauracion_cuenta'->>'token' = :token
            LIMIT 1";
    
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([':token' => $token]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function finalizarRestauracion($id, $password_hash, $nuevoJson) {
    // Actualizamos la clave, el json (limpio) y el estado a activo
    $sql = "UPDATE usuarios.usuarios 
            SET password_hash = :pass, 
                configuracion_json = :json,
                estado = 'activo' 
            WHERE id = :id";
            
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        ':pass' => $password_hash,
        ':json' => $nuevoJson,
        ':id'   => $id
    ]);
}

public function enviarCorreoRestauracion($email, $token)
{
    try {

        $mail = new PHPMailer(true);

        $mail->isSMTP();

        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom('k3vinfx@gmail.com', 'Etreva Travel');
        $mail->addAddress($email);

        $link = "http://localhost/etreva/index.php?c=usuarios&a=restablecer&token=" . $token;

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Restablecer contraseña - Etreva Travel';

        $mail->Body = "
            <h3>Restablecer contraseña</h3>
            <p>Haz clic en el siguiente enlace:</p>
            <a href='{$link}'>{$link}</a>
            <br><br>
            <small>Si no solicitaste esto, ignora este mensaje.</small>
        ";

        $mail->send();

        return true;

    } catch (Exception $e) {

        error_log("Error enviando restauración: " . $e->getMessage());
        return false;
    }
}
public function buscarPorCorreo($email)
{
    $sql = "SELECT id, configuracion_json
            FROM usuarios.usuarios
            WHERE email = :email
            LIMIT 1";

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        ':email' => trim($email)
    ]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

       

   





}
