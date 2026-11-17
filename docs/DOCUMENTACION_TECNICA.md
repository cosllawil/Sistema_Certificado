# Documentación técnica

## 1. Descripción

Sistema web para administrar participantes, cursos, plantillas y certificados digitales con verificación pública.

## 2. Arquitectura

- **Backend:** PHP 8.1+ con controladores, servicios, modelos y repositorios.
- **Base de datos:** MySQL.
- **Frontend:** HTML, CSS y JavaScript sin framework principal.
- **PDF:** FPDF, FPDI y fuentes personalizadas.
- **Servidor:** Apache con `mod_rewrite` habilitado.
- **Entrada pública:** archivos ubicados en `public/` y rutas limpias definidas en `.htaccess`.

## 3. Instalación

1. Instalar PHP, Apache, MySQL y Composer.
2. Crear el archivo `.env` en la raíz del proyecto:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_certificado
DB_USERNAME=usuario_mysql
DB_PASSWORD=contraseña_mysql
```

3. Instalar dependencias:

```bash
composer install
```

4. Crear la base de datos e importar la estructura ubicada en `bd/`.
5. Verificar permisos de escritura en `storage/`.
6. Habilitar `mod_rewrite` en Apache.

## 4. Módulos principales

- Participantes.
- Cursos y tipos de certificado.
- Plantillas y configuración de campos.
- Emisión y verificación pública de certificados.
- Usuarios, roles y permisos.
- Auditoría.
- Reportes.
- Recuperación y cambio de contraseña.
- Configuración general e identidad visual.
- Backup y restore manual de base de datos.

## 5. Backup y restore

Los backups manuales se generan mediante `app/Services/DatabaseBackupService.php` y se guardan en:

```text
storage/backups/
```

El sistema permite:

- Crear un backup desde Configuración → Base de datos.
- Consultar el historial paginado.
- Buscar backups por nombre.
- Seleccionar un backup.
- Restaurarlo mediante confirmación modal.
- Crear automáticamente un backup de seguridad antes de restaurar.
- Registrar la cantidad de restauraciones por archivo.

El directorio de backups está protegido contra acceso web directo mediante `storage/backups/.htaccess`.

## 6. Seguridad

- No publicar el archivo `.env`.
- Mantener protegidos los backups y archivos de almacenamiento.
- Usar contraseñas seguras para MySQL y SMTP.
- Mantener activos los tokens CSRF.
- Validar permisos antes de acceder a módulos administrativos.
- Validar tipo y tamaño de imágenes subidas.
- No almacenar credenciales dentro del código fuente.

## 7. Validaciones realizadas

- Sintaxis PHP validada en `app/`, `public/` y `config/`.
- Sintaxis JavaScript validada en `assets/js/`.
- Rutas principales verificadas.
- Flujo de configuración, backup y restore revisado.
- Diseño responsive revisado para escritorio y móvil.
- Protección de archivos SQL verificada.

## 8. Mantenimiento recomendado

- Crear una copia estable antes de cambios grandes.
- Revisar periódicamente los logs de Apache y PHP.
- Limpiar backups antiguos según una política definida.
- Probar periódicamente la restauración de un backup.
- Mantener Composer y PHP actualizados dentro de versiones compatibles.
