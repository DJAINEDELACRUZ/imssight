# IMSSight - Estructura de Base de Datos

## Descripción general

IMSSight utiliza MariaDB/MySQL como motor principal.

La arquitectura está diseñada para:

- Especialidades médicas
- Temas clínicos
- Casos clínicos interactivos
- Escenas tipo simulador
- Exámenes
- Resultados de usuarios
- Motor de búsqueda interno

---

# Estructura general

```text
Especialidades
    ↓
Temas
    ↓
Casos Clínicos
    ↓
Escenas
    ↓
Exámenes
    ↓
Preguntas
```

---

# Tabla: especialidades

Catálogo principal de especialidades médicas.

```sql
CREATE TABLE imssight.especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    icono VARCHAR(100),
    color VARCHAR(20),
    activo TINYINT DEFAULT 1
);
```

## Campos

| Campo | Descripción |
|---|---|
| id | Identificador |
| nombre | Nombre de especialidad |
| icono | Material icon |
| color | Color hexadecimal |
| activo | Estado lógico |

---

# Tabla: temas

Temas clínicos pertenecientes a una especialidad.

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

## Relación

```text
especialidades 1 ---> N temas
```

---

# Tabla: casos_clinicos

Casos clínicos interactivos.

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

## Relación

```text
temas 1 ---> N casos_clinicos
```

---

# Tabla: escenas

Motor narrativo del simulador clínico.

Cada escena representa:
- texto
- multimedia
- preguntas
- decisiones
- videos
- consecuencias

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
    REFERENCES casos_clinicos(id)
);
```

## Relación

```text
casos_clinicos 1 ---> N escenas
```

---

# Tabla: usuarios

Usuarios del sistema.

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

- administrador
- profesor
- alumno
- residente

---

# Tabla: respuestas_usuario

Historial de respuestas de escenas.

```sql
CREATE TABLE imssight.respuestas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_escena INT NOT NULL,
    respuesta_usuario VARCHAR(10),
    correcta TINYINT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

# Motor de búsqueda

## Tabla: search_index

Índice interno para búsqueda tipo Encarta / IA local.

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

---

# Sistema de exámenes

## Tabla: examenes

Exámenes asociados a un caso clínico.

```sql
CREATE TABLE imssight.examenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(255),
    descripcion TEXT,
    activo TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

## Tabla: examen_preguntas

Banco de preguntas.

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
    orden_pregunta INT DEFAULT 0
);
```

---

## Tabla: examen_resultados

Resultados de evaluaciones.

```sql
CREATE TABLE imssight.examen_resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_examen INT NOT NULL,
    id_usuario INT NOT NULL,
    calificacion DECIMAL(5,2),
    respuestas_correctas INT,
    total_preguntas INT,
    intento INT DEFAULT 1,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

# Filosofía del sistema

IMSSight busca funcionar como:

- simulador clínico
- videojuego educativo
- plataforma de razonamiento médico
- sistema de evaluación
- biblioteca interactiva médica

Inspirado en:
- Encarta
- videojuegos narrativos
- simuladores clínicos
- plataformas LMS
- sistemas hospitalarios IMSS

---