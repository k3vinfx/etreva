<?php
class Database
{
    //Esta función permite la conexión al SGBD: MariaDB.
    //host = tipo de conexión local - 'localhost'.
    //dbname = nombre de la base de datos.
    //charset = utf8, indica la codificación de caracteres utilizada.
    //root = nombre de usuario (solo para fines académicos=root).
    //'' = contraseña del root (solo para fines académicos). u838621046_neurona_2

  public static function Conectar()
      {
          try {
              // El formato para Postgres es pgsql:host=...;dbname=...
              // Nota: En Postgres el usuario por defecto suele ser 'postgres'
              $host = 'localhost';
              $db   = 'etreva_travel';
              $user = 'postgres'; 
              $pass = ''; // Sin contraseña según tu indicación

              $pdo = new PDO("pgsql:host=$host;port=5432;dbname=$db", $user, $pass);
              
              // Configuración de errores
              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              
              return $pdo;
          } catch (PDOException $e) {
              die("Error en la conexión a Postgres: " . $e->getMessage());
          }
      }
  }
