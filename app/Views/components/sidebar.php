<?php
$sidebarAuthorization = new AuthorizationService(databaseConnection());
$sidebarUserId = (int)($_SESSION['auth_user']['id'] ?? 0);
$can = static fn(string $permission): bool => $sidebarUserId > 0 && $sidebarAuthorization->allows($sidebarUserId, $permission);
?>
<aside class="sidebar">
    <div class="brand"><span class="brand-mark"></span><span><b>Sistema de<br>Certificados</b></span></div>
    <nav class="menu">
        <?php if ($can('dashboard.ver')): ?><a href="dashboard">&#8962; <span>Dashboard</span></a><?php endif; ?>
        <?php if ($can('participantes.ver')): ?><a href="participantes">&#128101; <span>Participantes</span></a><?php endif; ?>
        <?php if ($can('cursos.ver')): ?><a href="cursos">&#128218; <span>Cursos</span></a><?php endif; ?>
        <?php if ($can('certificados.ver')): ?><a href="tipos_certificado">&#128220; <span>Tipos de certificado</span></a><?php endif; ?>
        <?php if ($can('plantillas.ver')): ?><a href="plantillas">&#128218; <span>Plantillas</span></a><?php endif; ?>
        <?php if ($can('firmantes.administrar')): ?><a href="firmantes">&#128737; <span>Firmantes</span></a><?php endif; ?>
        <?php if ($can('certificados.crear')): ?><a href="registrar_certificado">&#128196; <span>Asignar Certificado</span></a><?php endif; ?>
        <?php if ($can('certificados.ver')): ?><a href="certificados">&#128196; <span>Certificados</span></a><?php endif; ?>
        <?php if ($can('reportes.ver')): ?><a href="reportes">&#128202; <span>Reportes</span></a><?php endif; ?>
        <?php if ($can('usuarios.administrar')): ?><a href="usuarios">&#128100; <span>Usuarios</span></a><?php endif; ?>
        <?php if ($can('configuracion.administrar')): ?><a href="configuracion">&#9881; <span>Configuración</span></a><?php endif; ?>
        <?php if ($can('roles.administrar')): ?><a href="roles">&#128221; <span>Roles y permisos</span></a><?php endif; ?>
        <?php if ($can('auditoria.ver')): ?><a href="auditoria">&#128337; <span>Auditoría</span></a><?php endif; ?>
    </nav>
    <div class="sidebar-profile"><span class="sidebar-profile-avatar">WC</span><span class="sidebar-profile-info"><b><?= e($displayName) ?></b><small><?= e($roleName) ?></small></span><span class="sidebar-profile-logout" aria-label="Cerrar sesión"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <path d="m16 17 5-5-5-5M21 12H9" />
            </svg></span></div>
</aside>