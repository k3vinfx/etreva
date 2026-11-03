<?php
// swagger/index.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Documentación de API</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            url: 'openapi.json', // El archivo está en la misma carpeta
            dom_id: '#swagger-ui',
        });
    </script>
</body>
</html>