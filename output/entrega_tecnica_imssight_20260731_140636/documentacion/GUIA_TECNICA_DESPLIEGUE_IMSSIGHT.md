# Guia tecnica de entrega y despliegue IMSSight

Fecha de corte: 2026-07-31 14:06 America/Mexico_City  
Commit de referencia: c1b5a35e9cfc  
Estado del codigo: incluye el estado actual del directorio de trabajo, con cambios locales aun no confirmados en Git.

## 1. Resumen ejecutivo

IMSSight es una plataforma web educativa e interactiva construida principalmente con PHP, HTML, JavaScript, CSS y MariaDB/MySQL. Actualmente opera en Docker con un contenedor web PHP/Apache, un contenedor MariaDB y un contenedor phpMyAdmin para administracion.

El paquete de entrega incluye:

- Codigo fuente del proyecto sin videos locales.
- Dump SQL completo de la base de datos `imssight`.
- Dockerfile y `docker-compose.yml` actuales.
- Instrucciones para despliegue en contenedor.
- Instrucciones para arquitectura de doble servidor sin contenedor.
- Recomendaciones para una posible reingenieria de backend.
- Estimacion de volumetria y ancho de banda.

Nota importante: los videos locales de `app/video/` no forman parte de esta entrega y no deben cargarse al servidor. Si en una fase posterior se requiere contenido en video, se recomienda publicarlo fuera del servidor aplicativo, por ejemplo en CDN, object storage, plataforma institucional de video o servidor multimedia dedicado.

## 2. Versiones identificadas

### Aplicacion

- Backend principal: PHP.
- PHP en contenedor: 8.2.31.
- Servidor web: Apache 2.4.67 sobre Debian.
- Extension de base de datos: PDO MySQL (`pdo`, `pdo_mysql`).
- Frontend: HTML, JavaScript y CSS estatico.
- Conteo aproximado de archivos de aplicacion:
  - PHP: 60.
  - HTML: 38.
  - JavaScript: 18.
  - CSS: 5.
  - Total de archivos bajo `app/`: 530.

### Base de datos

- Motor en uso: MariaDB.
- Imagen Docker: `mariadb:10.11`.
- Version reportada: 10.11.17-MariaDB-ubu2204.
- Base de datos: `imssight`.
- Usuario aplicativo actual: `imssight_user`.
- Charset de conexion PHP: `utf8mb4`.
- Objetos en base: 43.
- Tamano logico aproximado: 72 MB.

MariaDB 10.11 es compatible para este proyecto con sintaxis y conectores de MySQL/MariaDB comunes. Si se instala MySQL en lugar de MariaDB, usar preferentemente MySQL 8.0 LTS o superior y validar vistas, indices y collation antes de liberar.

### Docker

- Docker Engine local usado para la entrega: 29.5.2.
- Docker Compose local usado para la entrega: v5.1.3.
- Imagen web base: `php:8.2-apache`.
- Imagen phpMyAdmin: `phpmyadmin/phpmyadmin`.

## 3. Estructura del paquete

La entrega esta organizada asi:

```text
entrega_tecnica_imssight_20260731_140636/
  codigo/
    imssight_codigo_sin_videos_20260731_140636.tar.gz
  database/
    imssight_20260731_140636.sql
    imssight_20260731_140636.sql.gz
  documentacion/
    GUIA_TECNICA_DESPLIEGUE_IMSSIGHT.md
  README_ENTREGA.md
```

El dump SQL fue generado con:

```bash
docker exec imssight-db mariadb-dump -uroot -p --single-transaction --routines --triggers --events --databases imssight > imssight_20260731_140636.sql
```

Checksum SHA-256 del SQL sin comprimir:

```text
6ffa4b0974a7b78abeffc7f57a51c93893b6125ce9606d2c281fcabbba9656fb
```

Checksum SHA-256 del codigo sin videos:

```text
6ba7913c6328f37eccb3fef4f980953bee998b6d915782f2113faf316995d160
```

## 4. Configuracion actual Docker

Servicios actuales:

- `imssight-web`: aplicacion PHP/Apache, expone `8080:80`.
- `imssight-db`: MariaDB 10.11, expone `3307:3306`.
- `imssight-phpmyadmin`: phpMyAdmin, expone `8081:80`.

Variables actuales en `docker-compose.yml`:

```yaml
MYSQL_ROOT_PASSWORD: rootpass
MYSQL_DATABASE: imssight
MYSQL_USER: imssight_user
MYSQL_PASSWORD: MiPasswordSegura123!
```

Nota de seguridad: para produccion real, cambiar estas claves antes de exponer el servicio. No publicar phpMyAdmin a Internet; limitarlo a VPN, red interna o tunel SSH.

Archivo de conexion PHP actual:

```php
$host = 'imssight-db';
$db   = 'imssight';
$user = 'imssight_user';
$pass = 'MiPasswordSegura123!';
$charset = 'utf8mb4';
```

## 5. Despliegue recomendado en servidor Linux con contenedores

Estas instrucciones aplican para RHEL, Rocky Linux, AlmaLinux, Ubuntu Server o Debian. Si el servidor sera RHEL o derivado, sustituir comandos `apt` por `dnf`.

### 5.1 Requisitos minimos

Para piloto o uso institucional moderado:

- 2 vCPU.
- 4 GB RAM.
- 80 GB SSD.
- Linux 64 bits.
- Docker Engine y Docker Compose plugin.
- Firewall con puertos controlados.
- Dominio y HTTPS si saldra de red local.

Para produccion con alto crecimiento:

- 4 a 8 vCPU para aplicacion.
- 8 a 16 GB RAM.
- SSD/NVMe.
- Base de datos con almacenamiento separado y respaldos diarios.
- Proxy reverso con TLS, por ejemplo Nginx, Apache frontal, Traefik o Caddy.

### 5.2 Instalacion Docker en RHEL/Rocky/AlmaLinux

```bash
sudo dnf update -y
sudo dnf install -y yum-utils
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
sudo dnf install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
```

Cerrar sesion y volver a entrar para que aplique el grupo `docker`.

Validar:

```bash
docker --version
docker compose version
```

### 5.3 Instalacion Docker en Ubuntu/Debian

```bash
sudo apt update
sudo apt install -y ca-certificates curl gnupg
sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo $VERSION_CODENAME) stable" | sudo tee /etc/apt/sources.list.d/docker.list
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
sudo systemctl enable --now docker
sudo usermod -aG docker $USER
```

### 5.4 Copiar proyecto al servidor

Desde el servidor, crear ruta:

```bash
sudo mkdir -p /opt/imssight
sudo chown -R $USER:$USER /opt/imssight
cd /opt/imssight
```

Copiar el paquete desde USB, SCP o repositorio. Si se usa el `.tar.gz`:

```bash
tar -xzf imssight_codigo_sin_videos_20260731_140636.tar.gz -C /opt/imssight
cd /opt/imssight/imssight
```

Si el tar abre directamente el contenido del repo sin carpeta padre, entrar a la carpeta donde esten `Dockerfile` y `docker-compose.yml`.

### 5.5 Levantar contenedores

Editar `docker-compose.yml` y cambiar claves antes de produccion.

```bash
docker compose up -d --build
docker ps
```

Validar:

```bash
curl -I http://localhost:8080/
```

### 5.6 Restaurar base de datos en contenedor

Copiar el SQL al servidor, por ejemplo a `/opt/imssight/backups/imssight_20260731_140636.sql`.

Restaurar:

```bash
docker exec -i imssight-db mariadb -uroot -p < /opt/imssight/backups/imssight_20260731_140636.sql
```

Validar conteo de tablas:

```bash
docker exec -it imssight-db mariadb -uroot -p -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='imssight';"
```

### 5.7 Publicacion con dominio y HTTPS

No se recomienda exponer directamente `:8080` a Internet. Usar un proxy reverso:

```nginx
server {
    listen 80;
    server_name imssight.ejemplo.gob.mx;

    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Despues activar HTTPS con certificado institucional o Let's Encrypt segun politica local.

## 6. Arquitectura de doble servidor sin contenedores

Escenario: un servidor Linux para aplicacion web y otro servidor Linux para base de datos.

### 6.1 Servidor de base de datos

Instalar MariaDB 10.11 o MySQL 8.0:

```bash
sudo dnf install -y mariadb-server
sudo systemctl enable --now mariadb
sudo mysql_secure_installation
```

Crear base y usuario:

```sql
CREATE DATABASE imssight CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'imssight_user'@'IP_SERVIDOR_APP' IDENTIFIED BY 'CAMBIAR_PASSWORD_SEGURO';
GRANT ALL PRIVILEGES ON imssight.* TO 'imssight_user'@'IP_SERVIDOR_APP';
FLUSH PRIVILEGES;
```

Restaurar dump:

```bash
mysql -uroot -p < imssight_20260731_140636.sql
```

Ajustar firewall para permitir 3306 solo desde el servidor aplicativo:

```bash
sudo firewall-cmd --permanent --add-rich-rule='rule family="ipv4" source address="IP_SERVIDOR_APP" port protocol="tcp" port="3306" accept'
sudo firewall-cmd --reload
```

### 6.2 Servidor aplicativo

Instalar Apache/PHP:

```bash
sudo dnf install -y httpd php php-pdo php-mysqlnd php-mbstring php-json php-gd unzip tar
sudo systemctl enable --now httpd
```

Copiar el contenido de `app/` a la carpeta web por defecto:

```bash
sudo mkdir -p /var/www/html/imssight
sudo tar -xzf imssight_codigo_20260731_140636.tar.gz -C /tmp
sudo rsync -av /tmp/imssight/app/ /var/www/html/imssight/
sudo chown -R apache:apache /var/www/html/imssight
```

En Ubuntu/Debian el usuario suele ser `www-data:www-data`.

Actualizar `app/php/conn.php` en el servidor aplicativo:

```php
$host = 'IP_O_DNS_SERVIDOR_DB';
$db   = 'imssight';
$user = 'imssight_user';
$pass = 'CAMBIAR_PASSWORD_SEGURO';
$charset = 'utf8mb4';
```

Configurar VirtualHost:

```apache
<VirtualHost *:80>
    ServerName imssight.ejemplo.gob.mx
    DocumentRoot /var/www/html/imssight

    <Directory /var/www/html/imssight>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog /var/log/httpd/imssight_error.log
    CustomLog /var/log/httpd/imssight_access.log combined
</VirtualHost>
```

Reiniciar:

```bash
sudo apachectl configtest
sudo systemctl reload httpd
```

### 6.3 Carpetas por defecto

Si el equipo receptor quiere "soltar" archivos:

- Codigo aplicativo: copiar contenido de `app/` a `/var/www/html/imssight/` o `/var/www/html/`.
- Base de datos: importar `database/imssight_20260731_140636.sql` en MariaDB/MySQL.
- Configuracion de conexion: editar `/var/www/html/imssight/php/conn.php`.
- Archivos estaticos: conservar `img/`, `assets/` y `docs/` con permisos de lectura del usuario web.
- Videos: no cargar `app/video/` al servidor. Esta entrega fue preparada sin videos locales.

## 7. Respaldo, restauracion y operacion

### Respaldo diario recomendado

```bash
mkdir -p /opt/backups/imssight
docker exec imssight-db mariadb-dump -uroot -p --single-transaction --routines --triggers --events imssight | gzip > /opt/backups/imssight/imssight_$(date +%F_%H%M).sql.gz
tar -czf /opt/backups/imssight/imssight_code_$(date +%F_%H%M).tar.gz /opt/imssight
```

Conservar:

- Diario: 7 a 14 dias.
- Semanal: 8 semanas.
- Mensual: 12 meses.

### Monitoreo minimo

- CPU, RAM y disco.
- Logs Apache/PHP.
- Errores 500/404.
- Tiempo de respuesta de login, busqueda, muro y recursos multimedia.
- Tamano de tablas grandes: `demanda_unidad_mensual_geo`, `prediccion_demanda`, `search_index`.

## 8. Volumetria actual sin videos

Tamano local aproximado:

- Paquete de codigo sin videos: 212 MB.
- Proyecto original con videos locales: 7.0 GB.
- `app/video` excluido de la entrega: 6.5 GB.
- `app/img`: 154 MB.
- `app/assets`: 25 MB.
- `app/docs`: 54 MB.
- Base SQL sin comprimir: 56 MB.
- Base logica reportada por MariaDB: 72 MB.

Tablas principales por volumen:

- `demanda_unidad_mensual_geo`: 281,559 filas, 46.58 MB.
- `prediccion_demanda`: 80,114 filas, 21.09 MB.
- `search_index`: 259 filas, 1.53 MB.
- `escenas`: 140 filas, 1.53 MB.

Principal factor de ancho de banda despues de excluir videos:

- Imagenes grandes en `app/img`: varias entre 2 MB y 6.6 MB.
- Documentos en `app/docs`: 54 MB totales.
- La carpeta `app/video/` no debe desplegarse en el servidor aplicativo.

## 9. Estimacion para 100,000 usuarios no simultaneos

Suposicion: 100,000 usuarios registrados o potenciales en un periodo, no todos conectados al mismo tiempo. En plataformas educativas, la concurrencia real suele variar entre 1% y 10% segun campanas, horarios de clase, examenes o eventos.

Escenarios de concurrencia:

- Bajo: 1% concurrente = 1,000 usuarios activos.
- Medio: 3% concurrente = 3,000 usuarios activos.
- Alto: 10% concurrente = 10,000 usuarios activos en picos.

### Trafico si se optimizan recursos

Si cada sesion consume 20 a 50 MB de HTML, CSS, JS, imagenes optimizadas y algunos contenidos:

- 100,000 sesiones/mes x 20 MB = 2 TB/mes.
- 100,000 sesiones/mes x 50 MB = 5 TB/mes.

### Videos excluidos de esta entrega

Los videos locales no se incluyen en el paquete y no se deben publicar desde el contenedor Apache ni desde la carpeta web por defecto. Esto reduce el paquete de codigo de aproximadamente 6.7 GB a 212 MB y evita consumo masivo de ancho de banda.

Si en otra etapa se decide usar videos, publicarlos en una plataforma externa o servicio multimedia y referenciarlos por URL; no copiarlos dentro de `app/video/`.

### Comportamiento esperado

Para que la plataforma se comporte bien:

- Paginar todo listado de muro, busqueda, usuarios, notificaciones y chat.
- Mantener indices en tablas de busqueda, muro y chat.
- Cachear archivos estaticos con headers de larga duracion.
- Comprimir imagenes a WebP/AVIF con variantes responsivas.
- Mantener videos fuera del servidor aplicativo; si se requieren, servirlos desde plataforma especializada.
- Separar base de datos en servidor dedicado cuando haya crecimiento.
- Agregar balanceador y multiples instancias web si hay mas de 1,000 usuarios concurrentes.
- Evitar phpMyAdmin expuesto.
- Usar HTTPS y cookies seguras.

### Dimensionamiento sugerido

Piloto hasta 200 concurrentes:

- 1 servidor: 4 vCPU, 8 GB RAM, 150 GB SSD.
- Docker Compose con web + DB.

Produccion media hasta 1,000 concurrentes:

- Web: 2 instancias de 4 vCPU y 8 GB RAM detras de Nginx/HAProxy.
- DB: 4 a 8 vCPU, 16 GB RAM, SSD/NVMe.
- CDN/object storage para imagenes pesadas y documentos si el trafico crece.

Produccion alta de 3,000 a 10,000 concurrentes:

- Web horizontal: 3 a 8 instancias.
- DB dedicada con replicas de lectura si aplica.
- Cache Redis para sesiones, busquedas frecuentes y contadores.
- CDN recomendado para estaticos pesados.
- Observabilidad: metricas, logs centralizados y alertas.

## 10. Reingenieria si se cambia PHP por otro backend

Si el equipo decide migrar a otro lenguaje, no iniciar por reescritura total. Recomendacion:

1. Congelar contrato funcional: listar endpoints PHP actuales, parametros, respuestas JSON y tablas usadas.
2. Separar estaticos: mantener HTML/CSS/JS inicialmente y migrar solo endpoints `/php/*.php`.
3. Crear API nueva versionada, por ejemplo `/api/v1`.
4. Mantener MariaDB al inicio para reducir riesgo.
5. Implementar autenticacion y sesiones con estrategia clara: cookies seguras, JWT o sesiones server-side.
6. Migrar modulo por modulo: login, usuarios, muro, chat, busqueda, examenes, contenidos, metricas.
7. Escribir pruebas de regresion con respuestas esperadas de endpoints actuales.
8. Solo despues evaluar cambio de base de datos, arquitectura frontend o microservicios.

Opciones razonables:

- Laravel o Symfony si se desea seguir en PHP con estructura empresarial.
- Node.js con NestJS si el equipo domina TypeScript.
- Python con FastAPI si se prioriza rapidez, datos y APIs limpias.
- Java/Spring Boot o C#/.NET si la institucion ya opera esos stacks.

Recomendacion practica: para continuidad, primero ordenar PHP actual o migrar a Laravel; para APIs modernas con equipo mixto, NestJS o FastAPI son opciones viables.

## 11. Seguridad antes de produccion

- Cambiar todas las contrasenas incluidas en `docker-compose.yml` y `conn.php`.
- Usar variables de entorno o archivos `.env` fuera de Git.
- Activar HTTPS.
- Configurar cookies `Secure`, `HttpOnly` y `SameSite`.
- Restringir CORS si se crea API.
- No exponer puerto 3306/3307 a Internet.
- No exponer phpMyAdmin a Internet.
- Crear usuario de base con permisos minimos.
- Revisar subida de archivos, tipos MIME y tamanos maximos.
- Revisar consultas para SQL injection aunque PDO ya ayuda si se usan prepared statements.
- Configurar respaldos cifrados y prueba de restauracion mensual.

## 12. Validacion posterior al despliegue

Checklist:

- La pagina principal carga por dominio/URL final.
- Login funciona.
- Sesion se conserva.
- Busqueda responde.
- Muro publica, lista y pagina.
- Chat envia y recibe.
- Examenes guardan respuestas.
- Recursos `img/`, `assets/` y `docs/` cargan.
- La carpeta `app/video/` no esta incluida y no debe validarse como requisito de despliegue.
- No hay errores 500 en logs.
- Base tiene 43 objetos despues de importar.
- Las tablas grandes existen y tienen datos.

Consultas utiles:

```sql
SELECT COUNT(*) FROM usuarios;
SELECT COUNT(*) FROM muro_publicaciones;
SELECT COUNT(*) FROM search_index;
SELECT COUNT(*) FROM demanda_unidad_mensual_geo;
SELECT COUNT(*) FROM prediccion_demanda;
```

## 13. Comandos rapidos de emergencia

Ver contenedores:

```bash
docker ps
```

Ver logs web:

```bash
docker logs --tail=200 imssight-web
```

Ver logs base:

```bash
docker logs --tail=200 imssight-db
```

Reiniciar:

```bash
docker compose restart
```

Respaldar base:

```bash
docker exec imssight-db mariadb-dump -uroot -p --single-transaction --routines --triggers --events imssight | gzip > imssight_backup.sql.gz
```

Restaurar base:

```bash
gunzip -c imssight_backup.sql.gz | docker exec -i imssight-db mariadb -uroot -p imssight
```
