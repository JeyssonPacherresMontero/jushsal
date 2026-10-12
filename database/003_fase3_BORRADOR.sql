-- ==================================================================
-- JUSHSAL · Fase 3 · BORRADOR (NO importar hasta cerrar la Tarea T0)
-- Los campos marcados [CONFIRMAR] dependen del cuestionario
-- (docs/FASE3_CUESTIONARIO.md). Solo tablas NUEVAS; no altera la Fase 1.
-- Respaldar junta_bd antes de importar (phpMyAdmin → Exportar).
-- ==================================================================
USE junta_bd;
SET NAMES utf8mb4;

INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES
 (3,'padron','Registro/secretaría: gestiona el padrón y ve datos personales completos');

-- Usuarios de agua (titulares del padrón)
CREATE TABLE IF NOT EXISTS usuarios_agua (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tipo_persona ENUM('natural','juridica') NOT NULL DEFAULT 'natural',
  nombre_razon_social VARCHAR(180) NOT NULL,
  doc_tipo ENUM('DNI','RUC','CE') NOT NULL,
  doc_numero VARCHAR(15) NOT NULL,                       -- SOLO roles admin/padron; mostrar enmascarado
  celular VARCHAR(20) NULL,
  correo VARCHAR(150) NULL,
  direccion VARCHAR(200) NULL,                           -- [CONFIRMAR] si se necesita
  comision_id SMALLINT UNSIGNED NULL,
  fecha_inscripcion DATE NULL,                           -- [CONFIRMAR]
  estado ENUM('activo','baja') NOT NULL DEFAULT 'activo',
  motivo_baja VARCHAR(200) NULL,
  fecha_baja DATE NULL,
  habil TINYINT(1) NOT NULL DEFAULT 0,                   -- [CONFIRMAR] criterio de habilidad; manual en Fase 3, calculado en Fase 4
  habil_motivo VARCHAR(200) NULL,
  habil_fecha DATE NULL,
  acepta_notificaciones TINYINT(1) NOT NULL DEFAULT 0,
  fecha_consentimiento DATE NULL,
  origen_consentimiento VARCHAR(80) NULL,                -- ficha, asamblea, WhatsApp, etc.
  created_by INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_doc (doc_tipo, doc_numero),
  INDEX idx_ua_comision (comision_id, estado),
  INDEX idx_ua_nombre (nombre_razon_social),
  CONSTRAINT fk_ua_comision FOREIGN KEY (comision_id) REFERENCES comisiones(id) ON DELETE SET NULL,
  CONSTRAINT fk_ua_creador FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Predios / unidades bajo riego (un usuario puede tener varios) [CONFIRMAR relación y campos]
CREATE TABLE IF NOT EXISTS predios (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  usuario_agua_id INT UNSIGNED NOT NULL,
  comision_id SMALLINT UNSIGNED NULL,
  codigo_predio VARCHAR(40) NULL,
  area_riego_ha DECIMAL(8,2) NULL,
  cultivo VARCHAR(80) NULL,
  canal_toma VARCHAR(100) NULL,
  licencia_numero VARCHAR(60) NULL,                      -- N.º de licencia/resolución del derecho de uso de agua
  activo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_predios_usuario (usuario_agua_id),
  CONSTRAINT fk_predio_usuario FOREIGN KEY (usuario_agua_id) REFERENCES usuarios_agua(id) ON DELETE CASCADE,
  CONSTRAINT fk_predio_comision FOREIGN KEY (comision_id) REFERENCES comisiones(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Representantes de personas jurídicas (carta poder y vigencia) [CONFIRMAR]
CREATE TABLE IF NOT EXISTS representantes (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  usuario_agua_id INT UNSIGNED NOT NULL,                 -- la persona jurídica representada
  nombre_completo VARCHAR(150) NOT NULL,
  doc_numero VARCHAR(15) NOT NULL,
  vigencia_desde DATE NULL,
  vigencia_hasta DATE NULL,
  documento_poder VARCHAR(255) NULL,                     -- PDF/imagen en uploads/poderes/ (privado)
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_rep_usuario FOREIGN KEY (usuario_agua_id) REFERENCES usuarios_agua(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Importaciones CSV del padrón
CREATE TABLE IF NOT EXISTS importaciones_padron (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  archivo_nombre VARCHAR(160) NOT NULL,
  filas_total INT UNSIGNED NOT NULL DEFAULT 0,
  filas_ok INT UNSIGNED NOT NULL DEFAULT 0,
  filas_error INT UNSIGNED NOT NULL DEFAULT 0,
  usuario_id INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_imp_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Notificaciones (envío asistido por WhatsApp/correo)
CREATE TABLE IF NOT EXISTS notificaciones (
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tipo ENUM('convocatoria','comunicado') NOT NULL DEFAULT 'comunicado',
  convocatoria_id INT UNSIGNED NULL,
  titulo VARCHAR(160) NOT NULL,
  mensaje TEXT NOT NULL,                                 -- plantilla con {nombre}, {comision}
  canal ENUM('whatsapp_manual','correo') NOT NULL DEFAULT 'whatsapp_manual',
  filtro_comision_id SMALLINT UNSIGNED NULL,             -- NULL = todas
  creado_por INT UNSIGNED NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_not_conv FOREIGN KEY (convocatoria_id) REFERENCES convocatorias(id) ON DELETE SET NULL,
  CONSTRAINT fk_not_comision FOREIGN KEY (filtro_comision_id) REFERENCES comisiones(id) ON DELETE SET NULL,
  CONSTRAINT fk_not_user FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS notificacion_destinatarios (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  notificacion_id INT UNSIGNED NOT NULL,
  usuario_agua_id INT UNSIGNED NOT NULL,
  estado ENUM('pendiente','enviado','fallido','omitido') NOT NULL DEFAULT 'pendiente',
  enviado_at DATETIME NULL,
  detalle VARCHAR(200) NULL,                             -- p. ej. "sin consentimiento", "sin celular"
  INDEX idx_nd_notif (notificacion_id, estado),
  CONSTRAINT fk_nd_notif FOREIGN KEY (notificacion_id) REFERENCES notificaciones(id) ON DELETE CASCADE,
  CONSTRAINT fk_nd_usuario FOREIGN KEY (usuario_agua_id) REFERENCES usuarios_agua(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- OPCIONAL (solo si el cuestionario §C lo confirma): acreditación y asistencia a asamblea ----------
-- CREATE TABLE IF NOT EXISTS acreditaciones_asamblea (
--   id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
--   convocatoria_id INT UNSIGNED NOT NULL,
--   usuario_agua_id INT UNSIGNED NOT NULL,
--   representante_id INT UNSIGNED NULL,
--   tipo ENUM('personal','representante') NOT NULL DEFAULT 'personal',
--   recibido_en DATETIME NOT NULL,
--   observacion VARCHAR(200) NULL,
--   asistio TINYINT(1) NOT NULL DEFAULT 0,
--   UNIQUE KEY uq_acred (convocatoria_id, usuario_agua_id),
--   CONSTRAINT fk_ac_conv FOREIGN KEY (convocatoria_id) REFERENCES convocatorias(id) ON DELETE CASCADE,
--   CONSTRAINT fk_ac_ua FOREIGN KEY (usuario_agua_id) REFERENCES usuarios_agua(id) ON DELETE CASCADE,
--   CONSTRAINT fk_ac_rep FOREIGN KEY (representante_id) REFERENCES representantes(id) ON DELETE SET NULL
-- ) ENGINE=InnoDB;
