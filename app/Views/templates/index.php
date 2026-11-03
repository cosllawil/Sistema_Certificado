<?php if (!empty($templateError)): ?><div class="server-form-alert" data-message="<?= htmlspecialchars((string)$templateError, ENT_QUOTES, 'UTF-8') ?>"></div><?php endif; ?>
<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Plantillas</h1>
            <p class="intro">Administra las plantillas disponibles para emitir certificados.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nueva plantilla</button><button type="button" class="participant-action participant-action-edit template-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar plantilla</button><button type="button" class="participant-action template-config" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 5h16v14H4z" />
                        <path d="M8 9h8M8 13h5" />
                    </svg>Configurar plantilla</button><button type="button" class="participant-action participant-action-disable template-disable" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar plantilla</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="panel-heading">
            <h2>Listado de plantillas</h2>
            <div class="table-search"><input type="search" class="data-search" placeholder="Buscar plantilla..."></div>
        </div>
        <div class="table-wrap">
            <table class="template-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Formato</th>
                        <th>Orientación</th>
                        <th>Versión</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($templates as $template): ?><tr class="template-row" data-template-id="<?= (int)$template['id'] ?>" data-active="<?= (int)$template['activo'] ?>" data-name="<?= htmlspecialchars((string)$template['nombre'], ENT_QUOTES, 'UTF-8') ?>" data-description="<?= htmlspecialchars((string)($template['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-format="<?= htmlspecialchars((string)$template['formato'], ENT_QUOTES, 'UTF-8') ?>" data-orientation="<?= htmlspecialchars((string)$template['orientacion'], ENT_QUOTES, 'UTF-8') ?>" data-fondo="<?= htmlspecialchars((string)($template['fondo_path'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-config="<?= htmlspecialchars((string)($template['configuracion_json'] ?? '{}'), ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($template['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)($template['descripcion'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($template['formato'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($template['orientacion'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$template['version'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge <?= $template['activo'] ? 'activo' : 'pendiente' ?>"><?= $template['activo'] ? 'Activado' : 'Desactivado' ?></span></td>
                        </tr><?php endforeach;
                            if (!$templates): ?><tr>
                            <td colspan="6" class="empty">No hay plantillas registradas.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal participant-confirm" id="template-confirm" hidden>
    <div class="participant-modal-card participant-confirm-card">
        <h2>Desactivar plantilla</h2>
        <p>¿Deseas desactivar la plantilla seleccionada?</p>
        <form method="post" action="plantillas"><input type="hidden" name="template_id"><input type="hidden" name="activo">
            <div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit" class="participant-confirm-ok"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar</button></div>
        </form>
    </div>
</div>
<div class="participant-modal course-modal" id="template-config-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2>Configurar plantilla</h2><button type="button" class="participant-modal-close">&times;</button>
        </div>
        <form class="template-config-form" method="post" action="plantillas"><input type="hidden" name="_token" value="<?= htmlspecialchars((string)($_SESSION['template_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="config_template_id"><input type="hidden" name="configuracion_json">
            <div class="form-grid">
                <div class="field wide"><label>Campos visibles</label>
                    <div class="template-fields"><label><input type="checkbox" value="participante"> Participante</label><label><input type="checkbox" value="registro_cas"> Registro CAS</label><label><input type="checkbox" value="curso"> Curso</label><label><input type="checkbox" value="tipo"> Tipo de certificado</label><label><input type="checkbox" value="fecha_sesion"> Fecha de sesión</label><label><input type="checkbox" value="fecha_inicio"> Fecha de inicio</label><label><input type="checkbox" value="fecha_fin"> Fecha de fin</label><label><input type="checkbox" value="horas"> Horas académicas</label><label><input type="checkbox" value="firmante"> Firmante</label></div>
                </div>
                <div class="field"><label>Tipo de letra</label><select name="fuente">
                        <?php foreach ($fonts as $font): ?><option value="<?= htmlspecialchars((string)$font, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$font, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                    </select></div>
                <div class="field"><label>Tamaño</label><input name="tamano" type="number" value="12" min="6" max="72"></div>
                <div class="field"><label>Color</label><input name="color" type="color" value="#000000"></div>
            </div>
            <div class="form-actions"><button type="button" class="course-cancel">Cancelar</button><button type="submit">Guardar configuración</button></div>
        </form>
    </div>
</div>
<div class="participant-modal course-modal" id="template-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="m6 16 4-4 3 3 2-2 3 3M8 8h.01" />
                </svg>Nueva plantilla</h2><button type="button" class="participant-modal-close">&times;</button>
        </div>
        <form method="post" action="plantillas" enctype="multipart/form-data" autocomplete="off" novalidate><input type="hidden" name="template_id">
            <div class="form-grid">
                <div class="field"><label>Horas académicas</label><input name="horas_academicas" type="text" inputmode="decimal" autocomplete="off"></div>
                <div class="field"><label>Fecha de sesión</label><input name="fecha_sesion" type="date"></div>
                <div class="field"><label>Fecha de inicio</label><input name="fecha_inicio" type="date"></div>
                <div class="field"><label>Fecha de fin</label><input name="fecha_fin" type="date"></div>
                <div class="field"><label>Nombre </label><input name="nombre" required></div>
                <div class="field"><label>Formato</label><input name="formato" value="A4"></div>
                <div class="field"><label>Orientación</label><select name="orientacion">
                        <option value="horizontal">Horizontal</option>
                        <option value="vertical">Vertical</option>
                    </select></div>
                <div class="field wide"><label>Plantilla PDF</label><label class="file-upload"><input type="file" name="fondo" accept="application/pdf"><span class="file-upload-button"><svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M12 16V4M7 9l5-5 5 5M5 20h14" />
                            </svg>Subir PDF</span><span class="file-upload-name">Ningún archivo seleccionado</span></label></div>
                <div class="field wide"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            </div>
            <div class="form-actions"><button type="button" class="course-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13H5z" />
                        <path d="M8 4v6h8V4M8 20v-6h8v6" />
                    </svg>Guardar plantilla</button></div>
        </form>
    </div>
</div>
