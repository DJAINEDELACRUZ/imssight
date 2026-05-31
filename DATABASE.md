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
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_usuario)
    REFERENCES imssight.usuarios(id)
);
```

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
- `admin` y `docente` pueden moderar cualquier comentario.

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
- El autor de la publicación y participantes previos reciben notificación.
- La notificación abre `muro_publicacion.html?id=...`.

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
