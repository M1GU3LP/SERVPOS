<?php
// Roles del sistema (RF1.2)
define('ROL_PROPIETARIO', 'propietario');
define('ROL_GERENTE', 'gerente');
define('ROL_CAJERO', 'cajero');

// Minutos de inactividad antes de cerrar sesion automaticamente (RNF4.5)
define('MINUTOS_EXPIRACION_SESION', 5);

// Roles que pueden administrar usuarios e inventario (RF1.x, RF3.9)
define('ROLES_ADMINISTRACION', [ROL_PROPIETARIO, ROL_GERENTE]);

// Todos los roles validos del sistema, usados para tomar pedidos (RF4.x)
define('ROLES_TODOS', [ROL_PROPIETARIO, ROL_GERENTE, ROL_CAJERO]);

// Datos de la empresa para el layout (RNF3.4, RNF3.6, RNF3.7)
define('NOMBRE_EMPRESA', 'San Juan Comidas Rápidas');
define('URL_FACEBOOK', 'https://facebook.com/sanjuancomidasrapidas');
define('URL_INSTAGRAM', 'https://instagram.com/sanjuancomidasrapidas');
define('URL_TWITTER', 'https://twitter.com/sanjuancomidas');
