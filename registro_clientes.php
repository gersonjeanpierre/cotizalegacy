<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Cliente</title>
    <link rel="stylesheet" href="styless.css">
</head>
<body>
<div class="form-container">
        <h2>Formulario de Registro de Cliente</h2>
        <form action="guardar_cliente.php" method="POST">
            <label>Nombre del Cliente:</label>
            <input type="text" name="nombre" required>
   
            <label>Razón Social:</label>
            <input type="text" name="razon_social" required>

            <label>RUC:</label>
            <input type="text" name="ruc" required>

            <label>Celular:</label>
            <input type="text" name="celular" required>

            <label>Correo:</label>
            <input type="email" name="correo" required>

            <input type="submit" value="Registrar Cliente">
            
    </form>
</body>
</html>
