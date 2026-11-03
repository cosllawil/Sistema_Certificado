<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Participantes</h1>
            <p class="intro">Consulta las personas registradas para recibir certificados.</p>
            <div class="participant-actions">
                <button type="button" class="participant-action participant-action-new"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo participante</button>
                <button type="button" class="participant-action participant-action-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar participante</button>
                <button type="button" class="participant-action participant-action-disable" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar participante</button>
            </div>
        </div>
    </div>
    <div class="panel recent">
        <div class="panel-heading">
            <h2>Listado de Participantes</h2>
            <div class="table-search"><input type="search" class="data-search" placeholder="Buscar participante..."></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Participante</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Registro CAS</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($participants as $participant): ?><tr class="participant-row" data-participant-id="<?= (int) $participant['id'] ?>" data-tipo-documento="<?= htmlspecialchars($participant['tipo_documento'], ENT_QUOTES, 'UTF-8') ?>" data-numero-documento="<?= htmlspecialchars($participant['numero_documento'], ENT_QUOTES, 'UTF-8') ?>" data-nombres="<?= htmlspecialchars($participant['nombres'], ENT_QUOTES, 'UTF-8') ?>" data-apellidos="<?= htmlspecialchars($participant['apellidos'], ENT_QUOTES, 'UTF-8') ?>" data-email="<?= htmlspecialchars((string)($participant['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-telefono="<?= htmlspecialchars((string)($participant['telefono'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-cas="<?= htmlspecialchars((string)($participant['nro_registro_cas'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($participant['tipo_documento'] . ' ' . $participant['numero_documento'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($participant['apellidos'] . ', ' . $participant['nombres'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($participant['email'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($participant['telefono'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($participant['nro_registro_cas'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $participant['deleted_at'] ? 'pendiente' : '' ?>"><?= $participant['deleted_at'] ? 'Desactivado' : 'Activado' ?></span></td>
                        </tr><?php endforeach;
                            if (!$participants): ?><tr>
                            <td colspan="6" class="empty">No hay participantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal" id="participant-modal" hidden>
    <div class="participant-modal-card" role="dialog" aria-modal="true" aria-labelledby="participant-modal-title">
        <div class="participant-modal-header">
            <h2 id="participant-modal-title"><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM4 20a8 8 0 0 1 16 0" />
                </svg>Nuevo participante</h2><button type="button" class="participant-modal-close" aria-label="Cerrar">&times;</button>
        </div>
        <form class="participant-form" method="post" action="participantes" novalidate autocomplete="off">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['participant_csrf'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="participant_id" value="">
            <input type="hidden" name="action" value="save">
            <div class="form-grid">
                <div class="field"><label>Tipo de documento </label><select name="tipo_documento">
                        <option value="">---Seleccione Tipo de documento---</option>
                        <option value="DNI">DNI</option>
                        <option value="CE">CE</option>
                        <option value="PASAPORTE">PASAPORTE</option>
                        <option value="OTRO">OTRO</option>
                    </select></div>
                <div class="field"><label>Número de documento </label><input name="numero_documento" disabled></div>
                <div class="field"><label>Nombres </label><input name="nombres"></div>
                <div class="field"><label>Apellidos </label><input name="apellidos"></div>
                <div class="field"><label>Correo</label><input name="email" type="text"></div>
                <div class="field"><label>Teléfono</label><input name="telefono" maxlength="9" inputmode="numeric"></div>
                <div class="field"><label>Registro CAS </label><input name="nro_registro_cas" inputmode="numeric"></div>
            </div>
            <div class="form-actions"><button type="button" class="participant-modal-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13H5z" />
                        <path d="M8 4v6h8V4M8 20v-6h8v6" />
                    </svg>Guardar participante</button></div>
        </form>
    </div>
</div>
<div class="participant-modal participant-confirm" id="participant-confirm" hidden>
    <div class="participant-modal-card participant-confirm-card">
        <h2>Desactivar participante</h2>
        <p>¿Deseas desactivar el participante seleccionado?</p>
        <div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>Cancelar</button><button type="button" class="participant-confirm-ok"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m5 12 4 4L19 6" />
                </svg>Desactivar</button></div>
    </div>
</div>