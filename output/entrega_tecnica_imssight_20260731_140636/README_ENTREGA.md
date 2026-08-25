# Entrega tecnica IMSSight 20260731_140636

Contenido:

- `codigo/imssight_codigo_sin_videos_20260731_140636.tar.gz`: codigo fuente del proyecto sin `app/video/`.
- `database/imssight_20260731_140636.sql`: respaldo completo de la base MariaDB `imssight`.
- `database/imssight_20260731_140636.sql.gz`: mismo respaldo comprimido.
- `documentacion/GUIA_TECNICA_DESPLIEGUE_IMSSIGHT.md`: guia para despliegue con Docker, doble servidor sin contenedor, reingenieria, volumetria y ancho de banda.
- `documentacion/MANUAL_EJECUTIVO_INSTALACION_VOLUMETRIA_IMSSIGHT.docx`: manual ejecutivo-administrativo en formato Word, con enfasis en volumetria, versiones, puntos de montaje y comandos basicos.
- `CHECKSUMS_SHA256.txt`: huellas para verificar integridad de los archivos grandes.

Para empezar, abrir primero:

```text
documentacion/GUIA_TECNICA_DESPLIEGUE_IMSSIGHT.md
```

Versiones principales:

- PHP 8.2.31.
- Apache 2.4.67.
- MariaDB 10.11.17.
- Docker Engine 29.5.2.
- Docker Compose v5.1.3.

Nota: los videos locales no se incluyen en esta entrega. No cargar `app/video/` al servidor.
