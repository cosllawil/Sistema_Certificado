<section class="form-page">
    <div class="panel-heading">
        <div>
            <h1><svg class="page-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 3a9 9 0 1 0 9 9" />
                    <path d="M12 7v5l3 2M16 3h5v5" />
                </svg>Auditoría</h1>
            <p class="intro">Últimas acciones registradas en el sistema.</p>
        </div>
    </div>
    <div class="panel recent audit-panel">
        <div class="panel-heading audit-panel-heading">
            <h2>Registro de auditoría</h2>
            <div class="audit-toolbar"><label class="audit-search"><svg viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="11" cy="11" r="6" />
                        <path d="m16 16 5 5" />
                    </svg><input type="search" id="audit-search" placeholder="Buscar en auditoría..."></label></div>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Acción</th>
                        <th>Módulo</th>
                        <th>Registro</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody id="audit-body"><?php foreach ($entries as $entry): ?><tr class="audit-row">
                            <td><?= e((string)($entry['usuario'] ?? 'Sistema')) ?></td>
                            <td><?= e((string)$entry['accion']) ?></td>
                            <td><?= e((string)$entry['entidad']) ?></td>
                            <td><?= $entry['entidad_id'] ? (int)$entry['entidad_id'] : '—' ?></td>
                            <td><?= e(date('d/m/Y H:i', strtotime((string)$entry['created_at']))) ?></td>
                        </tr><?php endforeach;
                                        if (!$entries): ?><tr class="audit-empty">
                            <td colspan="5" class="empty">No hay acciones registradas.</td>
                        </tr><?php endif; ?></tbody>
            </table>
        </div>
        <div class="audit-no-results" id="audit-no-results" hidden>No se encontraron coincidencias.</div>
    </div>
</section>