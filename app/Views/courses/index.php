<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1>Cursos</h1>
            <p class="intro">Consulta los cursos y eventos disponibles para emitir certificados.</p>
            <div class="participant-actions"><button type="button" class="participant-action participant-action-new" onclick="document.getElementById('course-modal').removeAttribute('hidden')"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" />
                    </svg>Nuevo curso</button><button type="button" class="participant-action participant-action-edit course-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>Editar curso</button><button type="button" class="participant-action participant-action-disable course-disable" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                    </svg>Desactivar curso</button></div>
        </div>
    </div>
    <div class="panel recent">
        <div class="panel-heading">
            <h2>Listado de Cursos</h2>
            <div class="table-search"><input type="search" class="data-search" placeholder="Buscar curso..."></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Horas</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody><?php foreach ($courses as $course): ?><tr class="course-row" data-course-id="<?= (int)$course['id'] ?>" data-codigo="<?= htmlspecialchars($course['codigo'], ENT_QUOTES, 'UTF-8') ?>" data-nombre="<?= htmlspecialchars($course['nombre'], ENT_QUOTES, 'UTF-8') ?>" data-horas="<?= htmlspecialchars((string)$course['horas_academicas'], ENT_QUOTES, 'UTF-8') ?>" data-inicio="<?= htmlspecialchars((string)($course['fecha_inicio'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-fin="<?= htmlspecialchars((string)($course['fecha_fin'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" data-descripcion="<?= htmlspecialchars((string)($course['descripcion'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <td><?= htmlspecialchars($course['codigo'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($course['nombre'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int)$course['horas_academicas'] ?></td>
                            <td><?= !empty($course['fecha_inicio']) ? date('d/m/Y', strtotime((string)$course['fecha_inicio'])) : '—' ?></td>
                            <td><?= !empty($course['fecha_fin']) ? date('d/m/Y', strtotime((string)$course['fecha_fin'])) : '—' ?></td>
                            <td><span class="badge <?= $course['estado'] === 'inactivo' || $course['deleted_at'] ? 'pendiente' : 'activo' ?>"><?= $course['estado'] === 'inactivo' || $course['deleted_at'] ? 'Desactivado' : 'Activado' ?></span></td>
                        </tr><?php endforeach;
                            if (!$courses): ?><tr>
                            <td colspan="6" class="empty">No hay cursos registrados.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
    </div>
</section>
<div class="participant-modal course-modal" id="course-modal" hidden>
    <div class="participant-modal-card">
        <div class="participant-modal-header">
            <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 5h16v14H4z" />
                    <path d="M8 9h8M8 13h6" />
                </svg>Nuevo curso</h2><button type="button" class="participant-modal-close" onclick="document.getElementById('course-modal').setAttribute('hidden','')">&times;</button>
        </div>
        <form class="course-form" method="post" action="cursos" autocomplete="off" novalidate><input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['course_csrf'], ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="course_id" value=""><input type="hidden" name="action" value="save">
            <div class="form-grid">
                <div class="field"><label>Código </label><input name="codigo" value="<?= e($nextCourseCode ?? '1') ?>" readonly required></div>
                <div class="field"><label>Nombre </label><input name="nombre" required></div>
                <div class="field"><label>Horas académicas</label><input name="horas_academicas" type="text" inputmode="numeric" placeholder="0"></div>
                <div class="field"><label>Fecha de inicio </label><input name="fecha_inicio" type="date"></div>
                <div class="field"><label>Fecha de fin </label><input name="fecha_fin" type="date"></div>
                <div class="field wide"><label>Descripción</label><textarea name="descripcion"></textarea></div>
            </div>
            <div class="form-actions"><button type="button" class="course-cancel" onclick="document.getElementById('course-modal').setAttribute('hidden','')"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 4h11l3 3v13H5z" />
                        <path d="M8 4v6h8V4M8 20v-6h8v6" />
                    </svg>Guardar curso</button></div>
        </form>
    </div>
</div>
<div class="participant-modal participant-confirm" id="participant-confirm" hidden>
    <div class="participant-modal-card participant-confirm-card">
        <h2>Desactivar curso</h2>
        <p>¿Deseas desactivar el curso seleccionado?</p>
        <div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>Cancelar</button><button type="button" class="participant-confirm-ok"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 7h12M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5M14 11v5" />
                </svg>Desactivar</button></div>
    </div>
</div>
