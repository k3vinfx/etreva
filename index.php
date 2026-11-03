<?php

  //Se incluye la configuración de conexión a datos en el
  //SGBD: MariaDB.
session_start();
require_once 'lang/Lang.php';
Lang::load($_SESSION['lang'] ?? 'es');

  require_once 'model/database.php';

  //Para registrar productos es necesario iniciar los proveedores
  //de los mismos, por ello la variable controller para este
  //ejercicio se inicia con el 'proveedor'.
  $controller = 'inicio';
  //$controller = 'cliente';
  // Todo esta lógica hara el papel de un FrontController
  if(!isset($_REQUEST['c']))
  {
    //Llamado de la página principal
   // require_once "controller/$controller.controller.php";
   // $controller = ucwords($controller) . 'Controller';
   // $controller = new $controller;

    require_once "controller/$controller.controller.php";
     $controller = ucwords($controller) . 'Controller';
     $controller = new $controller;
     $controller->Index();

  }
  else
  {
    // Obtiene el controlador a cargar
    $controller = strtolower($_REQUEST['c']);
    $accion = isset($_REQUEST['a']) ? $_REQUEST['a'] : 'Index';

    // Instancia el controlador
    require_once "controller/$controller.controller.php";
    $controller = ucwords($controller) . 'Controller';
    $controller = new $controller;

    // Llama la accion
    call_user_func( array( $controller, $accion ) );
  }
