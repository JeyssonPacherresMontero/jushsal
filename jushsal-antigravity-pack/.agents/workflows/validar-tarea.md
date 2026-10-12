---
description: Valida la tarea actual del PRP-001 (sintaxis PHP, BD, pruebas manuales y seguridad)
---

# /validar-tarea

1. Sintaxis PHP de todo el proyecto (PowerShell, desde la raíz):
   `Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & "C:\xampp\php\php.exe" -l $_.FullName }`
   Todos deben decir "No syntax errors detected".
2. Si la tarea toca la BD: reimportar y comprobar
   `& "C:\xampp\mysql\bin\mysql.exe" -u root -e "SOURCE database/schema.sql"` y luego `SOURCE database/seed.sql`;
   `SELECT COUNT(*) FROM comisiones;` → 16 · `SELECT presidente_nombre FROM gestiones_presidenciales WHERE es_vigente=1;` → Miguel Castillo Vera.
3. Si la tarea toca el popup: `curl.exe -s http://localhost/jushsal/api/convocatoria_activa.php` y comprobar el JSON.
4. Revisa los criterios "✅ Validar" de la tarea en el PRP y márcalos uno por uno (lista con ✔/✘).
5. Chequeo de seguridad rápido sobre lo escrito: PDO preparado, `e()` en toda salida, CSRF en POST, `requerir_rol()`, subida validada, sin DNI/celular en HTML público.
6. Para pruebas visuales en navegador (Antigravity browser): abrir la URL local, probar a 375 px y 1280 px, revisar consola sin errores y adjuntar captura como evidencia.
7. Informa el resultado. No marques la tarea como terminada si algún punto falla.
