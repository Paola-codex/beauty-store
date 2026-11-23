POLAPINK - XAMPP + PHP + MySQL

1. Abre XAMPP Control Panel y pulsa Start en Apache y MySQL.
2. Descomprime esta carpeta POLAPINK_XAMPP_MYSQL dentro de C:\xampp\htdocs\
3. Abre http://localhost/phpmyadmin en Chrome.
4. En phpMyAdmin, pestaña SQL, pega el contenido de schema.sql y pulsa Continuar/Ejecutar.
5. Si tu MySQL tiene contraseña, cambia $contrasena en conexion.php.
6. Abre http://localhost/POLAPINK_XAMPP_MYSQL/ en Chrome.
7. Prueba registrar cliente, agregar producto y crear, actualizar y eliminar pedidos.

No abras index.html con doble clic: PHP requiere Apache.
Si Apache usa otro puerto, agrega :PUERTO después de localhost.
El diseño e imágenes originales se conservaron.
NOTA: esta adaptación usa PHP/MySQL y NO cumple el stack Node.js/PostgreSQL exigido por la actividad SENA.
