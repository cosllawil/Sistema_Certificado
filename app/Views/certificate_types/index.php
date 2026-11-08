<?php if (!empty($typeError)): ?><div class="server-form-alert" data-message="<?= htmlspecialchars((string) $typeError, ENT_QUOTES, 'UTF-8') ?>"></div><?php endif; ?>
<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Tipos de certificado</h1>
            <p class="intro">Consulta los tipos disponibles para emitir certificados.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo tipo de certificado</button><button type="button" class="participant-action participant-action-edit type-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar tipo de certificado</button><button type="button" class="participant-action participant-action-disable type-disable" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar tipo de certificado</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="panel-heading">
            <h2>Listado de tipos de certificado</h2>
            <div class="table-search"><input type="search" class="data-search" placeholder="Buscar tipo..."></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($types as $type): ?><tr class="type-row" data-type-id="<?= (int)$type['id'] ?>" data-active="<?= (int)$type['activo'] ?>" data-code="<?= htmlspecialchars((string)$type['codigo'], ENT_QUOTES, 'UTF-8') ?>" data-name="<?= htmlspecialchars((string)$type['nombre'], ENT_QUOTES, 'UTF-8') ?>" data-description="<?= htmlspecialchars((string)($type['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-plantilla="<?= (int)($type['plantilla_id'] ?? 0) ?>">
                            <td><?= htmlspecialchars((string)$type['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($type['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($type['descripcion'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $type['activo'] ? 'activo' : 'pendiente' ?>"><?= $type['activo'] ? 'Activado' : 'Desactivado' ?></span></td>
                        </tr><?php endforeach;
                            if (!$types): ?><tr>
                            <td colspan="4" class="empty">No hay tipos registrados.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal course-modal" id="type-modal" data-next-code="<?= e($nextTypeCode ?? 'TC-001') ?>" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="12" cy="9" r="5" />
                    <path d="m9 13-1 7 4-2 4 2-1-7M10 9l2-2 2 2-2 2z" />
                </svg>Nuevo tipo de certificado</h2><button type="button" class="participant-modal-close" onclick="document.getElementById('type-modal').setAttribute('hidden','')">&times;</button>
        </div>
        <form class="type-form" method="post" action="tipos_certificado" autocomplete="off" novalidate><input type="hidden" name="type_id">
            <div class="form-grid">
                <div class="field"><label>Código </label><input name="codigo" value="<?= e($nextTypeCode ?? 'TC-001') ?>" readonly required></div>
                <div class="field"><label>Nombre del certificado</label><input name="nombre" required></div>
                <div class="field wide"><label>Plantilla asociada</label><select name="plantilla_id"><option value="">Seleccionar plantilla</option><?php foreach (($templates ?? []) as $template): ?><option value="<?= (int)$template['id'] ?>"><?= e($template['nombre']) ?></option><?php endforeach; ?></select></div>
                <div class="field wide"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            </div>
            <div class="form-actions"><button type="button" class="course-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13H5z" />
                        <path d="M8 4v6h8V4M8 20v-6h8v6" />
                    </svg>Guardar tipo</button></div>
        </form>
    </div>
</div>
<script>
document.addEventListener('click', function (event) {
    if (!event.target.closest('.type-edit')) return;
    const row = document.querySelector('.type-row.selected');
    const select = document.querySelector('#type-modal select[name="plantilla_id"]');
    if (!row || !select) return;
    select.value = row.dataset.plantilla || '';
    select.dispatchEvent(new Event('change', { bubbles: true }));
    const trigger = select.closest('.select2-lite')?.querySelector('.select2-trigger');
    if (trigger) trigger.textContent = select.selectedOptions[0]?.textContent || 'Seleccionar plantilla';
});
</script>
<div class="participant-modal participant-confirm" id="type-confirm" hidden>
    <div class="participant-modal-card participant-confirm-card">
        <h2>Desactivar tipo de certificado</h2>
        <p>¿Deseas desactivar el tipo seleccionado?</p>
        <form method="post" action="tipos_certificado"><input type="hidden" name="type_id"><input type="hidden" name="activo">
            <div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit" class="participant-confirm-ok"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar</button></div>
        </form>
    </div>
</div>
