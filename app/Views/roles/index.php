<?php $csrf = e((string)($_SESSION['role_csrf'] ?? '')); ?>
<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Roles y permisos</h1>
            <p class="intro">Consulta los roles y permisos asignados en el sistema.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new" data-role-new><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo rol</button><button type="button" class="participant-action participant-action-edit role-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar rol</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Permisos</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($roles as $role): ?><tr class="role-row" data-role-id="<?= (int)$role['id'] ?>" data-codigo="<?= e($role['codigo']) ?>" data-nombre="<?= e($role['nombre']) ?>" data-descripcion="<?= e((string)$role['descripcion']) ?>" data-permissions="<?= e(json_encode($role['permisos_ids'], JSON_THROW_ON_ERROR)) ?>">
                            <td><?= e($role['nombre']) ?></td>
                            <td><?= e($role['codigo']) ?></td>
                            <td><?= e((string)($role['descripcion'] ?? '—')) ?></td>
                            <td><?= (int)$role['permisos'] ?></td>
                        </tr><?php endforeach;
                            if (!$roles): ?><tr>
                            <td colspan="4" class="empty">No hay roles registrados.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal" id="role-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2 id="role-modal-title">♢ Nuevo rol</h2><button type="button" class="participant-modal-close" data-role-close>×</button>
        </div>
        <form method="post" action="roles" class="form-grid" autocomplete="off"><input type="hidden" name="_token" value="<?= $csrf ?>"><input type="hidden" name="role_id">
            <div class="field"><label>Nombre</label><input name="nombre"></div>
            <div class="field"><label>Código</label><input name="codigo"></div>
            <div class="field wide"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            <fieldset class="role-permissions wide">
                <legend>Permisos</legend>
                <div class="permissions-grid"><?php foreach ($permissions as $permission): ?><label class="permission-item"><input type="checkbox" name="permisos[]" value="<?= (int)$permission['id'] ?>"><span><strong><?= e(ucfirst($permission['modulo'])) ?></strong> — <?= e($permission['nombre']) ?></span></label><?php endforeach; ?></div>
            </fieldset>
            <div class="form-actions wide"><button type="button" class="course-cancel" data-role-close>✕ Cancelar</button><button type="submit">▣ Guardar rol</button></div>
        </form>
    </div>
</div>