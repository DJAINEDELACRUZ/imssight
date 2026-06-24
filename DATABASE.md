# IMSSight - Estructura de Base de Datos

Documentación breve de las tablas principales de IMSSight.

---

# Estructura general

```text
Especialidades
    ↓
Temas
    ↓
Casos Clínicos
    ├── Escenas
    ├── Perlas Clínicas
    └── Exámenes
            ↓
        Preguntas
            ↓
        Resultados

Usuarios
    ├── Perfil de usuario
    ├── Respuestas de escenas
    ├── Chat privado
    └── Publicaciones del muro
            ↓
        Comentarios del muro
            ↓
        Notificaciones

Motor de búsqueda
    ↓
Search Index
```

---

# Especialidades

Para crear especialidades médicas.

```sql
CREATE TABLE imssight.especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    icono VARCHAR(100),
    color VARCHAR(20),
    activo TINYINT DEFAULT 1
);
```

---

# Temas

Para crear temas clínicos dentro de una especialidad.

```sql
CREATE TABLE imssight.temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_especialidad INT,
    titulo VARCHAR(255),
    descripcion TEXT,
    imagen VARCHAR(255),

    FOREIGN KEY (id_especialidad)
    REFERENCES imssight.especialidades(id)
);
```

---

# Casos Clínicos

Para crear casos clínicos dentro de un tema.

```sql
CREATE TABLE imssight.casos_clinicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_tema INT,
    titulo VARCHAR(255),
    descripcion TEXT,
    dificultad VARCHAR(50),
    portada VARCHAR(255),
    activo TINYINT DEFAULT 1,

    FOREIGN KEY (id_tema)
    REFERENCES imssight.temas(id)
);
```

---

# Escenas

Para crear escenas narrativas de un caso clínico.

```sql
CREATE TABLE imssight.escenas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT,
    orden INT,
    tipo VARCHAR(50),
    titulo VARCHAR(255),
    contenido TEXT,
    multimedia VARCHAR(255),

    FOREIGN KEY (id_caso)
    REFERENCES imssight.casos_clinicos(id)
);
```

---

# Perlas Clínicas

Para crear perlas clínicas asociadas a un caso.

```sql
CREATE TABLE imssight.perlas_clinicas_caso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    seccion VARCHAR(150) NOT NULL,
    contenido TEXT,
    orden INT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_caso)
    REFERENCES imssight.casos_clinicos(id)
);
```

---

# Infografías por Caso

Para cargar imágenes clínicas y asociarlas a un caso.

```sql
CREATE TABLE imssight.infografias_caso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    objetivo TEXT,
    identificador VARCHAR(120) NOT NULL,
    ruta_imagen VARCHAR(255) NOT NULL,
    mime_type VARCHAR(80),
    tamano_bytes INT DEFAULT 0,
    alt_text VARCHAR(255),
    color_sugerido VARCHAR(20) DEFAULT '#1f5f4f',
    orden INT DEFAULT 1,
    activo TINYINT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_infografias_caso (id_caso, orden),
    UNIQUE KEY uq_infografias_identificador (identificador),

    FOREIGN KEY (id_caso)
    REFERENCES imssight.casos_clinicos(id)
    ON DELETE CASCADE
);
```

Notas:
- `ruta_imagen` guarda la ruta relativa servida por la app, por ejemplo `img/infografias/casos/apendicitis-1-20260623111500.png`.
- `identificador` permite ubicar la infografía desde el explorador visual y mantener compatibilidad con el buscador/hub de infografías.
- El endpoint administrativo es `app/php/infografias.php`; acepta `GET`, `POST multipart/form-data` y `DELETE`.

---

# Escalas Pronósticas por Caso

Para asociar calculadoras o escalas diagnósticas externas a un caso clínico.

```sql
CREATE TABLE imssight.escalas_pronosticas_caso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    url VARCHAR(600) NOT NULL,
    proveedor VARCHAR(120),
    orden INT DEFAULT 1,
    activo TINYINT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_escalas_caso (id_caso, orden),

    FOREIGN KEY (id_caso)
    REFERENCES imssight.casos_clinicos(id)
    ON DELETE CASCADE
);
```

Notas:
- `url` guarda el enlace externo de la escala o calculadora clínica.
- La página pública intenta mostrar el enlace embebido en `iframe`; si el proveedor bloquea embebidos, el usuario conserva un botón para abrir la escala en una pestaña nueva.
- El endpoint administrativo es `app/php/escalas.php`; acepta `GET`, `POST application/json` y `DELETE`.

---

# Exámenes

Para crear exámenes asociados a un caso clínico.

```sql
CREATE TABLE imssight.examenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(255),
    descripcion TEXT,
    activo TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_caso)
    REFERENCES imssight.casos_clinicos(id)
);
```

---

# Preguntas

Para crear preguntas de un examen.

```sql
CREATE TABLE imssight.examen_preguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_examen INT NOT NULL,
    pregunta TEXT,
    opcion_a TEXT,
    opcion_b TEXT,
    opcion_c TEXT,
    opcion_d TEXT,
    respuesta_correcta ENUM('A','B','C','D'),
    explicacion TEXT,
    dificultad VARCHAR(50),
    orden_pregunta INT DEFAULT 0,

    FOREIGN KEY (id_examen)
    REFERENCES imssight.examenes(id)
);
```

---

# Resultados

Para guardar resultados de exámenes por usuario.

```sql
CREATE TABLE imssight.examen_resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_examen INT NOT NULL,
    id_usuario INT NOT NULL,
    calificacion DECIMAL(5,2),
    respuestas_correctas INT,
    total_preguntas INT,
    intento INT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_examen)
    REFERENCES imssight.examenes(id),

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
);
```

---

# Usuarios

Para crear usuarios del sistema.

```sql
CREATE TABLE imssight.usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    matricula VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    rol VARCHAR(30),
    activo TINYINT DEFAULT 1
);
```

## Roles previstos

- `admin`: administra contenido y puede fijar publicaciones del muro.
- `docente`: publica contenido editorial y puede fijar publicaciones del muro.
- `usuario`: publica aportaciones normales de comunidad.

## Administración de permisos

- El módulo `admin_usuarios.html` edita `usuarios.rol` y `usuarios.activo`.
- Solo `admin` puede cambiar permisos.
- El sistema impide degradar o desactivar al último administrador activo.
- `profile.html` es el panel de administración; `user_profile.html` es el perfil social del usuario.

---

# Perfil de Usuario

Para guardar información complementaria del usuario.

```sql
CREATE TABLE imssight.usuarios_perfil (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL UNIQUE,
    telefono VARCHAR(30),
    correo_personal VARCHAR(150),
    sexo VARCHAR(20),
    fecha_nacimiento DATE,
    estado VARCHAR(100),
    universidad VARCHAR(255),
    especialidad VARCHAR(255),
    semestre VARCHAR(100),
    foto VARCHAR(255),
    biografia TEXT,
    hospital VARCHAR(255),
    cumpleanos_publico VARCHAR(40),
    puesto VARCHAR(150),
    etapa_profesional VARCHAR(100),
    intereses TEXT,
    frase_perfil VARCHAR(255),
    perfil_publico TINYINT DEFAULT 1,
    mostrar_correo TINYINT DEFAULT 0,
    mostrar_telefono TINYINT DEFAULT 0,
    mostrar_estado TINYINT DEFAULT 1,
    mostrar_biografia TINYINT DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
);
```

## Reglas

- `perfil_publico = 1` permite que el usuario aparezca en el buscador.
- `mostrar_correo` y `mostrar_telefono` controlan si esos datos se muestran en el perfil público.
- `mostrar_estado` y `mostrar_biografia` controlan datos académicos visibles.
- `hospital`, `puesto`, `etapa_profesional`, `intereses` y `frase_perfil` alimentan el perfil público y el buscador.
- `cumpleanos_publico` guarda una fecha libre visible sin exponer fecha de nacimiento completa.
- Sexo y fecha de nacimiento se conservan como datos privados del expediente.

---

# Entradas de Perfil

Para publicar frases, historias, aprendizajes o reflexiones dentro del perfil.

```sql
CREATE TABLE imssight.perfil_entradas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    tipo ENUM('frase','reflexion','historia','aprendizaje') DEFAULT 'reflexion',
    titulo VARCHAR(180),
    contenido TEXT NOT NULL,
    activo TINYINT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
    ON DELETE CASCADE
);
```

## Reglas

- Cada entrada pertenece a un usuario.
- `activo = 0` oculta la entrada sin borrarla físicamente.
- Las entradas se muestran en el perfil público si `perfil_publico = 1`.
- Se indexan en `search_index` como `perfil_entrada`.

---

# Respuestas de Escenas

Para guardar respuestas del usuario dentro de escenas.

```sql
CREATE TABLE imssight.respuestas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_escena INT NOT NULL,
    respuesta_usuario VARCHAR(10),
    correcta TINYINT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id),

    FOREIGN KEY (id_escena)
    REFERENCES imssight.escenas(id)
);
```

---

# Publicaciones del Muro

Para crear publicaciones fijas, noticias y aportaciones de usuarios.

```sql
CREATE TABLE imssight.muro_publicaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    autor_nombre VARCHAR(150) NOT NULL,
    autor_rol VARCHAR(50),
    tipo ENUM('institucional','experto','noticia','usuario') DEFAULT 'usuario',
    titulo VARCHAR(180),
    contenido TEXT NOT NULL,
    fuente VARCHAR(255),
    fijado TINYINT DEFAULT 0,
    fecha_fijado TIMESTAMP NULL,
    activo TINYINT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
    ON DELETE SET NULL
);
```

## Reglas

- `fijado = 1` muestra la publicación al inicio del muro.
- `fecha_fijado` ordena primero lo fijado más recientemente.
- `admin` y `docente` pueden fijar publicaciones existentes y usar tipos editoriales.
- `usuario` publica con tipo `usuario`.
- Los enlaces se guardan dentro de `contenido` y se renderizan como recursos visuales.
- El feed público se carga por páginas para evitar traer todo el muro en una sola petición.

## Índices recomendados

```sql
CREATE INDEX idx_muro_publicaciones_feed
ON imssight.muro_publicaciones(activo, fijado, fecha_fijado, fecha);

CREATE INDEX idx_muro_publicaciones_autor
ON imssight.muro_publicaciones(id_usuario, activo, fecha);
```
- El autor puede editar o eliminar su publicación.
- `admin` puede moderar cualquier publicación.
- `docente` puede moderar publicaciones propias y de usuarios comunes; no puede moderar contenido administrativo, institucional, experto o de otros docentes.

---

# Comentarios del Muro

Para crear comentarios dentro de publicaciones del muro.

```sql
CREATE TABLE imssight.muro_comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_publicacion INT NOT NULL,
    id_usuario INT NOT NULL,
    autor_nombre VARCHAR(150) NOT NULL,
    autor_rol VARCHAR(50),
    contenido TEXT NOT NULL,
    activo TINYINT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_publicacion)
    REFERENCES imssight.muro_publicaciones(id)
    ON DELETE CASCADE,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
    ON DELETE CASCADE
);
```

## Reglas

- `activo = 0` oculta el comentario sin romper la conversación.
- El autor puede borrar su comentario.
- `admin` puede moderar cualquier comentario.
- `docente` puede moderar comentarios propios y de usuarios comunes; no puede moderar comentarios de admin u otros docentes.

## Índice recomendado

```sql
CREATE INDEX idx_muro_comentarios_publicacion
ON imssight.muro_comentarios(id_publicacion, activo, fecha);
```

---

# Notificaciones

Para avisar eventos del muro y pendientes del usuario.

```sql
CREATE TABLE imssight.notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_destino INT NOT NULL,
    id_usuario_actor INT NULL,
    actor_nombre VARCHAR(150),
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    mensaje TEXT NOT NULL,
    url VARCHAR(255),
    id_publicacion INT NULL,
    id_comentario INT NULL,
    leida TINYINT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario_destino)
    REFERENCES imssight.usuarios(id)
    ON DELETE CASCADE,

    FOREIGN KEY (id_usuario_actor)
    REFERENCES imssight.usuarios(id)
    ON DELETE SET NULL
);
```

## Reglas

- `comentario_muro` se crea cuando alguien comenta una publicación.
- `mensaje_privado` se crea cuando alguien envía un mensaje por chat.
- El autor de la publicación y participantes previos reciben notificación.
- Las notificaciones de comentarios abren `muro_publicacion.html?id=...`.
- Las notificaciones de chat abren `chat.html?usuario_id=...`.

## Índice recomendado

```sql
CREATE INDEX idx_notificaciones_usuario_estado
ON imssight.notificaciones(id_usuario_destino, leida, fecha);
```

---

# Chat Privado

Para enviar mensajes privados entre usuarios.

```sql
CREATE TABLE imssight.chat_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_remitente INT NOT NULL,
    id_destinatario INT NOT NULL,
    contenido TEXT NOT NULL,
    leido TINYINT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_remitente)
    REFERENCES imssight.usuarios(id)
    ON DELETE CASCADE,

    FOREIGN KEY (id_destinatario)
    REFERENCES imssight.usuarios(id)
    ON DELETE CASCADE
);
```

## Reglas

- Cualquier usuario activo puede buscar y escribir a otro usuario activo.
- `leido = 1` se marca al abrir la conversación.

## Índices recomendados

```sql
CREATE INDEX idx_chat_conversacion_fecha
ON imssight.chat_mensajes(id_remitente, id_destinatario, fecha);

CREATE INDEX idx_chat_destinatario_leido
ON imssight.chat_mensajes(id_destinatario, leido, fecha);
```

---

# Search Index

Para crear el índice interno del motor de búsqueda.

```sql
CREATE TABLE imssight.search_index (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50),
    titulo VARCHAR(255),
    contenido LONGTEXT,
    descripcion TEXT,
    id_especialidad INT,
    id_tema INT,
    id_caso INT,
    id_escena INT,
    url TEXT
);
```

## Índice recomendado

```sql
ALTER TABLE imssight.search_index
ADD FULLTEXT INDEX idx_search_fulltext(titulo, contenido, descripcion);
```

## Tipos indexados

- `especialidad`: para buscar especialidades.
- `tema`: para buscar temas.
- `caso`: para buscar casos clínicos.
- `escena`: para buscar texto dentro de escenas.
- `usuario`: para buscar perfiles públicos por nombre, matrícula, rol o datos académicos visibles.
- `perfil_entrada`: para buscar frases, historias, aprendizajes o reflexiones de perfiles públicos.
