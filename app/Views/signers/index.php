<?php if (!empty($signerError)): ?><div class="server-form-alert" data-message="<?= htmlspecialchars((string)$signerError, ENT_QUOTES, 'UTF-8') ?>"></div><?php endif; ?>
<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Firmantes</h1>
            <p class="intro">Consulta las personas autorizadas para firmar certificados.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new signer-new"><svg viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo firmante</button><button type="button" class="participant-action participant-action-edit signer-edit" disabled><svg viewBox="0 0 24 24">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar firmante</button><button type="button" class="participant-action participant-action-disable signer-disable" disabled><svg viewBox="0 0 24 24">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar firmante</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="panel-heading">
            <h2>Listado de firmantes</h2>
            <div class="table-search"><input type="search" class="data-search" placeholder="Buscar firmante..."></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Firma</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($signers as $signer): ?><tr class="signer-row" data-signer-id="<?= (int)$signer['id'] ?>" data-active="<?= $signer['estado'] === 'activo' ? '1' : '0' ?>" data-nombres="<?= htmlspecialchars($signer['nombres'], ENT_QUOTES, 'UTF-8') ?>" data-apellidos="<?= htmlspecialchars($signer['apellidos'], ENT_QUOTES, 'UTF-8') ?>" data-cargo="<?= htmlspecialchars($signer['cargo'], ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($signer['nombres'] . ' ' . $signer['apellidos'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($signer['cargo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $signer['firma_path'] ? 'Registrada' : 'No registrada' ?></td>
                            <td><span class="badge <?= $signer['estado'] === 'activo' ? 'activo' : 'pendiente' ?>"><?= $signer['estado'] === 'activo' ? 'Activado' : 'Desactivado' ?></span></td>
                        </tr><?php endforeach;
                            if (!$signers): ?><tr>
                            <td colspan="4" class="empty">No hay firmantes registrados.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal course-modal" id="signer-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2><svg class="modal-title-icon" viewBox="0 0 24 24">
                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0" />
                </svg>Nuevo firmante</h2><button type="button" class="participant-modal-close">&times;</button>
        </div>
        <form class="signer-form" method="post" action="firmantes" enctype="multipart/form-data" autocomplete="off" novalidate><input type="hidden" name="signer_id">
            <div class="form-grid">
                <div class="field"><label>Tipo de documento</label><select class="user-document-type" name="tipo_documento"><option value="">---Seleccione Tipo de documento---</option><option value="DNI">DNI</option><option value="CE">CE</option><option value="PASAPORTE">PASAPORTE</option><option value="OTRO">OTRO</option></select></div>
                <div class="field"><label>Número de documento</label><input name="numero_documento" inputmode="numeric" autocomplete="off" disabled></div>
                <div class="field"><label>Nombres </label><input name="nombres" required></div>
                <div class="field"><label>Apellidos </label><input name="apellidos" required></div>
                <div class="field"><label>Cargo </label><input name="cargo" required></div>
                <div class="field"><label>Firma</label><label class="file-upload"><input type="file" name="firma" accept="image/png,image/jpeg,image/svg+xml"><span class="file-upload-button"><svg viewBox="0 0 24 24">
                                <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                            </svg>Subir firma</span><span class="file-upload-name">Ningún archivo seleccionado</span></label></div>
            </div>
            <div class="form-actions"><button type="button" class="course-cancel"><svg viewBox="0 0 24 24">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24">
                        <path d="M5 4h11l3 3v13H5z" />
                        <path d="M8 4v6h8V4M8 20v-6h8v6" />
                    </svg>Guardar firmante</button></div>
        </form>
    </div>
</div>
<div class="participant-modal participant-confirm" id="signer-confirm" hidden>
    <div class="participant-modal-card participant-confirm-card">
        <h2>Desactivar firmante</h2>
        <p>¿Deseas desactivar el firmante seleccionado?</p>
        <form method="post" action="firmantes"><input type="hidden" name="signer_id"><input type="hidden" name="estado">
            <div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit" class="participant-confirm-ok"><svg viewBox="0 0 24 24">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar</button></div>
        </form>
    </div>
</div>
