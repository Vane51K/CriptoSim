# CriptoSim - Simulador de Compra y Venta de Criptomonedas

<p align="center">
  <img src="https://media4.giphy.com/media/v1.Y2lkPTc5MGI3NjExOGNueTVldDk2cHZzZ3FoNmgybDBlaW5pNm1qYXFyMmc4bmwxdDcxZCZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/LPFNd1AJBoYcVUExmE/giphy.gif" width="300"/>
</p>


Proyecto Final de Desarrollo Web
Universidad Mariano Galvez de Guatemala

## 1. Nombre del proyecto

**CriptoSim** - Simulador de Compra y Venta de Criptomonedas.

## 2. Descripcion

CriptoSim es una plataforma web universitaria que simula operaciones de compra y venta de
criptomonedas con fines educativos. Permite que los usuarios se registren, reciban un saldo
ficticio inicial en quetzales (Q) y operen con tres criptomonedas (Bitcoin, Ethereum y
Dogecoin) a precios simulados, sin dinero real de por medio.

El sistema cuenta con un area privada para el usuario (dashboard, mercado, portafolio,
historial, factura simulada, consejo y perfil) y un area administrativa separada (dashboard,
gestion de usuarios, detalle por usuario, reportes, transacciones y configuracion) para
suspender cuentas, dar de baja usuarios, agregar saldo virtual y visualizar reportes.

## 3. Objetivo

Disenar y desarrollar una plataforma web que simule la compra y venta de criptomonedas con
una interfaz grafica atractiva y funcional, incluyendo una base de datos estructurada
(MySQL), manejo de sesiones con seguridad basica, validaciones en cliente y servidor, y un
sistema de reporteria que indique las monedas mas vendidas, las mas compradas y los usuarios
que generan mas transacciones.

## 4. Tecnologias

| Capa      | Tecnologia                              |
|-----------|-----------------------------------------|
| Frontend  | HTML5, CSS3, JavaScript vanilla (sin frameworks) |
| Backend   | PHP 8 (sin frameworks)                  |
| Base de datos | MySQL 8.0                            |
| Graficas  | Barras y elementos propios en HTML/CSS  |
| Servidor  | Servidor integrado de PHP (php -S) o XAMPP |
| Mascota   | Simi, imagen decorativa propia          |

Las graficas no dependen de librerias externas ni CDN: se dibujan con HTML, CSS y JavaScript
vanilla, por lo que el sistema funciona completo sin conexion a internet (ideal para la
presentacion en el aula).

## 5. Requisitos

- PHP 8.0 o superior con la extension `mysqli` habilitada.
- MySQL 8.0 (o MariaDB 10.4+).
- Navegador web moderno (Chrome, Edge, Firefox).
- Para importar la base de datos: MySQL Workbench, la consola `mysql` o phpMyAdmin.
- Alternativa todo-en-uno: XAMPP (Apache + PHP + MySQL).

## 6. Instalacion

1. Copiar la carpeta `PROYECTO_FINAL` al directorio web del servidor (por ejemplo
   `C:\xampp\htdocs\PROYECTO_FINAL`), o conservarla donde se vaya a ejecutar el servidor
   integrado de PHP.
2. Crear e importar la base de datos (ver puntos 7 y 8).
3. Ajustar las credenciales de MySQL en `config/conexion.php` (ver punto 7).
4. Iniciar el servidor (ver punto 9).
5. Abrir `http://127.0.0.1:8080` en el navegador.

## 7. Configuracion de MySQL

El archivo `config/conexion.php` contiene las credenciales de conexion:

```php
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', 'TU_CLAVE_DE_MYSQL');
define('DB_NAME', 'criptosim');
define('DB_PORT', 3306);
```

Cambiar `DB_USER` y `DB_PASS` por las credenciales locales. Pueden usarse variables de
entorno si se prefiere, pero la consigna pide seguridad basica para un proyecto universitario
y estas credenciales son locales de desarrollo.

## 8. Importacion del database.sql

Desde MySQL Workbench o la consola:

```sql
SOURCE ruta/completa/database.sql;
```

O directamente con el cliente de consola:

```
mysql -u root -p < database.sql
```

El script crea la base `criptosim` (con `DROP DATABASE IF EXISTS`), crea las 5 tablas
(`usuarios`, `criptomonedas`, `portafolio`, `transacciones` y `config_sistema`) y deja la
estructura lista.

Despues, para poblar el sistema con datos de demostracion, importar en este orden:

```
datos_demo.sql            Historial de operaciones ficticio + usuario demo
datos_usuarios_demo.sql   Dos usuarios demo adicionales (Luis Angel Yuman y Keylie Sanchez)
```

## 9. Configuracion de PHP

Se puede ejecutar con el servidor integrado de PHP:

```
php -S 127.0.0.1:8080 -t "ruta/PROYECTO_FINAL"
```

El proyecto incluye dos archivos de ayuda (`Encender CriptoSim.bat` y `Apagar
CriptoSim.bat`) que inician y detienen el servidor en `http://127.0.0.1:8080`
automáticamente. Tambien funciona con Apache de XAMPP colocando la carpeta en `htdocs`.

Requisitos PHP: extension `mysqli` activa, `session` activa y `display_errors` en on para
desarrollo.

## 10. Usuarios de prueba

| Rol    | Correo                  | Contrasena   | Saldo inicial (Q) |
|--------|-------------------------|--------------|-------------------|
| Admin  | admin@criptosim.com     | Admin123*    | 5,800.00          |
| Usuario demo | usuario@criptosim.com | Usuario123*  | 58,124.09 (con historial) |
| Usuario demo | lyuman@criptosim.com  | Usuario123*  | 7,325.53          |
| Usuario demo | ksanchez@criptosim.com | Usuario123* | 6,551.52          |

El administrador ve el area administrativa; los demas ven el area de usuario. Los saldos ya
incluyen operaciones ficticias cargadas por los scripts de datos demo para que los reportes y
las graficas tengan informacion que mostrar en la presentacion.

## 11. Funcionalidades

**Area publica:**
- Registro de usuarios con saldo ficticio inicial.
- Inicio y cierre de sesion con proteccion de paginas.

**Area de usuario:**
- Dashboard con tarjetas (saldo disponible, valor del portafolio, monedas en cartera y
  ultima operacion) y graficas propias.
- Mercado con las 3 criptomonedas, precios simulados y modales de compra/venta.
- Portafolio con posiciones, rendimiento y venta de monedas.
- Historial completo de compras y ventas con filtros.
- Factura simulada de cada operacion (correlativa, con boton de imprimir).
- Consejo aleatorio del dia (funcionalidad creativa).
- Perfil con los datos de la cuenta.

**Area administrativa:**
- Dashboard con metricas generales.
- Gestion de usuarios: listado, suspension, reactivacion y baja.
- Detalle de un usuario (sus operaciones y reportes individuales).
- Reportes: moneda mas comprada, mas vendida, usuarios con mas transacciones, historial por
  usuario y balance general, mas graficas.
- Transacciones globales con filtros y enlace a cada factura.
- Agregar saldo virtual a un usuario.
- Configuracion del sistema.

## 12. Estructura del proyecto

```
PROYECTO_FINAL/
├── index.php                  Pagina de portada
├── database.sql               Script de la base de datos (importable)
├── datos_demo.sql             Datos de demostracion (operaciones)
├── datos_usuarios_demo.sql    Usuarios demo adicionales
├── README.md                  Documentacion general del proyecto
├── INSTRUCCIONES.MD           Documento de requisitos original
├── .htaccess                  Control de acceso a rutas sensibles
├── Encender CriptoSim.bat     Arranque del servidor local
├── Apagar CriptoSim.bat       Detencion del servidor local
│
├── config/
│   └── conexion.php           Conexion a MySQL (host, usuario, clave, base)
│
├── includes/
│   ├── auth.php               Sesiones, roles y proteccion de paginas
│   ├── funciones.php          Utilidades: escape, formato, CSRF, configuracion
│   ├── precios.php            Obtencion de precios (simulados)
│   ├── trading.php            Logica de compra, venta y consultas
│   ├── graficas.php           Datos y render de las graficas
│   ├── consejos.php           Lista de consejos aleatorios
│   ├── mascota.php            Simi, la mascota del sistema
│   ├── header.php             Encabezado y barra lateral comun
│   └── footer.php             Pie comun
│
├── auth/
│   ├── registro.php           Formulario de registro
│   ├── login.php              Inicio de sesion
│   └── logout.php             Cierre de sesion
│
├── usuario/                   Area privada del usuario
│   ├── dashboard.php          Panel principal con tarjetas y graficas
│   ├── mercado.php            Mercado de criptomonedas y modales de operacion
│   ├── portafolio.php         Mis posiciones y venta
│   ├── historial.php          Historial con filtros
│   ├── factura.php            Factura simulada de una operacion
│   ├── consejo.php            Consejo aleatorio del dia
│   └── perfil.php             Datos de mi cuenta
│
├── admin/                     Area privada del administrador
│   ├── dashboard.php          Metricas generales
│   ├── usuarios.php           Listado, suspension, reactivacion, baja y saldo virtual
│   ├── usuario_detalle.php    Detalle de un usuario y sus movimientos
│   ├── reportes.php           Los reportes y las graficas
│   ├── transacciones.php      Transacciones globales con filtros y facturas
│   └── config.php             Configuracion del sistema
│
├── css/
│   └── estilos.css            Hoja de estilos unica
│
├── js/
│   ├── app.js                 JavaScript del cliente: modales y validaciones
│   ├── consejo.js             Mascara y animaciones del consejo
│   └── lateral.js             Comportamiento de la barra lateral
│
├── img/
│   └── simi/                  Imagenes de la mascota
│
└── documentacion/
    └── capturas/              Capturas de pantalla para la documentacion
```

## 13. Base de datos

Base: `criptosim`, motor MySQL, juego de caracteres `utf8mb4`.

| Tabla           | Descripcion                                                    |
|-----------------|----------------------------------------------------------------|
| usuarios        | Datos personales, saldo, rol y estado de la cuenta            |
| criptomonedas   | Nombre, simbolo y precio simulado de cada moneda              |
| portafolio      | Cantidad de cada moneda que posee cada usuario                |
| transacciones   | Compras y ventas: tipo, cantidad, precio, total, fecha, usuario |
| config_sistema  | Parametros configurables del sistema                          |

Relaciones principales:
- `usuarios 1-N portafolio N-1 criptomonedas` (un usuario posee varias monedas).
- `usuarios 1-N transacciones N-1 criptomonedas` (cada operacion referencia usuario y moneda).
- Restriccion de clave unica en `portafolio (id_usuario, id_criptomoneda)` para que cada
  usuario tenga una sola fila por moneda.

## 14. Seguridad

- Contrasenas con hash seguro (`password_hash` de PHP, no en texto plano).
- Consultas con sentencias preparadas de `mysqli` contra inyeccion SQL.
- Proteccion CSRF con token unico por sesion en los formularios.
- Cookies de sesion con `httponly` y `SameSite=Lax`; regeneracion del id de sesion al iniciar.
- Validaciones en el cliente (JavaScript) y en el servidor (PHP).
- Paginas protegidas segun rol: `requiere_logueo()` y `requiere_admin()`.
- Verificacion del estado de la cuenta (activo/suspendido/baja) en cada pagina protegida.
- Escape de la salida con `htmlspecialchars` contra XSS.
- `.htaccess` para bloquear rutas sensibles del servidor.
- Consulta del administrador protege al propio admin y a los usuarios.

## 15. Pruebas

- `php -l` (lint) sin errores en todas las paginas.
- Registro de un usuario nuevo: recibe saldo ficticio inicial.
- Inicio de sesion correcto e incorrecto (password erroneo rechazado).
- Compra y venta de las 3 criptomonedas: el saldo y el portafolio se actualizan.
- Validaciones: saldo insuficiente, cantidad invalida y formularios sin campos rechazados.
- Control de acceso: un usuario comun no puede entrar a paginas `admin/*` (redirige), y un
  visitante sin sesion no entra a `usuario/*` ni `admin/*`.
- Factura simulada: cada usuario solo ve sus facturas; la factura ajena es bloqueada.
- Reportes y transacciones: los indicadores, tablas y graficas muestran datos coherentes con
  el historial cargado.
- Agregar saldo virtual: se aplica y refleja en el saldo del usuario seleccionado.

### Capturas de pantalla

Las capturas de todas las paginas (publicas, de usuario y de administrador) estan en
`documentacion/capturas/` y pueden insertarse directamente en el documento tecnico y en el
manual de usuario.
