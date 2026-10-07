# Prompt para generar la documentación de CriptoSim

Este archivo es un **prompt listo para copiar y pegar** en otra IA (Claude, ChatGPT, Gemini, etc.).

Su propósito: que esa IA te genere el **Documento técnico** y el **Manual de usuario** que pide la rúbrica.

---

## Cómo usarlo

1. Abrí el proyecto en tu editor o terminal para que la IA pueda leer los archivos.
2. Copiá desde la línea `===== INICIO DEL PROMPT =====` hasta `===== FIN DEL PROMPT =====`.
3. Pegalo como primer mensaje de una conversación nueva.
4. Pedile los **dos documentos por separado**, en ese orden. El manual se escribe mejor cuando la IA ya entendió el sistema a través del documento técnico.

---

## Por qué este prompt y no uno genérico

Un prompt de documentación mal escrito produce documentos llenos de cosas que después tenés que corregir a mano. Este te da:

- El **estado real** del sistema, verificado con pruebas, no lo que la IA imagina.
- La **estructura exacta** de archivos, para que no invente carpetas.
- La **base de datos real**, con sus tablas, sus columnas y sus claves foráneas.
- **Instrucciones explícitas de no inventar**: si algo no está, que lo diga en vez de fabricate.

---

## Reglas de oro para la IA que documente

Si leés esto antes de pegar el prompt, entendés por qué funciona:

- **Documentá lo que existe, no lo que debería existir.** Una IA tiende a describir el software ideal. Eso te cuesta puntos en la revisión.
- **Cada afirmación técnica debe poder rastrearse a un archivo.** Si dice "el sistema valida el saldo antes de comprar", tiene que poder señalar `includes/trading.php`.
- **Las capturas las ponés vos.** El prompt pide marcadores `[CAPTURA: descripción]` en los puntos exactos donde van.
- **Los criterios de la rúbrica no se inventan.** El prompt incluye la rúbrica real para que la IA mapee funcionalidades a criterios.

---

===== INICIO DEL PROMPT =====

# Tarea: Documentar el proyecto web "CriptoSim"

Vas a documentar un simulador web educativo de compra y venta de criptomonedas, ya terminado y funcional. Es un proyecto final de universidad de Desarrollo Web.

Tu trabajo es generar **DOS documentos**:

1. **Documento técnico**: decisiones de diseño, arquitectura, estructura, base de datos y seguridad.
2. **Manual de usuario**: cómo usar el sistema, paso a paso, para alguien que nunca lo vio.

**Regla más importante: no inventes nada.** Todo lo que escribas debe ser verificable en el código que te describo abajo. Si algo no existe o no lo podés confirmar, escríbelo explícitamente como pendiente, en lugar de inventar una explicación. Un documento honesto vale más que uno completo pero falso, porque va a ser revisado contra el código real.

---

## 1. Qué es el proyecto

**CriptoSim** es un simulador web de compra y venta de criptomonedas con fines educativos. No maneja dinero real, no usa blockchain, no se conecta a exchanges y no ejecuta transacciones reales. Todos los saldos y los precios son ficticios.

**Tecnologías:** PHP sin frameworks, MySQL, HTML, CSS y JavaScript vanilla. Las gráficas NO usan ninguna librería externa ni CDN: son barras y elementos dibujados con HTML, CSS y JavaScript vanilla. El sistema funciona completo sin conexión a internet.

**Nombre del sistema:** CriptoSim. **Moneda:** quetzales (Q).

---

## 2. Requisitos del proyecto

El sistema se construyó contra un documento de requisitos oficial. Estos son los requisitos que debe cumplir:

- Registro e inicio de sesión de usuarios
- Asignación de un saldo ficticio inicial a cada usuario nuevo
- Visualización del portafolio con precios simulados
- Compra y venta de al menos 3 criptomonedas
- Historial completo de transacciones
- Visualización del saldo actualizado
- Reportes: historial de movimientos por usuario, balance general y gráficas de actividad
- Interfaz intuitiva, responsiva y moderna
- Manejo de sesiones y seguridad básica
- Validaciones del lado cliente y del lado servidor
- Base de datos estructurada
- Administración de usuarios: suspender, reactivar y dar de baja
- Un área administrativa separada

**Rúbrica de evaluación (15 puntos):** funcionalidad completa 4, diseño de interfaz 2, base de datos estructurada 2, seguridad y validaciones 2, reportería funcional 2, documentación técnica 1, presentación y demostración 2.

---

## 3. Estructura real del proyecto

```
PROYECTO_FINAL/
├── index.php                  Página de portada
├── database.sql               Script de la base de datos (importable)
├── datos_demo.sql             Datos de ejemplo para la demostración
├── datos_usuarios_demo.sql    Usuarios demo adicionales (Luis Angel Yuman y Keylie Sanchez)
├── README.md                  Documentación general del proyecto
├── INSTRUCCIONES.MD           Documento de requisitos original
├── .htaccess                  Control de acceso a rutas sensibles
├── Encender CriptoSim.bat     Arranque del servidor local
├── Apagar CriptoSim.bat       Detención del servidor local
│
├── config/
│   └── conexion.php           Conexión a MySQL
│
├── includes/
│   ├── auth.php               Sesiones, roles y protección de páginas
│   ├── funciones.php          Utilidades: escape, formato, CSRF, configuración
│   ├── precios.php            Obtención de precios (simulados)
│   ├── trading.php            Lógica de compra, venta y consultas
│   ├── graficas.php           Datos y render de las gráficas
│   ├── consejos.php           Lista de consejos aleatorios
│   ├── mascota.php            Simi, la mascota del sistema
│   ├── header.php             Encabezado y barra lateral común
│   └── footer.php             Pie común
│
├── auth/
│   ├── registro.php           Formulario de registro
│   ├── login.php              Inicio de sesión
│   └── logout.php             Cierre de sesión
│
├── usuario/                   Área privada del usuario
│   ├── dashboard.php          Panel principal con tarjetas y gráficas
│   ├── mercado.php            Mercado de criptomonedas y modales de operación
│   ├── portafolio.php         Mis posiciones y venta
│   ├── historial.php          Historial con filtros
│   ├── factura.php            Factura simulada de una operación
│   ├── consejo.php            Consejo aleatorio del día
│   └── perfil.php             Datos de mi cuenta
│
├── admin/                     Área privada del administrador
│   ├── dashboard.php          Métricas generales
│   ├── usuarios.php           Listado, suspensión, reactivación, baja y saldo virtual
│   ├── usuario_detalle.php    Detalle de un usuario y sus movimientos
│   ├── reportes.php           Los 5 reportes y las gráficas
│   ├── transacciones.php      Transacciones globales con filtros y enlace a facturas
│   └── config.php             Configuración del sistema
│
├── css/
│   └── estilos.css            Hoja de estilos única
│
├── js/
│   ├── app.js                 JavaScript del cliente: modales y validaciones
│   ├── consejo.js             Mascara y animaciones del consejo
│   └── lateral.js             Comportamiento de la barra lateral
│
├── img/
│   └── simi/                  Imágenes de la mascota
│
└── documentacion/
    └── capturas/              Capturas de pantalla para el documento
```

---

## 4. Base de datos real

**Motor:** MySQL. **Base de datos:** `criptosim`.

Las cinco tablas son: `usuarios`, `criptomonedas`, `portafolio`, `transacciones` y `config_sistema`.

### Tabla `usuarios`

| Columna | Tipo | Notas |
|---|---|---|
| id_usuario | int unsigned | PK, autoincremental |
| nombre | varchar | Nombre completo |
| correo | varchar | Único |
| password | varchar | Hash con `password_hash()` |
| saldo | decimal(15,2) | No puede ser negativo |
| rol | enum | `usuario` o `admin` |
| estado | enum | `activo`, `suspendido` o `baja` |
| fecha_registro | datetime | Automático |

### Tabla `criptomonedas`

| Columna | Tipo | Notas |
|---|---|---|
| id_criptomoneda | int unsigned | PK, autoincremental |
| nombre | varchar(50) | Bitcoin, Ethereum, Dogecoin |
| simbolo | varchar(10) | Único. BTC, ETH, DOGE |
| precio | decimal(18,8) | **Precio simulado, fijo** |
| variacion | decimal(6,2) | Variación **simulada**, solo decorativa |
| estado | enum | `activo` o `inactivo` |

**Precios simulados actuales (cambian con el simulador, salen de la tabla `criptomonedas`):** Bitcoin ~Q568,770.88, Ethereum ~Q25,358.79, Dogecoin ~Q1.36. El precio se lee directo de la tabla en cada página; no hay API, caché externa ni conversión.

### Tabla `portafolio`

| Columna | Tipo | Notas |
|---|---|---|
| id_portafolio | int | PK |
| id_usuario | int unsigned | FK a `usuarios.id_usuario` |
| id_criptomoneda | int unsigned | FK a `criptomonedas.id_criptomoneda` |
| cantidad | decimal(20,8) | Cantidad poseída |

Un usuario tiene como máximo una fila por moneda, mediante una restricción de clave única sobre `(id_usuario, id_criptomoneda)`.

### Tabla `transacciones`

| Columna | Tipo | Notas |
|---|---|---|
| id_transaccion | int | PK |
| id_usuario | int unsigned | FK a `usuarios.id_usuario` |
| id_criptomoneda | int unsigned | FK a `criptomonedas.id_criptomoneda` |
| tipo | enum | `compra` o `venta` |
| cantidad | decimal(20,8) | |
| precio | decimal(18,8) | Precio unitario al momento de operar |
| total | decimal(18,2) | cantidad por precio |
| fecha | datetime | Automático |

### Tabla `config_sistema`

| Columna | Tipo | Notas |
|---|---|---|
| clave | varchar | PK |
| valor | varchar | |

Valores actuales: `nombre_sistema` = CriptoSim, `saldo_inicial` = 10000.00, `moneda` = Q, `ultima_actualizacion_precios` = fecha/hora de la última simulación de precios.

### Relaciones e integridad

- Las cuatro claves foráneas de `portafolio` y `transacciones` apuntan a `usuarios` y a `criptomonedas`, todas con `ON DELETE CASCADE`.
- `usuarios.saldo` no puede quedar negativo.
- **La baja de un usuario es un cambio de estado a `baja`, no un borrado físico.** La razón: el CASCADE borraría también su portafolio y su historial de transacciones, y el proyecto necesita conservar ese historial para los reportes administrativos.

---

## 5. Seguridad implementada

Verificá cada punto en el código antes de afirmarlo en el documento.

| Medida | Dónde |
|---|---|
| Contraseñas con `password_hash()` | Registro |
| Verificación con `password_verify()` | Inicio de sesión |
| Consultas preparadas en todas las consultas SQL | Todo el proyecto |
| Escape de toda salida con un helper `e()` | Todo el proyecto |
| Token CSRF en formularios sensibles | Compra, venta y acciones administrativas |
| Protección de páginas privadas | `requiere_logueo()` en las siete páginas de `usuario/` |
| Control de roles | `requiere_admin()` en las seis páginas de `admin/` |
| Regeneración del identificador de sesión al iniciar sesión | Inicio de sesión |
| Cookie de sesión con `httponly` y `samesite` | `funciones.php` |
| `.htaccess` que bloquea `.sql`, `.md`, `.log` y `.ini` | Raíz del proyecto |

Dos decisiones que conviene explicar en el documento técnico:

- El campo `password` almacena el hash, nunca la contraseña en texto plano.
- No hay rate limiting en el inicio de sesión. Es una omisión deliberada: el alcance del proyecto es educativo y la consigna pide no sobrecomplicar la seguridad. Se puede plantear como mejora futura.

---

## 6. Decisiones de diseño relevantes

1. **Sin frameworks.** PHP, MySQL y JavaScript vanilla. Es una decisión exigida por la consigna, para que el proyecto sea explicable en una presentación.
2. **Precios 100% simulados.** El sistema **no hace ninguna llamada a internet**: no hay API de precios, no hay caché externa y no hay conversión de moneda. El precio sale directo de la tabla `criptomonedas`. Esto cumple la prohibición del documento de requisitos de utilizar precios reales.
3. **La baja es lógica, no física.** Ver la sección 4.
4. **Gráficas propias, sin librerías externas.** Las gráficas se dibujan con HTML, CSS y JavaScript vanilla. No hay Chart.js ni ningún CDN: el sistema funciona completo sin conexión a internet, importante para presentar en un aula sin WiFi.
5. **Factura simulada por operación.** Cada compra o venta genera una factura `F-` + número correlativo de 6 dígitos. Es una simulación educativa con NIT "CF (Consumidor Final - simulada)" y una leyenda que aclara que no tiene validez fiscal. Solo el dueño de la operación o el administrador pueden verla.
6. **Simi, la mascota.** Un personaje con cinco expresiones: base, bienvenida, alegre, triste y pensativo. Se elige según el contexto: alegre en estados vacíos, triste ante errores o saldo insuficiente. No es decoración, comunica el resultado de una operación.
7. **Paleta de colores definida como variables CSS** en `:root`, para poder cambiar el tema sin tocar cada regla.
8. **Tablas responsive**, con desplazamiento horizontal en pantallas pequeñas.

---

## 7. Cómo ejecutar el proyecto

- **Servidor:** PHP 8 o superior, con `php -S localhost:8080` desde la raíz del proyecto, o cualquier servidor Apache o XAMPP.
- **Base de datos:** MySQL 5.7 o superior (el proyecto se probó con MySQL 8.0).
- **Paso 1:** importar `database.sql` desde MySQL Workbench o por consola.
- **Paso 2:** ajustar las credenciales en `config/conexion.php`.
- **Paso 3:** abrir `http://localhost:8080`.

**Cuentas de prueba:**

| Rol | Correo | Contraseña | Saldo actual |
|---|---|---|---|
| Administrador | `admin@criptosim.com` | `Admin123*` | Q5,800.00 |
| Usuario demo | `usuario@criptosim.com` | `Usuario123*` | Q58,124.09 |
| Usuario demo | `lyuman@criptosim.com` | `Usuario123*` | Q7,325.53 |
| Usuario demo | `ksanchez@criptosim.com` | `Usuario123*` | Q6,551.52 |

Para ver reportes y gráficas con datos en una demostración, importá además `datos_demo.sql`
(datos de operaciones) y `datos_usuarios_demo.sql` (los dos usuarios demo adicionales),
después de `database.sql`.

---

## 8. Qué debés producir

### Documento A: Documento técnico

Estructura sugerida. Podés ajustarla si lo necesitás, pero cubrí todo:

1. **Portada**: nombre del proyecto, autor, universidad, curso y fecha.
2. **Introducción**: qué es y por qué existe.
3. **Objetivos**: general y específicos.
4. **Planteamiento del problema**: contexto y justificación.
5. **Marco teórico**: qué es una criptomoneda, qué es un simulador educativo y qué son las monedas virtuales.
6. **Metodología**: cómo se construyó, en las etapas de análisis, diseño, desarrollo y pruebas.
7. **Requisitos**: funcionales y no funcionales, en tabla.
8. **Arquitectura del sistema**: diagrama o descripción de las capas.
9. **Modelo de datos**: las tablas y las relaciones entre ellas.
10. **Estructura de carpetas**: el árbol de archivos con una línea de descripción de cada carpeta.
11. **Flujo de los principales procesos**, descritos paso a paso:
    - Registro e inicio de sesión
    - Compra de criptomoneda
    - Venta de criptomoneda
    - Consulta del portafolio
    - Generación de la factura simulada de una operación
    - Administración de usuarios: suspender, reactivar y dar de baja
    - Agregar saldo virtual a un usuario
    - Consulta de transacciones globales del administrador
12. **Decisiones técnicas**: por qué se eligió cada cosa y qué alternativa se descartó.
13. **Seguridad**: las medidas de la sección 5, con el código relevante.
14. **Pruebas realizadas**: tabla con qué se probó, cómo y qué resultado dio.
15. **Conclusiones**.
16. **Trabajo futuro**: qué se mejoraría.
17. **Referencias**.

### Documento B: Manual de usuario

Escrito para alguien que **nunca vio el sistema**. Español claro, sin jerga.

1. **Portada**
2. **¿Qué es CriptoSim?**: una explicación corta, en lenguaje sencillo.
3. **Requisitos para usar el sistema**
4. **Cómo entrar por primera vez**
5. **Guía del usuario normal**
   - Mi panel
   - El mercado y cómo comprar
   - Cómo vender
   - Mi portafolio
   - Mi historial y sus filtros
   - Mi factura y cómo imprimirla
   - El consejo del día
   - Mi perfil
6. **Guía del administrador**
   - El panel de administración
   - Gestión de usuarios y cómo agregar saldo virtual
   - Las transacciones globales y sus filtros
   - Los reportes
   - La configuración
7. **Preguntas frecuentes**: al menos ocho. ¿El dinero es real?, ¿Los precios son reales?, ¿Qué pasa si me equivoco al vender?, ¿Puedo tener dos cuentas?, ¿Cómo se suspende una cuenta?, ¿Se pueden borrar los datos?, ¿Qué pasa si no hay internet?, ¿Cómo reporto un problema?
8. **Glosario**: al menos diez términos.

---

## 9. Reglas de escritura

- **Idioma:** español neutro y profesional. Nada de voseo, nada de jerga informal, nada de emoji.
- **Honestidad absoluta:** si verificaste algo, decilo. Si no lo pudiste verificar, marcalo como pendiente. No inventes funcionalidades.
- **Tono:** el de un trabajo universitario serio, no el de un manual de software comercial.
- **Longitud:** el documento técnico, entre 12 y 20 páginas. El manual, entre 8 y 12.

### Marcas donde van las capturas

El autor va a insertar las capturas de pantalla él mismo. En lugar de inventar imágenes, poné un marcador en el punto exacto donde va cada una:

```
[CAPTURA: Página de portada de CriptoSim]
[CAPTURA: Formulario de registro con los campos nombre, correo, contraseña y confirmar]
[CAPTURA: Pantalla de inicio de sesión con el mensaje de error por credenciales incorrectas]
[CAPTURA: Panel del usuario con las tarjetas de saldo, valor del portafolio y número de operaciones]
[CAPTURA: Mercado con las tarjetas de Bitcoin, Ethereum y Dogecoin]
[CAPTURA: Modal de compra abierto con el precio, la cantidad y el total calculado]
[CAPTURA: Portafolio con las posiciones del usuario y el botón Vender]
[CAPTURA: Historial con las etiquetas COMPRA y VENTA y sus filtros]
[CAPTURA: Factura simulada de una operación con su botón de imprimir]
[CAPTURA: Consejo del día con la mascota Simi]
[CAPTURA: Panel de administración con las métricas de usuarios y transacciones]
[CAPTURA: Gestión de usuarios con el panel de Agregar saldo virtual]
[CAPTURA: Detalle de un usuario con sus movimientos]
[CAPTURA: Transacciones globales con sus filtros]
[CAPTURA: Reportes con las gráficas de actividad]
[CAPTURA: Configuración del sistema]
```

Las capturas ya están tomadas en `documentacion/capturas/` (archivos 00-inicio.png,
01-login.png, 02-registro.png, 10-dashboard-usuario.png, 11-mercado.png,
12-portafolio.png, 13-historial.png, 14-factura.png, 15-perfil.png, 16-consejo.png,
20-admin-dashboard.png, 21-admin-usuarios.png, 22-admin-usuario-detalle.png,
23-admin-reportes.png, 24-admin-transacciones.png y 25-admin-config.png). Incluí al menos
esos marcadores. Agregá los que necesites.

---

## 10. Cómo trabajar

1. **Primero leé el código.** Abrí los archivos del proyecto antes de escribir nada. El documento tiene que coincidir con lo que hay.
2. **Después producí el Documento técnico.**
3. **Esperá mi confirmación.**
4. **Luego producí el Manual de usuario.**

Si algo no lo podés verificar leyendo el código, preguntame antes de escribirlo.

===== FIN DEL PROMPT =====

---

## Dato verificado

La tabla de criptomonedas se llama **`criptomonedas`** y su clave primaria es **`id_criptomoneda`**. El prompt lo dice de forma explícita para que la IA no lo invente ni lo cambie. Si más adelante se renombra la tabla en el código, actualizá esta línea y la sección 4 del prompt.