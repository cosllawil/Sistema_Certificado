<section class="welcome">
    <div>
        <h1>&iexcl;Bienvenido, <?= e(explode(' ', $user)[0]) ?>!</h1>
        <p>Gestiona, emite y controla todos los certificados desde un solo lugar.</p>
    </div>
    <div class="date-card">&#128197; <span>Hoy<br><b><?= date('d/m/Y') ?></b></span></div>
</section>
<script type="application/json" id="dashboard-data">
    <?= json_encode(['monthly' => $summary['monthly'], 'statuses' => $summary['statuses'], 'trends' => $summary['trends'], 'notifications' => $summary['notifications'], 'permissions' => $permissions], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>
<section class="stats">
    <article class="stat blue"><span class="stat-icon">&#128196;</span>
        <div><small>Certificados emitidos</small><strong><?= e($summary['certificates']) ?></strong><em>Datos reales</em></div>
    </article>
    <article class="stat amber"><span class="stat-icon">&#128336;</span>
        <div><small>Pendientes</small><strong><?= e($summary['pending']) ?></strong><em>Por revisar</em></div>
    </article>
    <article class="stat green"><span class="stat-icon">&#128424;</span>
        <div><small>Impresos</small><strong><?= e($summary['printed']) ?></strong><em>Registros</em></div>
    </article>
    <article class="stat purple"><span class="stat-icon">&#128218;</span>
        <div><small>Plantillas activas</small><strong><?= e($summary['templates']) ?></strong><em>Disponibles</em></div>
    </article>
</section>
<section class="middle-grid">
    <article class="panel quick">
        <h2>&#9889; Acciones rápidas</h2>
        <p>Realiza las operaciones más comunes de forma rápida y sencilla.</p>
        <div class="quick-actions"><a class="primary" href="registrar_certificado">&#128196; &nbsp; Nuevo certificado <span>&#10132;</span></a><a href="certificados">&#128203; &nbsp; Ver certificados <span>&#10132;</span></a><a href="reportes">&#128202; &nbsp; Reportes <span>&#10132;</span></a><a href="plantillas">&#128218; &nbsp; Crear plantilla <span>&#10132;</span></a></div>
    </article>
    <article class="panel chart-panel">
        <h2>&#128202; &nbsp; Emisiones mensuales</h2>
        <div class="bars"><i style="height:35%"></i><i style="height:52%"></i><i style="height:44%"></i><i style="height:72%"></i><i style="height:58%"></i><i style="height:80%"></i><i style="height:63%"></i><i style="height:91%"></i><i style="height:68%"></i><i style="height:82%"></i><i style="height:74%"></i><i style="height:96%"></i></div>
        <div class="months"><span>Ene</span><span>Feb</span><span>Mar</span><span>Abr</span><span>May</span><span>Jun</span><span>Jul</span><span>Ago</span><span>Sep</span><span>Oct</span><span>Nov</span><span>Dic</span></div>
    </article>
    <article class="panel status-panel">
        <h2>&#9673; &nbsp; Estados de certificados</h2>
        <div class="status-content">
            <div class="donut"><strong><?= e($summary['certificates']) ?></strong><small>total</small></div>
            <ul>
                <li><i class="active-dot"></i>Activos <b>0</b></li>
                <li><i class="pending-dot"></i>Pendientes <b><?= e($summary['pending']) ?></b></li>
                <li><i class="printed-dot"></i>Impresos <b><?= e($summary['printed']) ?></b></li>
                <li><i class="cancel-dot"></i>Anulados <b>0</b></li>
            </ul>
        </div>
    </article>
</section>
<section class="panel recent">
    <div class="panel-heading">
        <h2>Certificados recientes</h2>
        <div class="table-search"><input type="search" class="data-search" placeholder="Buscar certificado..."></div><a href="#">Ver todos&nbsp; &rarr;</a>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>N.&deg;</th>
                    <th>Participante</th>
                    <th>Evento</th>
                    <th>Fecha de emisión</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody><?php foreach ($summary['recent'] as $index => $certificate): ?><tr class="dashboard-certificate-row" data-certificate-id="<?= (int)($certificate['id'] ?? 0) ?>">
                        <td><?= $index + 1 ?></td>
                        <td><?= e($certificate['participante'] ?? 'Sin participante') ?></td>
                        <td><?= e($certificate['curso'] ?? 'Sin curso') ?></td>
                        <td><?= !empty($certificate['fecha_emision']) ? date('d/m/Y', strtotime((string)$certificate['fecha_emision'])) : '—' ?></td>
                        <td><span class="badge <?= e($certificate['estado'] ?? '') ?>"><?= e(ucfirst((string) ($certificate['estado'] ?? 'pendiente'))) ?></span></td>
                        <td>
                            <div class="row-actions"><button type="button" aria-label="Ver">&#128065;</button><button type="button" aria-label="Editar">&#9998;</button><button type="button" aria-label="PDF">&#128196;</button><button type="button" aria-label="Más">&#8943;</button></div>
                        </td>
                    </tr><?php endforeach;
                        if (!$summary['recent']): ?><tr>
                        <td colspan="6" class="empty">A&uacute;n no hay certificados registrados.</td>
                    </tr><?php endif; ?></tbody>
        </table>
    </div>
</section>
<div class="pdf-viewer-modal" id="dashboard-pdf-modal" hidden>
    <div class="pdf-viewer-card">
        <div class="pdf-viewer-header">
            <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 3h9l3 3v15H6zM15 3v4h4M9 12h6M9 16h6" />
                </svg>Vista previa del certificado</h2><button type="button" class="pdf-viewer-close">&times;</button>
        </div>
        <iframe title="Vista previa del certificado"></iframe>
        <div class="modal-footer pdf-viewer-footer"><button type="button" class="participant-action modal-close-button pdf-viewer-close"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>Cerrar</button></div>
    </div>
</div>
