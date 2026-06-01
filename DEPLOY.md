# Flujo de trabajo IMSSight

## Desarrollo local (MacBook)

Entrar al proyecto:

```bash
cd ~/projects/imssight
```

Abrir Visual Studio Code:

```bash
code .
```

Enviar cambios:

```bash
git add .
git commit -m "descripcion"
git push
```

---

## Servidor Ubuntu

Entrar al servidor:

```bash
ssh usuario@IP_DEL_SERVIDOR
```

Entrar al proyecto:

```bash
cd ~/projects/imssight
```

Actualizar cambios:

```bash
git pull
```

---

## Rebuild Docker (solo si cambia infraestructura)

Ejecutar únicamente si cambia:

- Dockerfile
- docker-compose.yml
- extensiones PHP
- paquetes
- versiones

```bash
docker compose up -d --build
```

---

### Ingresar a mysql para ejecutar consultas

```bash
djaine@uei-head:~/projects/imssight$ docker exec -it imssight-db mysql -u root -p
```

contraseña en docker-compose.yml

---

## Bitacora de produccion - 2026-06-01

### Servidor

- Host: `192.168.68.100`
- Ruta del proyecto: `/home/djaine/projects/imssight`
- Commit desplegado: `b4328b329dfa280d366d0da5cc4a1baa32090753`
- Contenedores activos:
  - `imssight-web`: `0.0.0.0:8080 -> 80/tcp`
  - `imssight-db`: `0.0.0.0:3307 -> 3306/tcp`
  - `imssight-phpmyadmin`: `0.0.0.0:8081 -> 80/tcp`

### Respaldo antes del despliegue

Se generaron respaldos en el servidor antes de reemplazar codigo y base de datos:

- Codigo: `/home/djaine/imssight_backups/imssight_code_20260601_194914.tar.gz`
- Base de datos: `/home/djaine/imssight_backups/imssight_db_20260601_194914.sql.gz`

### Base de datos migrada

Se reemplazo la base de datos de produccion con un dump completo generado desde local, incluyendo datos y estructura.

Conteos verificados despues de importar:

- `usuarios`: 7
- `muro_publicaciones`: 8
- `perfil_entradas`: 3
- `search_index`: 201

Indices importantes confirmados:

- `muro_publicaciones.idx_muro_publicaciones_feed`
- `chat_mensajes.idx_chat_destinatario_leido`
- `search_index.idx_search_fulltext`

### Pruebas realizadas

Endpoints verificados:

- Login: `POST /php/login.php` respondio `success=true`.
- Sesion: `GET /php/auth.php` respondio usuario admin autenticado.
- Muro: `GET /php/muro_publicaciones.php?limit=10&offset=0` respondio con paginacion.
- Busqueda: `GET /php/search.php?q=Djaine` devolvio resultados de usuario y perfil.
- Administracion de usuarios: `GET /php/admin_usuarios.php?q=` devolvio usuarios.

Tiempos de respuesta desde red local:

- `/pages/sign-in.html`: `0.040s`
- `/php/muro_publicaciones.php?limit=10&offset=0`: `0.048s`
- `/php/search.php?q=Djaine`: `0.058s`
- `/pages/index.html`: `0.091s`

### Ajuste rapido de menu lateral - 2026-06-01

Se detecto que el menu lateral cargaba `img/podcast.png` dentro del sidebar. Ese archivo pesa aproximadamente 2.1 MB y podia provocar que el menu apareciera incompleto o tarde en redes WiFi.

Se genero una version optimizada para el menu:

- Archivo nuevo: `app/img/podcast-menu.png`
- Peso aproximado: 24 KB
- Referencia actualizada en: `app/pages/componentes/menu.html`
- Prueba en produccion: `/img/podcast-menu.png` respondio en `0.064s`

Sintaxis PHP verificada sin errores en:

- `/var/www/html/php/muro_publicaciones.php`
- `/var/www/html/php/chat.php`
- `/var/www/html/php/search.php`
- `/var/www/html/php/admin_usuarios.php`

### Salud del servidor

- Disco raiz: 115 GB totales, 19 GB usados, 91 GB libres.
- RAM: 62 GiB totales, aproximadamente 60 GiB disponibles.
- Carga: `0.00, 0.00, 0.00`.
- Uso de contenedores al revisar:
  - `imssight-web`: 14 MiB RAM aprox.
  - `imssight-db`: 98 MiB RAM aprox.
  - `imssight-phpmyadmin`: 28 MiB RAM aprox.

### Observaciones para produccion real

- El servidor esta conectado por WiFi. Para uso institucional estable conviene conectarlo por cable Ethernet.
- La pagina de inicio carga algunas imagenes grandes en `/img` de 1 MB a casi 4 MB. Conviene comprimirlas a WebP/AVIF y servir versiones responsivas.
- Hay un `favicon.ico` faltante que genera 404 menor. No rompe funcionalidad.
- Apache reporta falta de `ServerName`; es ruido de configuracion, pero conviene fijarlo para logs limpios.
- Mantener paginacion en el muro. No volver a cargar todas las publicaciones de golpe.
- Mantener indices en muro, chat y busqueda. Son la proteccion principal cuando crezcan publicaciones y mensajes.
- El polling del chat es correcto para esta etapa, pero si la concurrencia sube mucho conviene migrar mensajes en tiempo real a WebSocket o Server-Sent Events.
- Antes de abrir fuera de la red local, agregar HTTPS, dominio, politica de cookies segura y respaldos automaticos diarios.
- No exponer phpMyAdmin publicamente. Si se requiere, limitarlo a red interna o VPN.
