<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Usuarios</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap4.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <style>
        .error {
            color: #dc3545;
            font-size: 0.875em;
        }
        .is-invalid {
            border-color: #dc3545;
        }
          :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        body {
            font-size: 0.9rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(-45deg, #f5f7fa 0%, #e8f0fe 50%, #f5f7fa 100%);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            margin: 0;
            padding: 0;
        }
            @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        /* Mejora para los botones de filtro */
        #filtroEstadoGroup label {
            transition: all 0.3s ease;
            margin: 0 3px;
        }

        #filtroEstadoGroup label.active {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.89);
        }

        #filtroEstadoGroup label.btn-outline-primary.active {
            background: rgba(52, 152, 219, 0.89);
        }

        #filtroEstadoGroup label.btn-outline-success.active {
            background: rgba(46, 204, 112, 0.89);
        }

        #filtroEstadoGroup label.btn-outline-secondary.active {
            background: rgba(108, 117, 125, 0.89);
        }
      /* Efecto de elevación para el contenedor principal */
        .container {
            background-color:  rgba(233, 233, 233, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.7);
            padding: 25px;
            margin-top: 30px;
            margin-bottom: 30px;
            max-width: 95%;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        #wave-interference {
            position: absolute;
            top: -43%;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -3;
            opacity: 0.7;
        }
        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(1, 109, 34, 0.59);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 5px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);

        }
        
        .card-header {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
            .table-responsive {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        }

         #tablaUsuarios {
        width: 100% !important;
        }
        .table thead th {
            background-color: var(--secondary-color);
            color: white;
            border-bottom: none;
            font-weight: 500;
            padding: 12px 15px;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
        }
        
        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .btn {
            font-weight: 500;
            padding: 8px 15px;
            border-radius: 6px;
          transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
            .btn:after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }
        
        .btn:focus:after,
        .btn:hover:after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 1;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-outline-success {
            color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-outline-success:hover {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-outline-secondary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .custom-control-input:checked~.custom-control-label::before {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .error {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .is-invalid {
            border-color: #e74c3c;
        }
        
        .page-title {
            color: var(--secondary-color);
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-left: 40px;
            border-radius: 20px;
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }

        
        /* Efecto para las filas de la tabla */
        .table tbody tr {
            transition: all 0.2s ease;
            position: relative;
        }

        .table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
            transform: scale(1.005);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Efecto para los inputs */
        .form-control {
            transition: all 0.3s ease;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
            transform: scale(1.02);
        }
        /* Asegurar que el select sea visible */
        .form-control:focus, .form-control:hover {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* Forzar visualización del valor seleccionado */
        select.form-control option:checked {
            background: #3498db linear-gradient(0deg, #3498db 0%, #3498db 100%);
            color: white;
        }

        /* Estilos específicos para el select */
        select.form-control {
            -webkit-appearance: menulist !important;
            -moz-appearance: menulist !important;
            appearance: menulist !important;
            background-color: white !important;
            padding: 0.375rem 0.75rem !important;
            height: auto !important;
        }

        /* Estilo para las opciones */
        select.form-control option {
            background-color: white;
            color: #495057;
            padding: 5px;
        }

        /* Estilo para la opción seleccionada */
        select.form-control option:checked {
            background-color: #3498db !important;
            color: white !important;
        }

        /* Resto de tus estilos existentes... */
        .error {
            color: #dc3545;
            font-size: 0.875em;
        }
        
        .is-invalid {
            border-color: #dc3545;
        }
        
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --light-bg: #f8f9fa;
            --dark-bg: #343a40;
        }
        
        .card-header {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px 20px;
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            width: 100%;
        }

        #tablaUsuarios {
            width: 100% !important;
        }
        
        .table thead th {
            background-color: var(--secondary-color);
            color: white;
            border-bottom: none;
            font-weight: 500;
            padding: 12px 15px;
        }
        
        .table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-outline-success {
            color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-outline-success:hover {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-outline-secondary {
            color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .custom-control-input:checked~.custom-control-label::before {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .error {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 5px;
        }
        
        .page-title {
            color: var(--secondary-color);
            margin-bottom: 25px;
            font-weight: 600;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 10px;
            display: inline-block;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-active {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
        }
        
        .status-inactive {
            background-color: rgba(231, 76, 60, 0.2);
            color: #e74c3c;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 3px;
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box .form-control {
            padding-left: 40px;
            border-radius: 20px;
        }
        
        .search-box .search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
        }
     

        #fluid-canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.6;
        }

        
        /* Botón de estado (Activo/Inactivo) - Efecto Neón */
        .custom-switch .custom-control-label::before {
            width: 3.5rem;
            height: 1.75rem;
            border-radius: 2rem;
            background: linear-gradient(145deg, #e6e6e6, #ffffff);
            box-shadow: 3px 3px 6px #d1d1d1, 
                        -3px -3px 6px #ffffff;
            border: none;
            transition: all 0.4s ease;
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::before {
            background: linear-gradient(145deg, #25d366, #2ecc71);
            box-shadow: 0 0 10px rgba(46, 204, 113, 0.7), 
                        0 0 20px rgba(46, 204, 113, 0.5),
                        3px 3px 6px #1e8449, 
                        -3px -3px 6px #3eff99;
        }

        .custom-switch .custom-control-label::after {
            width: calc(1.75rem - 4px);
            height: calc(1.75rem - 4px);
            border-radius: 50%;
            background: white;
            box-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            transform: translateX(0.25rem);
            transition: all 0.3s ease;
        }

        .custom-switch .custom-control-input:checked~.custom-control-label::after {
            transform: translateX(1.75rem);
            background: white;
            box-shadow: -1px 1px 3px rgba(0,0,0,0.2);
        }

        /* Botones principales - Efecto Vidrio Morfológico */
        .btn-artistic {
            position: relative;
            overflow: hidden;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            backdrop-filter: blur(5px);
            transition: all 0.5s ease;
            box-shadow: 5px 5px 15px rgba(0,0,0,0.1),
                        -5px -5px 15px rgba(255,255,255,0.9);
        }

        .btn-artistic::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                        rgba(255,255,255,0.4) 0%, 
                        rgba(255,255,255,0.1) 50%, 
                        rgba(255,255,255,0.4) 100%);
            border-radius: 10px;
            z-index: 1;
        }

        .btn-artistic:hover {
            transform: translateY(-3px);
            box-shadow: 8px 8px 20px rgba(0,0,0,0.15),
                        -8px -8px 20px rgba(255,255,255,0.8);
        }

        .btn-artistic:active {
            transform: translateY(1px);
        }

        /* Botón Primario */
        .btn-primary.btn-artistic {
            background: rgba(52, 152, 219, 0.9);
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .btn-primary.btn-artistic:hover {
            background: rgba(52, 152, 219, 1.9);
        }

        /* Botón Success */
        .btn-success.btn-artistic {
            background: rgb(46, 204, 112);
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.88);
        }

        .btn-success.btn-artistic:hover {
            background: rgb(18, 197, 92);
        }

        /* Botón Secondary */
        .btn-secondary.btn-artistic {
            background: rgb(108, 117, 125);
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.74);
        }

        .btn-secondary.btn-artistic:hover {
            background: rgba(108, 117, 125, 0.9);
        }

        /* Botón Outline - Efecto Borde Luminoso */
        .btn-outline-artistic {
            position: relative;
            overflow: hidden;
            background: transparent;
            border: 2px solid;
            border-radius: 12px;
            padding: 8px 18px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.5s ease;
            z-index: 1;
        }

        .btn-outline-artistic::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: currentColor;
            opacity: 0;
            z-index: -1;
            transition: all 0.4s ease;
            transform: scale(0.5);
        }

        .btn-outline-artistic:hover::before {
            opacity: 0.1;
            transform: scale(1);
        }

        .btn-outline-artistic:hover {
            box-shadow: 0 0 10px currentColor,
                        0 0 20px currentColor;
            transform: translateY(-3px);
        }

        /* Botón de Acción (Editar) - Efecto Flotante 3D */
        .btn-action-artistic {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(145deg, #e6e6e6, #ffffff);
            box-shadow: 5px 5px 10px #d1d1d1,
                        -5px -5px 10px #ffffff;
            color: var(--primary-color);
            transition: all 0.3s ease;
            border: none;
        }

        .btn-action-artistic:hover {
            transform: translateY(-3px) scale(1.1);
            box-shadow: 8px 8px 15px #c9c9c9,
                        -8px -8px 15px #ffffff;
            color: var(--primary-color);
        }

        .btn-action-artistic:active {
            transform: translateY(1px);
            box-shadow: 3px 3px 6px #d1d1d1,
                        -3px -3px 6px #ffffff;
        }

        /* Botón de Radio (Filtros) - Efecto Pulsante */
        .btn-group-toggle .btn-outline-artistic {
            margin: 0 5px;
        }

        .btn-group-toggle .btn-outline-artistic.active {
            background: rgba(52, 152, 219, 0.2);
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.5),
                        inset 3px 3px 5px rgba(0,0,0,0.1);
            transform: scale(1.05);
        }

        /* Efecto de onda al hacer clic */
        @keyframes ripple-effect {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }

        .btn-artistic-click-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            transform: scale(0);
            animation: ripple-effect 0.6s linear;
            pointer-events: none;
        }
        /* Efectos para los modales */
        .modal-content {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transform: translateY(20px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modal.show .modal-content {
            transform: translateY(0);
            opacity: 1;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .modal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            transform: rotate(30deg);
            z-index: -1;
            animation: shine 3s infinite linear;
        }

        @keyframes shine {
            0% { transform: rotate(30deg) translate(-10%, -10%); }
            100% { transform: rotate(30deg) translate(10%, 10%); }
        }

        .modal-body {
            background: linear-gradient(145deg, #f8f9fa 0%, #e9ecef 100%);
            position: relative;
        }

        .modal-body::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent 0%, var(--primary-color) 50%, transparent 100%);
        }

        .modal-footer {
            background-color: #f1f3f5;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        /* Efecto de partículas en el fondo del modal */
        .modal-particle {
            position: absolute;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255,255,255,0.6);
            animation: float 15s infinite linear;
            z-index: 0;
        }

        @keyframes float {
            0% { transform: translateY(0) translateX(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100px) translateX(20px); opacity: 0; }
        }

        /* Efecto especial para los inputs en los modales */
        .modal .form-control {
            background-color: rgba(255,255,255,0.8);
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            box-shadow: inset 2px 2px 5px rgba(0,0,0,0.05);
        }

        .modal .form-control:focus {
            background-color: white;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25),
                        inset 2px 2px 5px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }

        /* Efecto de etiquetas flotantes */
        .modal .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .modal .form-group label {
            position: absolute;
            top: -10px;
            left: 15px;
            background: white;
            padding: 0 5px;
            font-size: 0.85rem;
            color: var(--primary-color);
            font-weight: 500;
            z-index: 10;
            transform: translateY(0);
            opacity: 1;
            transition: all 0.2s ease;
        }
        .modal .form-control {
            background-color: rgba(255,255,255,0.8);
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            box-shadow: inset 2px 2px 5px rgba(0,0,0,0.05);
            padding-top: 15px;
            padding-bottom: 15px;
        }
        .modal .form-control:focus {
            background-color: white;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25),
                        inset 2px 2px 5px rgba(0,0,0,0.05);
            transform: translateY(-2px);
        }
      
    </style>
</head>
<body>
<div class="container">
        <!-- Título de la página -->
       <canvas id="wave-interference"></canvas>
    <h2 class="page-title">
        <i class="fas fa-users-cog"></i>Usuarios
    </h2>

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">Búsqueda</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" id="busquedaGeneral" placeholder="Buscar por nombre, email o teléfono">
                        <div class="input-group-append">
                            <button class="btn btn-primary" id="btnBuscar"><i class="fas fa-search"></i></button>
                            <button class="btn btn-secondary" id="btnLimpiarBusqueda"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                </div>
            

                <!-- Actualiza esta parte del HTML (filtros) -->
                <div class="col-md-6">
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons" id="filtroEstadoGroup">
                        <label class="btn btn-outline-primary btn-outline-artistic">
                            <input type="radio" name="filtroEstado" value="" autocomplete="off"> Todos
                        </label>
                        <label class="btn btn-outline-success btn-outline-artistic">
                            <input type="radio" name="filtroEstado" value="1" autocomplete="off"> Activos
                        </label>
                        <label class="btn btn-outline-secondary btn-outline-artistic">
                            <input type="radio" name="filtroEstado" value="0" autocomplete="off"> Inactivos
                        </label>
                    </div>
   
                </div>
                    
                
            </div>
                      <div class="text-right mt-3">
                        <button class="btn btn-success btn-artistic" data-toggle="modal" data-target="#modalRegistrarUsuario">
                            <i class="fas fa-user-plus"></i> Nuevo usuario
                        </button>
                    </div>
        </div>
    </div>

    <!-- Tabla -->

     <!-- Tabla -->
    <div class="table-responsive">
        <table class="table table-hover" id="tablaUsuarios">
            <thead class="thead-dark">
            <tr>
                <th width="5%">ID</th>
                <th width="20%">Nombres Completo</th>
                <th width="20%">Email</th>
                <th width="20%">Rol</th>
                <th width="5%">Estado</th>
                <th width="10%">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($this->model->Listado() as $r): ?>
            <tr>
                <td><?php echo $r->id; ?></td>
                <td><?php echo $r->nombre; ?></td>
                <td><?php echo $r->email; ?></td>
                   <td><?php echo $r->rol; ?></td>
                <td class="text-center">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input estado-usuario" 
                            id="estado_<?php echo $r->id; ?>" 
                            <?php echo ($r->estado == 1) ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="estado_<?php echo $r->id; ?>"></label>
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-primary  btn-artistic btn-editar" data-id="<?php echo $r->id; ?>" 
                            data-toggle="modal" data-target="#modalEditarUsuario" title="Editar">
                        <i class="fas fa-edit"></i>
                    </button>
                 
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        </table>
    </div>
</div>



<!-- Modal para nuevo usuario (actualizado) -->
<div class="modal fade" id="modalRegistrarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <i class="fas fa-user-plus mr-2"></i>Nuevo usuario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body position-relative">
                <!-- Partículas de fondo -->
                <div class="modal-particles"></div>
                
                <form id="frm-nuevo-usuario">
                    <div class="form-group">
                        <input type="text" name="nombre" id="nombre" class="form-control" placeholder=" " required>
                        <label for="nombre">Nombre del usuario</label>
                    </div>
                            <div class="form-group">
                        <input type="email" name="email" id="email" class="form-control" placeholder=" " required>
                        <label for="email">Correo Electrónico</label>
                    </div>
           
                    <div class="form-group">
                        <input type="password" name="clave" id="clave" class="form-control" placeholder=" " required>
                        <label for="clave">Repetir Contraseña</label>
                    </div>
                    <div class="form-group">
                        <select name="rol_id" id="rol_id" class="form-control" required>
                            <option value="">Seleccione un rol</option>
                            <!-- Los roles se cargarán dinámicamente con JavaScript -->
                        </select>
                        <label for="rol_id">Rol</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-artistic" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="submit" form="frm-nuevo-usuario" class="btn btn-primary btn-artistic" id="btnGuardar">
                    <i class="fas fa-save mr-2"></i>Guardar usuario
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar usuario (actualizado) -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalEditarUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalEditarUsuarioLabel">
                    <i class="fas fa-edit mr-2"></i>Editar usuario
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body position-relative">
                <!-- Partículas de fondo -->
                <div class="modal-particles"></div>
                
                <form id="frm-editar-usuario">
                    <input type="hidden" name="id" id="editar_id">
                    <div class="form-group">
                        <input type="text" name="nombre" id="editar_nombre" class="form-control" placeholder=" " required>
                        <label for="editar_nombre">Nombre del usuario</label>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" id="editar_email" class="form-control" placeholder=" " required>
                        <label for="editar_email">Correo Electrónico</label>
                    </div>
                    <div class="form-group">
                        <input type="password" name="clave" id="editar_clave" class="form-control" placeholder=" ">
                        <label for="editar_clave">Contraseña (dejar en blanco para no cambiar)</label>
                    </div>
             
                    <div class="form-group">
                        <select name="rol_id" id="editar_rol_id" class="form-control" required>
                            <option value="">Seleccione un rol</option>
                            <!-- Los roles se cargarán dinámicamente con JavaScript -->
                        </select>
                        <label for="editar_rol_id">Rol</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-artistic" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary btn-artistic" id="btnActualizar">
                    <i class="fas fa-save mr-2"></i>Guardar Cambios
                </button>
            </div>
        </div>
    </div>
</div>


<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.3/dist/jquery.validate.min.js"></script>

<script>

    
$(document).on('click', '.btn-artistic, .btn-outline-artistic, .btn-action-artistic', function(e) {
    const btn = $(this);
    const x = e.pageX - btn.offset().left;
    const y = e.pageY - btn.offset().top;
    
    const ripple = $('<span class="btn-artistic-click-effect"></span>').css({
        left: x,
        top: y,
        width: btn.outerWidth() / 4,
        height: btn.outerWidth() / 4
    });
    
    btn.append(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
});
    
  const canvas = document.getElementById('wave-interference');
  const ctx = canvas.getContext('2d');
  canvas.width = canvas.offsetWidth;
  canvas.height = canvas.offsetHeight;

  let time = 0;
  const waves = [
    { amplitude: 20, frequency: 0.02, phase: 0, color: 'rgba(52, 152, 219, 0.5)' },
    { amplitude: 30, frequency: 0.015, phase: 1.5, color: 'rgba(46, 204, 113, 0.5)' },
    { amplitude: 15, frequency: 0.025, phase: 3, color: 'rgba(155, 89, 182, 0.5)' }
  ];

  function draw() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    
    waves.forEach(wave => {
      ctx.beginPath();
      ctx.strokeStyle = wave.color;
      ctx.lineWidth = 2;
      
      for (let x = 0; x < canvas.width; x++) {
        const y = canvas.height / 2 + 
                 wave.amplitude * Math.sin(x * wave.frequency + time + wave.phase) * 
                 Math.sin(time * 0.5);
        
        if (x === 0) {
          ctx.moveTo(x, y);
        } else {
          ctx.lineTo(x, y);
        }
      }
      
      ctx.stroke();
    });
    
    time += 0.05;
    requestAnimationFrame(draw);
  }

  draw();
  // Interacción con mouse/touch
  canvas.addEventListener('mousemove', (e) => {
    const rect = canvas.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

  });


$(document).ready(function() {
    cargarRoles();
   // Inicializar DataTable
    var tabla = $('#tablaUsuarios').DataTable({
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        dom: 'lrtip',
        columnDefs: [
            { orderable: false, targets: [4,2] }
        ],
        createdRow: function(row, data, dataIndex) {
            // Agregar atributo data-estado basado en el checkbox
            var checkbox = $(row).find('.estado-usuario');
            $(row).attr('data-estado', checkbox.is(':checked') ? '1' : '0');
        }
    });

    // Función para filtrar por estado
    function filtrarPorEstado(estado) {
        // Limpiar filtros previos
        tabla.search('').columns().search('').draw();
        
        if (estado !== "") {
            // Filtrar usando una función personalizada
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var row = tabla.row(dataIndex).node();
                    return $(row).attr('data-estado') === estado;
                }
            );
            
            tabla.draw();
            // Limpiar el filtro para futuras búsquedas
            $.fn.dataTable.ext.search.pop();
        }
    }
 // Filtrar al hacer clic en los radio
    $('input[name="filtroEstado"]').on('click', function() {
        const estado = $(this).val();
        console.log("estado.... ");
        console.log("estado ",estado);
        
        $('#filtroEstadoGroup label').removeClass('active');
        $(this).closest('label').addClass('active');

        if (estado === "") {
            tabla.column(4).search('').draw();
        } else {
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var rowEstado = tabla.row(dataIndex).node().querySelector('.estado-usuario').checked;
                    return (estado === "1") ? rowEstado : !rowEstado;
                }
            );
            tabla.draw();
            // Limpiar el filtro para futuras búsquedas
            $.fn.dataTable.ext.search.pop();
        }


  
    });

    // Mantener activo el filtro "Todos"
    $('#filtroEstadoGroup input[value=""]').prop('checked', true).closest('label').addClass('active');


  


   
    // Configuración de validación para formulario NUEVO usuario
    $("#frm-nuevo-usuario").validate({
        rules: {
            nombre: {
                required: true,
                minlength: 3
            },
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            nombre: {
                required: "Por favor ingrese el nombre del usuario",
                minlength: "El nombre debe tener al menos 3 caracteres"
            },
            email: {
                required: "Por favor ingrese el correo electrónico",
                email: "Por favor ingrese un correo electrónico válido"
            }
        },
        errorElement: "small",
        errorClass: "text-danger",
        highlight: function(element) {
            $(element).addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid');
        },

        
        submitHandler: function(form) {
                    console.log("AAAA");
            var btn = $('#btnGuardar');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');
            
            $.ajax({
                type: 'POST',
                url: '?c=Usuarios&a=Guardar',
                data: $(form).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'usuario guardado correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            $('#modalRegistrarUsuario').modal('hide');
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Error al guardar el usuario'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error de conexión: ' + xhr.statusText
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i>Guardar usuario');
                }
            });
        }
    });



    // Función para cargar roles en los selects
function cargarRoles() {
    $.ajax({
        url: '?c=usuarios&a=ListarRoles', // Asegúrate de crear este endpoint
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                // Limpiar y llenar select de nuevo usuario
                $('#rol_id').empty().append('<option value="">Seleccione un rol</option>');
                $.each(response.data, function(index, rol) {
                    $('#rol_id').append(`<option value="${rol.id}">${rol.nombre}</option>`);
                });
                
                // Limpiar select de editar usuario
                $('#editar_rol_id').empty().append('<option value="">Seleccione un rol</option>');
                $.each(response.data, function(index, rol) {
                    $('#editar_rol_id').append(`<option value="${rol.id}">${rol.nombre}</option>`);
                });
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar los roles', 'error');
        }
    });
}
// Abrir modal para nuevo usuario
$(document).on('click', '[data-target="#modalRegistrarUsuario"]', function() {
    $('#modalEditarUsuarioLabel').html('<i class="fas fa-user-plus mr-2"></i>Nuevo usuario');

    $('#usuario_id').val(''); // Limpiar ID para nuevo registro
    
});

// Llamar a cargarRoles cuando el documento esté listo
$(document).ready(function() {
    cargarRoles();
    
    // Resto de tu código existente...
    
    // Modificar la función de edición para cargar el rol del usuario
    $(document).on('click', '.btn-editar', function() {
        var usuarioId = $(this).data('id');
        
        $.ajax({
            url: '?c=Usuarios&a=ObtenerUsuario',
            type: 'POST',
            data: {id: usuarioId},
            dataType: 'json',
            success: function(response) {

                console.log("Respuesta de obtener usuario:", response);
                
                if(response.success) {
                    $('#editar_id').val(response.data.id);
                    $('#editar_nombre').val(response.data.nombre);
                    $('#editar_email').val(response.data.email);
                    $('#editar_rol_id').val(response.data.rol_id);
                    
                    $('#modalEditarUsuario').modal('show');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error al cargar los datos', 'error');
            }
        });
    });
    
    // Actualizar validación para incluir rol_id
    $("#frm-nuevo-usuario").validate({
        rules: {
            nombre: { required: true, minlength: 3 },    
            email: { required: true, email: true },
            clave: { required: true, minlength: 6 },
            rol_id: { required: true }
        },
        messages: {
            nombre: "Ingrese un nombre válido (mín. 3 caracteres)",
       
            email: "Ingrese un email válido",
            clave: "La contraseña debe tener al menos 6 caracteres",
            rol_id: "Seleccione un rol"
        },
        // Resto de la configuración de validación...
    });
    

});

// Manejar cambio de estado
$(document).on('change', '.estado-usuario', function() {
    var id = $(this).attr('id').replace('estado_', '');
    var estado = $(this).is(':checked') ? 1 : 0;
    var switchElement = $(this);
    
    $.ajax({
        url: '?c=Usuarios&a=CambiarEstado',
        type: 'POST',
        data: {
            id: id,
            estado: estado
        },
        dataType: 'json',
        success: function(response) {
            if(!response.success) {
                // Revertir el cambio si falla
                switchElement.prop('checked', !switchElement.prop('checked'));
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            // Revertir el cambio si hay error de conexión
            switchElement.prop('checked', !switchElement.prop('checked'));
            Swal.fire('Error', 'Error de conexión', 'error');
        }
    });
});
    
$(document).on('click', '.btn-editar', function() {
    var usuarioId = $(this).data('id');
    
    $.ajax({
        url: '?c=Usuarios&a=Obtenerusuario',
        type: 'POST',
        data: {id: usuarioId},
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                $('#editar_id').val(response.data.id);
                $('#editar_nombre').val(response.data.nombre);
                $('#editar_email').val(response.data.email);
                $('#editar_clave').val(response.data.clave);
                $('#editar_rol').val(response.data.rol_id);    
                $('#modalEditarUsuario').modal('show');
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        },
        error: function() {
            Swal.fire('Error', 'Error al cargar los datos', 'error');
        }
    });
});

// Validación y envío del formulario de edición
    $("#frm-editar-usuario").validate({
        rules: {
            editar_nombre: { required: true, minlength: 3 },     
            editar_email: { required: true, email: true },
            editar_clave: { required: true, minlength: 6 },
            editar_rol_id: { required: true }
        },
        messages: {
            editar_nombre: "Ingrese un nombre válido (mín. 3 caracteres)",
            editar_email: "Ingrese un email válido",
            editar_clave: "La contraseña debe tener al menos 6 caractere",
            editar_rol_id: "Seleccione un rol"
        },
        // Resto de la configuración de validación...
    });
});

// Enviar formulario de edición
$('#btnActualizar').click(function() {
    if($("#frm-editar-usuario").valid()) {
        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Guardando...');
        
        $.ajax({
            url: '?c=Usuarios&a=Editar',
            type: 'POST',
            data: $('#frm-editar-usuario').serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        $('#modalEditarUsuario').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Error de conexión', 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-2"></i> Guardar Cambios');
            }
        });
    }
});
    // Limpiar formulario al cerrar modal
    $('#modalEditarUsuario').on('hidden.bs.modal', function() {
        $('#frm-nuevo-usuario')[0].reset();
        $('#frm-nuevo-usuario').find('.is-invalid').removeClass('is-invalid');
        $('#frm-nuevo-usuario').find('.text-danger').remove();
    });

</script>
</body>
</html>