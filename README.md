# Sistema de Certificados

Sistema web para registrar participantes, cursos, plantillas y emitir certificados en PDF con código de verificación público.

## Funcionalidades

- Gestión de participantes, cursos, tipos de certificado y plantillas.
- Registro y emisión de certificados en PDF.
- Verificación pública mediante código de verificación.
- Usuarios, roles y permisos.
- Auditoría de acciones del sistema.
- Recuperación y cambio de contraseña.
- Reportes y exportación de información.

## Requisitos

- PHP 8.1 o superior.
- MySQL 8 o compatible.
- Apache con `mod_rewrite` habilitado.
- Composer.

## Instalación

1. Clonar el repositorio dentro del directorio público de Apache.

```bash
git clone https://github.com/cosllawil/Sistema_Certificado.git
cd Sistema_Certificado
```

2. Instalar las dependencias:

```bash
composer install
```

3. Crear un archivo `.env` en la raíz:

```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bd_certificado
DB_USERNAME=usuario_mysql
DB_PASSWORD=contraseña_mysql
```

4. Crear la base de datos e importar una estructura limpia del proyecto.

Las migraciones adicionales se encuentran en `bd/migrations/`.

5. Configurar Apache para que el proyecto utilice `public/` como directorio público.

## Acceso

La aplicación utiliza las rutas públicas definidas dentro de `public/`. La verificación de certificados se encuentra en:

```text
/verificar
```

## Seguridad

- No subir `.env` al repositorio.
- No subir volcados de base de datos con información real.
- Mantener protegidas las contraseñas SMTP y de la base de datos.
- Revisar los permisos de los directorios de almacenamiento.

## Licencia

Proyecto privado de gestión de certificados.
