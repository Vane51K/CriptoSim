# PROMPT MAESTRO PARA OPENCODE

## Proyecto Final Desarrollo Web

### Simulador de Compra y Venta de Criptomonedas

Actúa como desarrollador web full-stack y asistente de programación para ayudarme a desarrollar mi proyecto final de Desarrollo Web de la Universidad Mariano Gálvez de Guatemala.

El proyecto debe ser funcional, visualmente atractivo, creativo y fácil de explicar durante una exposición universitaria.

Tengo un documento oficial llamado:

**Proyecto Final Desarrollo Web.pdf**

Debes utilizar ese documento como referencia principal para determinar los requisitos.

---

# 1. OBJETIVO DEL PROYECTO

El proyecto consiste en desarrollar una plataforma web educativa que simule la compra y venta de criptomonedas.

NO se trata de una plataforma financiera real.

NO se utilizarán criptomonedas reales.

NO se realizarán transacciones reales.

Todo el dinero y las operaciones serán ficticios y tendrán únicamente fines educativos.

El sistema debe permitir que los usuarios se registren, reciban un saldo ficticio y puedan comprar y vender diferentes criptomonedas.

También debe existir un área administrativa y un sistema de reportes.

---

# 2. TECNOLOGÍAS OBLIGATORIAS

Quiero utilizar una arquitectura sencilla.

## Frontend

Utilizar:

* HTML
* CSS
* JavaScript

Se puede utilizar Bootstrap si realmente facilita el desarrollo, pero NO es obligatorio.

Evitar React, Vue, Angular u otros frameworks frontend si no son necesarios.

## Backend

Utilizar:

* PHP

Preferir PHP sencillo y estructurado.

No utilizar Laravel ni otros frameworks complejos.

## Base de datos

La base de datos debe ser:

**MySQL**

La administración y diseño de la base de datos se realizará utilizando:

**MySQL Workbench**

El proyecto debe incluir un archivo SQL que pueda importarse desde MySQL Workbench.

---

# 3. NO QUIERO UN PROYECTO COMPLICADO

Esta regla es MUY importante.

Quiero que el proyecto tenga bastantes funcionalidades y se vea creativo, pero que técnicamente sea sencillo de desarrollar y explicar.

NO quiero:

* React.
* Vue.
* Angular.
* Laravel.
* Node.js si no es necesario.
* Docker.
* Microservicios.
* APIs complejas.
* Blockchain.
* JWT si no es necesario.
* WebSockets.
* Arquitecturas excesivamente complejas.
* Sistemas financieros reales.
* Integraciones con exchanges.
* APIs reales de criptomonedas.

La prioridad es:

**FUNCIONALIDAD + DISEÑO + CÓDIGO COMPRENSIBLE**

Quiero que parezca un proyecto completo, no un proyecto técnicamente innecesariamente complicado.

---

# 4. IDENTIDAD VISUAL

Toda la aplicación debe utilizar esta paleta:

```text
#2C2C2C
#853953
#612D5
#F3F4F4
```

IMPORTANTE:

El color `#612D5` parece estar incompleto porque los colores HEX normalmente tienen 6 caracteres.

NO inventes el color.

Por ahora utiliza correctamente:

* #2C2C2C
* #853953
* #F3F4F4

Y deja el cuarto color como variable pendiente hasta que sea confirmado.

Por ejemplo:

```css
:root {
    --color-dark: #2C2C2C;
    --color-primary: #853953;
    --color-secondary: #612D5;
    --color-light: #F3F4F4;
}
```

Si `#612D5` provoca un error, NO reemplazarlo automáticamente por otro color.

Preguntarme antes de decidir cuál debe ser.

---

# 5. ESTILO VISUAL

Quiero una interfaz:

* Moderna.
* Elegante.
* Limpia.
* Profesional.
* Oscura con contrastes claros.
* Responsiva.
* Fácil de utilizar.
* Con personalidad propia.

NO quiero que parezca una plantilla genérica.

La aplicación debe transmitir la idea de una plataforma de trading, pero claramente como simulador educativo.

Utilizar:

* Tarjetas.
* Paneles.
* Iconos.
* Gráficas sencillas.
* Indicadores.
* Botones claros.
* Estados visuales.
* Tablas.
* Badges.
* Modales cuando faciliten la experiencia.

No abusar de animaciones.

Las animaciones deben ser sencillas y no afectar el funcionamiento.

---

# 6. NOMBRE DEL SISTEMA

Antes de comenzar a desarrollar, propone entre 3 y 5 nombres creativos para el simulador.

Los nombres deben:

* Ser fáciles de pronunciar.
* Tener relación con criptomonedas o simulación.
* Verse bien como logo.
* Ser apropiados para un proyecto universitario.

NO cambiar el nombre definitivo hasta que yo seleccione uno.

Mientras tanto, utilizar un nombre temporal en el código.

---

# 7. FUNCIONALIDADES PRINCIPALES

El sistema debe cumplir como mínimo:

## Registro

El usuario debe poder crear una cuenta.

Campos sugeridos:

* Nombre completo.
* Correo electrónico.
* Contraseña.
* Confirmación de contraseña.

Validar:

* Campos obligatorios.
* Correo válido.
* Correo no duplicado.
* Contraseñas iguales.
* Contraseña con requisitos básicos.

---

# 8. INICIO DE SESIÓN

Crear:

* Login.
* Logout.
* Manejo de sesiones.

El usuario debe poder acceder únicamente a las áreas correspondientes a su cuenta.

---

# 9. SALDO FICTICIO

Al registrarse:

El usuario debe recibir automáticamente un saldo inicial ficticio.

El monto puede ser:

**Q10,000.00**

Este monto debe poder modificarse fácilmente desde la configuración del sistema.

Mostrar claramente que se trata de:

**Saldo virtual / saldo de simulación**

---

# 10. CRIPTOMONEDAS

Utilizar inicialmente 3 criptomonedas:

### Bitcoin

BTC

### Ethereum

ETH

### Dogecoin

DOGE

Estas cantidades pueden ampliarse posteriormente si es sencillo.

Cada criptomoneda debe tener:

* Nombre.
* Símbolo.
* Precio simulado.
* Estado.
* Imagen o icono representativo si resulta sencillo.

NO utilizar precios reales.

Los precios serán ficticios.

Ejemplo:

Bitcoin:

Q500,000

Ethereum:

Q25,000

Dogecoin:

Q1.50

Los valores son únicamente ejemplos y pueden modificarse.

---

# 11. DASHBOARD DEL USUARIO

Crear un dashboard atractivo.

Debe mostrar:

### Tarjeta 1

Saldo disponible.

### Tarjeta 2

Valor del portafolio.

### Tarjeta 3

Cantidad de operaciones.

### Tarjeta 4

Cantidad de criptomonedas que posee.

Además:

* Resumen de portafolio.
* Últimas transacciones.
* Criptomonedas disponibles.
* Botones para comprar/vender.
* Gráfico sencillo de actividad.

El dashboard debe ser una de las partes visualmente más cuidadas.

---

# 12. MERCADO DE CRIPTOMONEDAS

Crear una sección llamada:

**Mercado**

Mostrar las criptomonedas mediante tarjetas.

Cada tarjeta puede incluir:

* Icono.
* Nombre.
* Símbolo.
* Precio.
* Variación simulada.
* Botón Comprar.
* Botón Vender.

La variación puede ser solamente visual/simulada.

No necesitamos un sistema financiero real.

---

# 13. COMPRA

Crear un formulario o modal de compra.

Ejemplo:

```text
Comprar Bitcoin

Precio actual:
Q500,000

Cantidad:
[ 0.01 ]

Total:
Q5,000

Saldo disponible:
Q10,000

[Cancelar] [Comprar]
```

Antes de completar:

* Verificar saldo.
* Verificar cantidad.
* Verificar criptomoneda.
* Calcular total.

Después:

* Restar saldo.
* Aumentar cantidad de criptomoneda.
* Registrar transacción.
* Mostrar mensaje de éxito.

---

# 14. VENTA

Crear un formulario o modal de venta.

Debe:

* Mostrar cantidad disponible.
* Permitir ingresar cantidad.
* Calcular total.
* Verificar que tenga suficiente.
* Restar criptomoneda.
* Aumentar saldo.
* Registrar transacción.

---

# 15. PORTAFOLIO

Crear una sección:

**Mi Portafolio**

Mostrar:

* Criptomoneda.
* Cantidad.
* Precio actual.
* Valor estimado.
* Botón vender.

También mostrar:

**Valor total del portafolio**

---

# 16. HISTORIAL DE TRANSACCIONES

Crear una sección:

**Historial**

Mostrar una tabla con:

* Fecha.
* Criptomoneda.
* Tipo.
* Cantidad.
* Precio.
* Total.

Utilizar badges para diferenciar:

* COMPRA
* VENTA

Agregar filtros sencillos si no complican el proyecto.

Por ejemplo:

* Todas.
* Compras.
* Ventas.

---

# 17. PERFIL DEL USUARIO

Agregar una sección sencilla:

**Mi Perfil**

Mostrar:

* Nombre.
* Correo.
* Fecha de registro.
* Estado de cuenta.

Opcionalmente:

* Cantidad de operaciones.
* Valor del portafolio.

NO crear edición compleja del perfil si no es necesaria.

---

# 18. ÁREA ADMINISTRATIVA

Crear un dashboard separado para:

**Administrador**

Debe tener un menú diferente al usuario normal.

Mostrar tarjetas como:

* Usuarios registrados.
* Usuarios activos.
* Usuarios suspendidos.
* Total de transacciones.
* Total de compras.
* Total de ventas.

---

# 19. ADMINISTRACIÓN DE USUARIOS

El administrador debe poder:

* Ver usuarios.
* Consultar información.
* Suspender cuentas.
* Reactivar cuentas.
* Dar de baja usuarios.

Debe existir una confirmación antes de realizar acciones importantes.

Ejemplo:

"¿Está seguro de que desea suspender esta cuenta?"

---

# 20. REPORTES

Crear una sección administrativa llamada:

**Reportes**

Debe incluir como mínimo:

### Reporte 1

Movimientos por usuario.

### Reporte 2

Balance general.

### Reporte 3

Criptomonedas más compradas.

### Reporte 4

Criptomonedas más vendidas.

### Reporte 5

Usuarios con mayor cantidad de transacciones.

---

# 21. GRÁFICAS

Se permite utilizar:

**Chart.js**

Utilizarlo únicamente para gráficos sencillos.

Por ejemplo:

### Gráfico de compras vs ventas

Un gráfico de barras.

### Criptomonedas más utilizadas

Gráfico de dona o circular.

### Actividad

Gráfico de transacciones.

No crear gráficos excesivamente complejos.

---

# 22. BASE DE DATOS MYSQL

Diseñar la base de datos utilizando MySQL Workbench.

Como mínimo deben existir entidades equivalentes a:

```text
usuarios
criptomonedas
transacciones
```

Se pueden agregar tablas adicionales cuando sean realmente necesarias.

Por ejemplo:

```text
roles
portafolio
```

Pero NO crear tablas innecesarias.

---

# 23. ESTRUCTURA SUGERIDA DE BASE DE DATOS

Una propuesta inicial podría ser:

## usuarios

```text
id_usuario
nombre
correo
password
saldo
rol
estado
fecha_registro
```

## criptomonedas

```text
id_criptomoneda
nombre
simbolo
precio
estado
```

## portafolio

```text
id_portafolio
id_usuario
id_criptomoneda
cantidad
```

## transacciones

```text
id_transaccion
id_usuario
id_criptomoneda
tipo
cantidad
precio
total
fecha
```

Esta estructura es solamente una propuesta.

Antes de crearla:

* Analízala.
* Verifica relaciones.
* Evita redundancia.
* Mantén normalización razonable.
* Explícame cualquier cambio importante.

---

# 24. MYSQL WORKBENCH

Quiero que toda la base de datos sea fácilmente manejable desde:

**MySQL Workbench**

Por eso debes generar:

```text
database.sql
```

Este archivo debe contener:

* CREATE DATABASE.
* CREATE TABLE.
* Primary Keys.
* Foreign Keys.
* Constraints necesarias.
* INSERT de criptomonedas.
* Usuario administrador de prueba.
* Datos de prueba cuando sean necesarios.

Debe poder ejecutarse desde MySQL Workbench.

---

# 25. SEGURIDAD

Implementar únicamente seguridad básica y apropiada para el proyecto.

Utilizar:

* password_hash().
* password_verify().
* Sesiones PHP.
* Validación del servidor.
* Consultas preparadas.
* Control de roles.
* Protección de páginas privadas.
* Escape de datos mostrados.

NO complicar la seguridad con sistemas empresariales.

---

# 26. MANEJO DE ERRORES

El sistema debe mostrar mensajes claros.

Ejemplos:

```text
Saldo insuficiente.
```

```text
Cantidad no válida.
```

```text
No posee suficiente cantidad para realizar esta venta.
```

```text
Correo electrónico ya registrado.
```

```text
Usuario o contraseña incorrectos.
```

```text
Su cuenta se encuentra suspendida.
```

Los mensajes deben verse integrados al diseño.

---

# 27. DETALLES CREATIVOS PERMITIDOS

Quiero que el proyecto tenga creatividad, pero únicamente con funcionalidades fáciles de implementar.

Puedes proponer cosas como:

### Indicador de actividad

Mostrar:

```text
Actividad reciente
● Compra de BTC
● Venta de DOGE
● Compra de ETH
```

### Estadísticas personales

Por ejemplo:

```text
12 operaciones
7 compras
5 ventas
```

### Resumen de portafolio

Mostrar visualmente qué porcentaje representa cada criptomoneda.

### Ranking interno

Un pequeño apartado:

**Tu actividad**

Comparar únicamente estadísticas del usuario consigo mismo, por ejemplo:

```text
Operaciones este mes
+25%
```

No es necesario comparar usuarios entre sí en la parte pública.

### Estado del mercado

Un indicador visual:

```text
Mercado activo
```

Esto debe ser solamente decorativo/simulado.

### Mensaje de bienvenida

Ejemplo:

```text
Bienvenido nuevamente, Vane.
¿Listo para continuar con tu simulación?
```

---

# 28. COSAS CREATIVAS QUE NO DEBES HACER

NO agregar:

* Wallet real.
* Blockchain.
* Transferencias reales.
* Dinero real.
* API de Binance.
* API de Coinbase.
* Pagos.
* Trading real.
* WebSockets.
* IA.
* Predicciones de precios.
* Noticias financieras.
* Compra de criptomonedas reales.
* Sistemas complejos de inversión.

Todo debe mantenerse como:

**SIMULADOR EDUCATIVO**

---

# 29. RESPONSIVIDAD

El sistema debe funcionar correctamente en:

* Computadora.
* Tablet.
* Teléfono.

Priorizar:

* Dashboard.
* Tablas.
* Formularios.
* Tarjetas.
* Menú.

En dispositivos pequeños, las tablas deben poder desplazarse horizontalmente si es necesario.

---

# 30. ESTRUCTURA DEL CÓDIGO

Antes de programar, analiza la estructura actual del repositorio.

NO elimines archivos sin revisarlos.

Utiliza una estructura sencilla.

Una posible estructura:

```text
/
├── config/
│   └── conexion.php
│
├── css/
│   └── estilos.css
│
├── js/
│   └── app.js
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── auth.php
│
├── admin/
│   ├── dashboard.php
│   ├── usuarios.php
│   ├── transacciones.php
│   └── reportes.php
│
├── usuario/
│   ├── dashboard.php
│   ├── mercado.php
│   ├── portafolio.php
│   ├── historial.php
│   └── perfil.php
│
├── auth/
│   ├── login.php
│   ├── registro.php
│   └── logout.php
│
├── index.php
├── database.sql
└── README.md
```

Adapta esta estructura si el proyecto existente utiliza otra organización.

---

# 31. FORMA DE TRABAJO DE OPENCODE

NO construyas todo de una sola vez.

Trabaja por fases.

## FASE 1

Analizar repositorio.

NO modificar código todavía.

Mostrar:

* Tecnologías detectadas.
* Archivos existentes.
* Configuración.
* Base de datos existente.
* Problemas encontrados.
* Qué se puede reutilizar.

---

## FASE 2

Diseñar la arquitectura.

Mostrar:

* Estructura de carpetas.
* Flujo de usuarios.
* Flujo de administrador.
* Modelo de base de datos.
* Relaciones.
* Tecnologías.

Esperar mi confirmación.

---

## FASE 3

Crear la base de datos MySQL.

Crear:

```text
database.sql
```

Comprobar relaciones.

---

## FASE 4

Crear conexión PHP + MySQL.

Probar conexión.

---

## FASE 5

Crear registro y login.

Probar:

* Registro.
* Login.
* Logout.
* Sesiones.

---

## FASE 6

Crear dashboard.

---

## FASE 7

Crear mercado de criptomonedas.

---

## FASE 8

Crear compras y ventas.

---

## FASE 9

Crear portafolio e historial.

---

## FASE 10

Crear administración.

---

## FASE 11

Crear reportes y gráficas.

---

## FASE 12

Pulir diseño.

---

## FASE 13

Realizar pruebas completas.

---

# 32. REGLA DESPUÉS DE CADA FASE

Después de cada fase debes indicarme:

### Qué hiciste

Lista breve.

### Archivos creados

Lista.

### Archivos modificados

Lista.

### Cómo probarlo

Pasos concretos.

### Resultado esperado

Qué debería aparecer.

### Siguiente fase

Qué se hará después.

No avances automáticamente a la siguiente fase si necesitas una decisión mía.

---

# 33. FORMA DE EXPLICARME EL CÓDIGO

Soy estudiante.

Necesito poder explicar el proyecto durante una presentación.

Por eso:

* Utiliza código sencillo.
* No sobreingenierices.
* Evita soluciones innecesariamente sofisticadas.
* Utiliza nombres claros.
* Explícame solamente las partes importantes.
* No llenes el código de comentarios innecesarios.

Cuando haya una decisión técnica importante, explica brevemente:

**Qué se hizo**
**Por qué se hizo**
**Cómo funciona**

---

# 34. PRUEBAS OBLIGATORIAS

Antes de finalizar:

## Registro

Probar:

* Registro correcto.
* Correo duplicado.
* Campos vacíos.
* Correo inválido.
* Contraseñas diferentes.

## Login

Probar:

* Credenciales correctas.
* Credenciales incorrectas.
* Usuario suspendido.

## Compra

Probar:

* Compra correcta.
* Saldo insuficiente.
* Cantidad inválida.

## Venta

Probar:

* Venta correcta.
* Cantidad superior a la disponible.
* Cantidad inválida.

## Administración

Probar:

* Acceso administrativo.
* Acceso denegado para usuario normal.
* Suspensión.
* Reactivación.
* Baja.

## Reportes

Verificar que los datos mostrados correspondan a las transacciones almacenadas.

---

# 35. REVISIÓN FINAL CONTRA EL DOCUMENTO

Antes de finalizar, compara el sistema desarrollado contra el documento oficial del proyecto.

El documento establece como requisitos:

* Registro e inicio de sesión.
* Saldo ficticio inicial.
* Visualización de portafolio.
* Precios simulados o fijados.
* Compra y venta de mínimo 3 criptomonedas.
* Historial de transacciones.
* Saldo actualizado.
* Área de reportes.
* Historial por usuario.
* Balance general.
* Gráficas opcionales.
* Interfaz intuitiva, responsiva y moderna.
* Manejo de sesiones.
* Seguridad básica.
* Validaciones cliente/servidor.
* Base de datos estructurada.
* Administración de usuarios.
* Suspensión y baja de cuentas.

No marques una funcionalidad como terminada hasta verificarla.

---

# 36. DOCUMENTACIÓN FINAL

Crear:

```text
README.md
```

Debe incluir:

* Nombre del proyecto.
* Descripción.
* Objetivo.
* Tecnologías.
* Requisitos.
* Instalación.
* Configuración de MySQL.
* Importación del database.sql.
* Configuración de PHP.
* Usuario administrador de prueba.
* Funcionalidades.
* Estructura.
* Base de datos.
* Seguridad.
* Pruebas.

---

# 37. REGLA PRINCIPAL

Quiero un proyecto que tenga este equilibrio:

```text
                    PROYECTO
                       │
          ┌────────────┼────────────┐
          │            │            │
       FÁCIL         CREATIVO     COMPLETO
          │            │            │
        PHP          Diseño       CRUD
        MySQL        Dashboard    Usuarios
        HTML         Gráficas     Compras
        CSS          Tarjetas     Ventas
        JS           Estadísticas Reportes
```

No quiero:

```text
COMPLEJO
   ↓
Framework tras framework
   ↓
APIs
   ↓
Docker
   ↓
Blockchain
   ↓
Arquitectura empresarial
   ↓
Difícil de explicar
```

El objetivo es obtener un proyecto universitario **completo, bonito, funcional y defendible**, no construir una plataforma financiera real.

---

# 38. PRIMERA ACCIÓN

Comienza únicamente con el análisis del repositorio.

NO programes todavía.

Primero revisa todo lo existente.

Tu primera respuesta debe contener exactamente:

## 1. Tecnologías detectadas

## 2. Archivos existentes

## 3. Configuración actual

## 4. Qué se puede reutilizar

## 5. Qué hace falta

## 6. Propuesta de arquitectura sencilla

## 7. Propuesta de base de datos MySQL

## 8. Propuesta de funcionalidades creativas

## 9. Propuesta de diseño utilizando la paleta indicada

## 10. Plan de desarrollo por fases

## 11. Posibles dificultades

## 12. Siguiente paso

Después de este análisis:

**ESPERA MI CONFIRMACIÓN.**

No empieces a programar hasta que yo confirme.

# FIN DEL PROMPT

# nueva paleta de color
#EBE8DB
#D76C82
#B03052 
#3D0301


# -------------------------------------------------------------------

Introducción 
En la actualidad, las criptomonedas han ganado gran relevancia como una forma alternativa de 
inversión y comercio digital. Este proyecto tiene como objetivo diseñar e implementar una 
plataforma web interactiva que simule operaciones de compra y venta de criptomonedas en 
tiempo real con fines educativos. 
Los estudiantes deberán crear un sistema completo que permita a los usuarios registrarse, 
recibir un saldo ficticio inicial y realizar transacciones con al menos tres criptomonedas 
diferentes, simulando el comportamiento de una plataforma real de trading. El sistema debera 
de contar con un area administrativa para que puedan darse de baja a usuario o bien suspender 
cuentas, tambien en la parte administrativa se podra contar con diferentes reportes que 
indiquen que moneda ha sido la mas vendida, comprada, asi como que usuarios son los que 
mas transacciones generan.  
Antecedentes 
Se conoce como criptomoneda al dinero que se intercambia por medio de internet. Esta nueva 
forma de dinero no puede tocarse, como sucede con el dinero en efectivo, pero es muy útil a la 
hora de realizar compras o pagos. 
La primera criptomoneda fue el Bitcoin creado por "Satoshi Nakamoto", seudónimo que 
corresponde a una persona que nadie sabe exactamente quién es. Esta persona publicó en 
2009 un artículo en el que describía un sistema de pagos P2P al que llamó Bitcoin. 
Otra cosa que deben saber es que el valor de cada criptomoneda es variable, y que en los 
últimos tiempos está habiendo una especulación similar a los activos bursátiles. Por lo tanto, 
hay mucha gente que está creándose wallets en servicios donde pueden comprar 
criptomonedas, e intentar comprarlas para venderlas después y así ganar dinero. 
Objetivo General 
Diseñar y desarrollar una plataforma web que simule la compra y venta de criptomonedas con 
una interfaz gráfica atractiva y funcional, incluyendo una base de datos estructurada y un 
sistema de reportería. 
Requerimientos del Proyecto 
Funcionales 
• Registro e inicio de sesión de usuarios. 
• Asignación de un saldo ficticio inicial a cada nuevo usuario. 
• Visualización del portafolio de criptomonedas con precios actualizados (pueden 
simularse o fijarse). 
• Compra y venta de al menos 3 criptomonedas diferentes (ej. Bitcoin, Ethereum, 
Dogecoin). 
• Historial completo de transacciones (compra y venta). 
• Visualización del saldo actualizado. 
• Área de reportes: 
• Historial de movimientos por usuario. 
• Balance general. 
• Gráficas de actividad (opcional con librerías como Chart.js o similar). 
No funcionales 
• Interfaz intuitiva, responsiva y moderna. 
• Manejo de sesiones y seguridad básica. 
• Validaciones del lado cliente y servidor. 
Base de Datos 
• Usuarios (datos personales, saldo). 
• Criptomonedas (nombre, símbolo, valor simulado). 
• Transacciones (tipo, cantidad, fecha, valor, usuario). 
• Registro de sesiones o auditoría (opcional). 
Tecnologías Sugeridas 
• Frontend: HTML, CSS (o Tailwind), JavaScript (React, Vue o Vanilla). 
• Backend: Node.js con Express, PHP o Python (Flask/Django). 
• Base de Datos: MySQL, PostgreSQL. 
• Extras opcionales: JWT para autenticación, Docker, APIs simuladas de precios, gráficos 
con Chart.js. 
Organización 
• Equipos de 3 estudiantes. 
Entregables: 
• Código fuente. 
• Base de datos exportada. 
• Documento técnico (descripción, estructura, capturas de pantalla, decisiones 
técnicas). 
• Manual de usuario. 
• Presentación final. 
Valor del Proyecto: 
• 15 puntos. 
Rubrica: 
Criterio 
Puntaje  Detalles 
Funcionalidad completa 4 pts 
Cumple con todos los requerimientos de usuario y 
transacciones. 
Diseño de interfaz 
2 pts 
Interfaz limpia, navegable, responsiva y atractiva. 
Base de datos 
estructurada 
2 pts 
Relaciones correctas, uso de llaves primarias/foráneas, 
normalización. 
Seguridad y validaciones 2 pts 
Validaciones en frontend/backend y protección básica. 
Reportería funcional 
2 pts 
Historial, reportes y visualización de datos. 
Documentación técnica 1 pt 
Explicación clara del sistema, decisiones técnicas y 
estructura del proyecto. 
Presentación y 
demostración 
2 pts 
Explicación fluida, entendimiento del funcionamiento del 
sistema.