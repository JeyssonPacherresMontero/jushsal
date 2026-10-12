-- ============================================================
-- JUSHSAL · Esquema Fase 1 (MySQL/MariaDB · XAMPP)
-- Importar en phpMyAdmin o: mysql -u root < database/schema.sql
-- ============================================================
CREATE DATABASE IF NOT EXISTS junta_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE junta_bd;
SET NAMES utf8mb4;

-- ---------- Seguridad / acceso ----------
CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(30) NOT NULL UNIQUE,            -- admin | comunicaciones
  descripcion VARCHAR(120) NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  rol_id TINYINT UNSIGNED NOT NULL,
  nombre VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,           -- password_hash()
  activo TINYINT(1) NOT NULL DEFAULT 1,
  intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  bloqueado_hasta DATETIME NULL,
  ultimo_acceso DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_usuarios_rol FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

-- ---------- Noticias ----------
CREATE TABLE IF NOT EXISTS categorias_noticia (
  id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS noticias (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  categoria_id SMALLINT UNSIGNED NULL,
  titulo VARCHAR(160) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  resumen VARCHAR(400) NOT NULL,
  contenido MEDIUMTEXT NULL,
  imagen_portada VARCHAR(255) NULL,              -- ruta relativa dentro de uploads/noticias/
  fecha_publicacion DATE NOT NULL,
  estado ENUM('borrador','publicado','archivado') NOT NULL DEFAULT 'borrador',
  destacada TINYINT(1) NOT NULL DEFAULT 0,
  autor_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_noticias_listado (estado, fecha_publicacion),
  CONSTRAINT fk_noticias_cat FOREIGN KEY (categoria_id) REFERENCES categorias_noticia(id) ON DELETE SET NULL,
  CONSTRAINT fk_noticias_autor FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Historial de presidentes (gestiones) ----------
CREATE TABLE IF NOT EXISTS gestiones_presidenciales (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  presidente_nombre VARCHAR(150) NOT NULL,
  periodo_inicio SMALLINT UNSIGNED NOT NULL,     -- año, ej. 2026
  periodo_fin SMALLINT UNSIGNED NULL,            -- NULL = en curso
  foto VARCHAR(255) NULL,                        -- uploads/presidentes/
  semblanza TEXT NULL,
  es_vigente TINYINT(1) NOT NULL DEFAULT 0,      -- solo UNO en 1 (se garantiza en transacción PHP)
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_gestion_periodo (periodo_inicio, periodo_fin),
  CONSTRAINT chk_periodo CHECK (periodo_fin IS NULL OR periodo_fin >= periodo_inicio)
) ENGINE=InnoDB;

-- "de tal año a año se realizó esto": hitos/obras/logros por año dentro de cada gestión
CREATE TABLE IF NOT EXISTS logros_gestion (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  gestion_id INT UNSIGNED NOT NULL,
  anio SMALLINT UNSIGNED NOT NULL,
  titulo VARCHAR(160) NOT NULL,
  descripcion TEXT NULL,
  imagen VARCHAR(255) NULL,
  orden SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_logros (gestion_id, anio, orden),
  CONSTRAINT fk_logros_gestion FOREIGN KEY (gestion_id) REFERENCES gestiones_presidenciales(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- Convocatorias a asamblea (popup) ----------
CREATE TABLE IF NOT EXISTS convocatorias (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  titulo VARCHAR(160) NOT NULL,                  -- ej. Asamblea General Ordinaria 29/09/2026
  imagen VARCHAR(255) NOT NULL,                  -- uploads/convocatorias/
  fecha_asamblea DATETIME NULL,
  mostrar_desde DATETIME NOT NULL,               -- vigencia del popup
  mostrar_hasta DATETIME NOT NULL,
  retraso_seg SMALLINT UNSIGNED NOT NULL DEFAULT 3,   -- espera antes de abrir el popup
  duracion_seg SMALLINT UNSIGNED NOT NULL DEFAULT 0,  -- 0 = hasta que el visitante lo cierre
  frecuencia ENUM('cada_visita','una_vez_por_sesion','una_vez_por_dia') NOT NULL DEFAULT 'una_vez_por_sesion',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_conv_vigencia (activo, mostrar_desde, mostrar_hasta),
  CONSTRAINT chk_conv_rango CHECK (mostrar_hasta > mostrar_desde),
  CONSTRAINT fk_conv_user FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- Comisiones de usuarios (16 sub sectores) ----------
CREATE TABLE IF NOT EXISTS comisiones (
  id SMALLINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  numero TINYINT UNSIGNED NOT NULL UNIQUE,
  nombre VARCHAR(100) NOT NULL,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS comision_presidentes (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  comision_id SMALLINT UNSIGNED NOT NULL,
  nombre_completo VARCHAR(150) NOT NULL,
  periodo_inicio SMALLINT UNSIGNED NOT NULL,
  periodo_fin SMALLINT UNSIGNED NULL,
  celular VARCHAR(20) NULL,                      -- SOLO visible en admin; nunca en la web pública
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_cp_comision FOREIGN KEY (comision_id) REFERENCES comisiones(id) ON DELETE CASCADE
) ENGINE=InnoDB;                                 -- NO existe columna DNI (decisión de privacidad)

-- ---------- Auditoría ----------
CREATE TABLE IF NOT EXISTS bitacora (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  usuario_id INT UNSIGNED NULL,
  accion VARCHAR(30) NOT NULL,                   -- login, crear, editar, eliminar
  entidad VARCHAR(40) NOT NULL,                  -- noticias, convocatorias, ...
  entidad_id INT UNSIGNED NULL,
  detalle VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_bitacora (entidad, entidad_id),
  CONSTRAINT fk_bitacora_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;
