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

            <label>RUC(11) o DNI(8):</label>
            <input type="text" name="ruc" required>

            <label>Celular:</label>
            <input type="text" name="celular" required>

            <label>Correo:</label>
            <input type="email" name="correo" required>

            <input type="submit" value="Registrar Cliente">
            
    </form>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rucInput = document.querySelector('input[name="ruc"]');
    const razonSocialInput = document.querySelector('input[name="razon_social"]');
    let timeout = null;

    rucInput.addEventListener('input', function() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            if (rucInput.value.length === 8) {
                razonSocialInput.value = 'Persona Natural';
            } else if (rucInput.value.length === 11) {
                razonSocialInput.value = '';
            }
        }, 2400); // 3 segundos
    });
});
</script>
