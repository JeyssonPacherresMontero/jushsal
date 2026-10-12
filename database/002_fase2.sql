-- ============================================================
-- JUSHSAL · Esquema Fase 2 (MySQL/MariaDB · XAMPP)
-- Documentos Institucionales, Galería y Mensajes de Contacto
-- ============================================================

USE junta_bd;
SET NAMES utf8mb4;

-- ---------- 1. Categorías de Documentos ----------
CREATE TABLE IF NOT EXISTS categorias_documento (
  id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(80) NOT NULL UNIQUE,
  slug VARCHAR(90) NOT NULL UNIQUE,
  orden TINYINT UNSIGNED NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- 2. Documentos Institucionales y Financieros ----------
CREATE TABLE IF NOT EXISTS documentos (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  categoria_id SMALLINT UNSIGNED NOT NULL,
  titulo VARCHAR(220) NOT NULL,
  descripcion VARCHAR(350) NULL,
  archivo_ruta VARCHAR(255) NOT NULL,            -- ruta relativa ej. documentos/ o uploads/documentos/
  nombre_original VARCHAR(255) NULL,
  formato VARCHAR(10) NOT NULL DEFAULT 'PDF',
  tamanio_bytes INT UNSIGNED NULL,
  anio SMALLINT UNSIGNED NULL,                   -- año fiscal o de emisión (ej. 2026)
  es_destacado TINYINT(1) NOT NULL DEFAULT 0,    -- 1 = visible en portada index.php
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  descargas INT UNSIGNED NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_doc_cat_activo (categoria_id, activo),
  INDEX idx_doc_destacado (es_destacado, activo),
  CONSTRAINT fk_doc_categoria FOREIGN KEY (categoria_id) REFERENCES categorias_documento(id),
  CONSTRAINT fk_doc_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- 3. Galería Fotográfica Institucional ----------
CREATE TABLE IF NOT EXISTS galeria_imagenes (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  titulo VARCHAR(150) NOT NULL,
  descripcion VARCHAR(255) NULL,
  imagen VARCHAR(255) NOT NULL,                  -- ruta en uploads/galeria/ o assets/images/
  es_destacada TINYINT(1) NOT NULL DEFAULT 0,    -- 1 = elemento ancho en grid (.gallery-wide)
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_galeria_activo (activo, orden),
  CONSTRAINT fk_galeria_usuario FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- 4. Bandeja de Mensajes de Contacto ----------
CREATE TABLE IF NOT EXISTS mensajes_contacto (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  telefono VARCHAR(25) NULL,
  mensaje TEXT NOT NULL,
  estado ENUM('pendiente', 'atendido', 'archivado') NOT NULL DEFAULT 'pendiente',
  notas_atencion TEXT NULL,                      -- notas internas del personal de comunicaciones
  ip VARCHAR(45) NULL,
  atendido_por INT UNSIGNED NULL,
  atendido_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_contacto_estado (estado, created_at),
  CONSTRAINT fk_contacto_usuario FOREIGN KEY (atendido_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- SEMILLAS INICIALES FASE 2 (INSERT IGNORE)
-- ============================================================

-- Categorías de Documentos
INSERT IGNORE INTO categorias_documento (id, nombre, slug, orden) VALUES
  (1, 'Leyes y Decretos Supremos', 'leyes-y-decretos', 1),
  (2, 'Resoluciones Jefaturales ANA', 'resoluciones-ana', 2),
  (3, 'Tarifas de Agua y POMDIH', 'tarifas-y-pomdih', 3),
  (4, 'Transparencia y Auditoría Financiera', 'transparencia-auditoria', 4),
  (5, 'Reglamentos y Guías Técnicas', 'reglamentos-guias', 5);

-- Documentos reales iniciales (tomados de la carpeta documentos/)
INSERT IGNORE INTO documentos (categoria_id, titulo, descripcion, archivo_ruta, formato, anio, es_destacado, orden) VALUES
  (1, 'Ley de Recursos Hídricos N.º 29338', 'Marco legal que regula el uso sostenible y gestión multisectorial de los recursos hídricos en el Perú.', 'documentos/LEY DE RECURSOS HIDRICOS N° 29338 ok.pdf', 'PDF', 2009, 1, 1),
  (3, 'Aprobación de Valores de Tarifa de Agua 2026', 'Resolución que fija los valores de la tarifa de agua para el sector hidráulico menor.', 'documentos/APRUEBAN VALORES DE TARIFA DE AGUA 2026.pdf', 'PDF', 2026, 1, 2),
  (2, 'R.J. 155 ANA - Reglamento de Operadores', 'Reglamento de operadores de infraestructura hidráulica mayor y menor.', 'documentos/r.j. 155 Reglamento Operadores.pdf.pdf', 'PDF', 2010, 1, 3),
  (2, 'R.J. 041-2018-ANA - Lineamientos de Supervisión', 'Lineamientos para la supervisión y fiscalización de las juntas de usuarios por la ANA.', 'documentos/R.J.041-2018-ANA Lineam.Superv.Juntas Usuarios (1).pdf', 'PDF', 2018, 1, 4),
  (1, 'Ley N.º 31801 - Regulación de las OUA', 'Ley que regula las Organizaciones de Usuarios de Agua para el fortalecimiento de la gestión de los RRHH.', 'documentos/LEY N° 31801 LEY QUE REGULA LAS OUA PARA EL FORALECIMIENTO EN LA GESTION MULTISECTORIAL DE LOS RRHH.pdf', 'PDF', 2023, 0, 5),
  (1, 'D.S. N.º 007-2024-MIDAGRI - Reglamento Ley 31801', 'Reglamento de la Ley 31801 aprobado por el MIDAGRI.', 'documentos/D.S N° 007-2024-MIDAGRI, REGLAMENTO.pdf', 'PDF', 2024, 0, 6),
  (4, 'RJ-239-2026-ANA - Auditoría de Estados Financieros', 'Disposiciones y lineamientos para la auditoría anual de estados financieros en juntas de usuarios.', 'documentos/RJ-239-2026-ANA  AUDITORIA ESTADOS FINANCIEROS.pdf', 'PDF', 2026, 0, 7),
  (4, 'RJ-239 Anexo - Lineamientos para Auditoría', 'Anexo técnico con los criterios de evaluación y auditoría financiera.', 'documentos/RJ -239-ANEXO LINEAMIENTOS PARA AUDITORIA.pdf', 'PDF', 2026, 0, 8),
  (3, 'RA-0249-2024 - Aprobación Tarifa POMDIH 2025', 'Aprobación de la tarifa para el Plan de Operación, Mantenimiento y Desarrollo de Infraestructura.', 'documentos/RA-0249-2024-04 Aprobacion de la Tarifa POMDIH 2025 OJO.pdf', 'PDF', 2024, 0, 9);

-- Galería inicial (basada en las fotos existentes en assets/images/)
INSERT IGNORE INTO galeria_imagenes (titulo, descripcion, imagen, es_destacada, orden) VALUES
  ('Sede Administrativa La Cruceta', 'Instalaciones principales de la Junta de Usuarios San Lorenzo en La Cruceta.', 'assets/images/sede.jpg', 1, 1),
  ('Presa y Repartidor Hidráulico', 'Estructuras de distribución y control del caudal de riego.', 'assets/images/repartidor.jpg', 0, 2),
  ('Válvulas y Compuertas de Control', 'Infraestructura de medición y derivación hacia las comisiones.', 'assets/images/valvula.jpg', 0, 3),
  ('Campos de Cultivo del Valle San Lorenzo', 'Valles agrícolas beneficiarios del sistema hidráulico de riego.', 'assets/images/campos.jpg', 0, 4),
  ('Reservorio San Lorenzo', 'Capacidad de almacenamiento y regulación del recurso hídrico.', 'assets/images/reservorio.jpg', 0, 5),
  ('Asamblea General de Usuarios', 'Participación activa de los regantes en la toma de decisiones institucionales.', 'assets/images/asamblea.jpg', 0, 6),
  ('Comunidad de Usuarios San Lorenzo', 'Familias y productores agrícolas comprometidos con el desarrollo del valle.', 'assets/images/about.jpg', 1, 7);
