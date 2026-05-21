/************ tablas para casos clinicos ********/

CREATE TABLE imssight.especialidades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    icono VARCHAR(100),
    color VARCHAR(20),
    activo TINYINT DEFAULT 1
);

INSERT INTO imssight.especialidades(nombre, icono, color)
VALUES
('MEDICINA GENERAL', 'medical_services', '#235B4E'),
('MEDICINA INTERNA', 'monitor_heart', '#235B4E'),
('MEDICINA DE URGENCIAS', 'emergency', '#235B4E'),
('CIRUGÍA', 'surgical', '#235B4E');

SELECT * FROM imssight.especialidades e ;



CREATE TABLE imssight.temas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_especialidad INT,
    titulo VARCHAR(255),
    descripcion TEXT,
    imagen VARCHAR(255),

    FOREIGN KEY (id_especialidad)
    REFERENCES imssight.especialidades(id)
);

INSERT INTO imssight.temas
(id_especialidad, titulo, descripcion)
VALUES
(4, 'Apendicitis Aguda',
'Inflamación aguda del apéndice'),
(4, 'Colecistitis',
'Inflamación de la vesícula biliar');

SELECT * FROM imssight.temas;






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

INSERT INTO imssight.casos_clinicos
(
    id_tema,
    titulo,
    descripcion,
    dificultad
)
VALUES
(
    1,
    'Apendicitis aguda complicada',
    'Caso clínico interactivo de cirugía',
    'Intermedio'
);

SELECT * FROM imssight.casos_clinicos cc;

ALTER TABLE imssight.casos_clinicos
ADD COLUMN id_tema_nuevo;



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

SELECT * FROM imssight.escenas e ;




/********* creación de usuarios *******/
CREATE TABLE imssight.usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    matricula VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    rol VARCHAR(30),
    activo TINYINT DEFAULT 1
);

SELECT * FROM imssight.usuarios u ;


CREATE TABLE imssight.respuestas_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_escena INT NOT NULL,
    respuesta_usuario VARCHAR(10),
    correcta TINYINT DEFAULT 0,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id),
    FOREIGN KEY (id_escena)
    REFERENCES escenas(id)
);

SELECT * 
FROM imssight.respuestas_usuario;

TRUNCATE TABLE imssight.respuestas_usuario;

ALTER TABLE imssight.respuestas_usuario




/******** tablas para busqueda ****/
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


SELECT * FROM imssight.search_index;

SELECT url

FROM imssight.search_index

LIMIT 20;





/********* examenes ******/

CREATE TABLE imssight.examenes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_caso INT NOT NULL,
    titulo VARCHAR(255),
    descripcion TEXT,
    activo TINYINT DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE imssight.examenes
ADD INDEX idx_id_caso (id_caso);

INSERT INTO imssight.examenes (
    id_caso,
    titulo,
    descripcion
)
VALUES(
    1,
    'Evaluación final - Apendicitis aguda',
    'Examen final del caso clínico'
);

select * from imssight.examenes e ;



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

ALTER TABLE imssight.examen_preguntas
ADD INDEX idx_id_examen (id_examen);

INSERT INTO imssight.examen_preguntas (
    id_examen,
    pregunta,
    opcion_a,
    opcion_b,
    opcion_c,
    opcion_d,
    respuesta_correcta,
    explicacion,
    dificultad,
    orden_pregunta
)
VALUES(
    1,
    '¿Cuál es la causa más común de apendicitis aguda?',
    'Infección viral',
    'Obstrucción luminal por facalito',
    'Trombosis arterial',
    'Perforación intestina',
    'B',
    'La fisiopatología clásica inicia con obstrucción luminal, siendo el fecalito la causa más frecuente en adultos, lo que genera aumento de la presión intraluminal, compromiso vascular, proliferación bacteriana e inflamación.',
    'media',
    1
);

select * from imssight.examen_preguntas;



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

ALTER TABLE imssight.examen_resultados
ADD INDEX idx_id_usuario (id_usuario),
ADD INDEX idx_id_examen (id_examen);

TRUNCATE imssight.examen_resultados;
select * from imssight.examen_resultados;

DESCRIBE imssight.examen_resultados;