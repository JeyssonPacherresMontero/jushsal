USE junta_bd;
SET NAMES utf8mb4;

INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
 (1,'admin','Acceso total al panel'),
 (2,'comunicaciones','Gestiona noticias, convocatorias y presidentes');

INSERT IGNORE INTO categorias_noticia (nombre, slug) VALUES
 ('Comunicado','comunicado'),('Gestión','gestion'),('Actividad','actividad'),('Campaña','campana'),('Convocatoria','convocatoria');

-- 16 comisiones reales (fuente: PRESIDENTES DE COMISIONES AÑO 2026, periodo 2026-2029)
INSERT IGNORE INTO comisiones (numero, nombre) VALUES
 (1,'Quiroz Paimas'),(2,'Chipillico Alto'),(3,'Quebrada Totoral Pampa Elera'),(4,'Chipillico Bajo'),
 (5,'Yuscay Tablazo Alto'),(6,'Tejedores'),(7,'TJ-05'),(8,'San Isidro I-II'),
 (9,'TG Malingas'),(10,'Hualtaco III'),(11,'M Malingas'),(12,'Hualtaco I-II-IV'),
 (13,'Valle de los Incas'),(14,'Somate Alto'),(15,'Somate Bajo'),(16,'Algarrobo Valle Hermoso');

-- Presidente vigente (firma de la convocatoria 29/09/2026 + Excel de comisiones, periodo 2026-2029)
INSERT INTO gestiones_presidenciales (presidente_nombre, periodo_inicio, periodo_fin, semblanza, es_vigente)
SELECT 'Miguel Castillo Vera', 2026, 2029, NULL, 1
WHERE NOT EXISTS (SELECT 1 FROM gestiones_presidenciales WHERE presidente_nombre='Miguel Castillo Vera' AND periodo_inicio=2026);

-- El usuario admin NO se siembra aquí: crear con  php database/crear_admin.php  (pide contraseña por consola).
-- Gestiones anteriores y logros: cargarlos desde el panel (no inventar datos).
