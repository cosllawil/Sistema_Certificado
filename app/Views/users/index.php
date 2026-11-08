<?php $csrf = e((string)($_SESSION['user_csrf'] ?? '')); ?>
<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Usuarios</h1>
            <p class="intro">Administra las cuentas de acceso al sistema.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new" data-user-new><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo usuario</button><button type="button" class="participant-action participant-action-edit user-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar usuario</button><button type="button" class="participant-action participant-action-disable user-status" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar usuario</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="table-wrap">
            <div class="panel-heading users-table-heading">
                <h2>Listado de usuarios</h2>
                <div class="table-search"><input type="search" class="data-search" placeholder="Buscar usuario..."></div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th>Último acceso</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($users as $user): ?><tr class="user-row" data-user-id="<?= (int)$user['id'] ?>" data-status="<?= e($user['estado']) ?>" data-nombres="<?= e($user['nombres']) ?>" data-apellidos="<?= e($user['apellidos']) ?>" data-email="<?= e($user['email']) ?>" data-username="<?= e($user['username']) ?>" data-role-id="<?= (int)($user['rol_id'] ?? 0) ?>">
                            <td><?= e($user['nombres'] . ' ' . $user['apellidos']) ?></td>
                            <td><?= e($user['username']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td><span class="badge <?= $user['estado'] === 'activo' ? 'activo' : 'pendiente' ?>"><?= e(ucfirst($user['estado'])) ?></span></td>
                            <td><?= e((string)$user['ultimo_acceso_at']) ?></td>
                        </tr><?php endforeach;
                            if (!$users): ?><tr>
                            <td colspan="5" class="empty">No hay usuarios registrados.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal" id="user-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2 id="user-modal-title"><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0" />
                </svg>Nuevo usuario</h2><button type="button" class="participant-modal-close" data-user-close>×</button>
        </div>
        <form method="post" action="usuarios" class="form-grid" autocomplete="off"><input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="user_id">
            <div class="field"><label>Tipo de documento</label><select class="user-document-type" name="tipo_documento"><option value="">---Seleccione Tipo de documento---</option><option value="DNI">DNI</option><option value="CE">CE</option><option value="PASAPORTE">PASAPORTE</option><option value="OTRO">OTRO</option></select></div>
            <div class="field"><label>Número de documento</label><input name="numero_documento" inputmode="numeric" autocomplete="off" disabled></div>
            <div class="field"><label>Nombres</label><input name="nombres" autocomplete="off" required></div>
            <div class="field"><label>Apellidos</label><input name="apellidos" autocomplete="off" required></div>
            <div class="field"><label>Correo</label><input name="email" type="email" autocomplete="off" required></div>
            <div class="field"><label>Usuario</label><input name="username" autocomplete="off" required></div>
            <div class="field"><label>Rol</label><select name="rol_id" required><option value="">Seleccionar rol</option><?php foreach ($roles as $role): ?><option value="<?= (int)$role['id'] ?>"><?= e($role['nombre']) ?></option><?php endforeach; ?></select></div>
            <div class="field"><label>Contraseña </label><input name="password" type="password" autocomplete="new-password"></div>
            <div class="form-actions wide"><button type="button" class="course-cancel" data-user-close><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13H5zM8 4v5h8V4M8 16h8" />
                    </svg>Guardar usuario</button></div>
        </form>
    </div>
</div>
<form method="post" action="usuarios" id="user-status-form"><input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="user_id"><input type="hidden" name="action"></form>
