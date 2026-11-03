<section class="form-page report-page">
    <h1>Reportes</h1>
    <p class="intro">Consulta y filtra los certificados registrados en el sistema.</p>
    <form class="form-card report-filters" method="get" action="reportes">
        <div class="field"><label>Curso</label><select name="curso_id"><option value="0">Todos los cursos</option><?php foreach ($courses as $course): ?><option value="<?= (int)$course['id'] ?>" <?= $cursoId === (int)$course['id'] ? 'selected' : '' ?>><?= e($course['nombre']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Estado</label><select name="estado"><option value="">Todos los estados</option><?php foreach (['borrador'=>'Borrador','emitido'=>'Emitido','anulado'=>'Anulado'] as $key => $label): ?><option value="<?= $key ?>" <?= $estado === $key ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Desde</label><input type="date" name="desde" value="<?= e($desde) ?>"></div><div class="field"><label>Hasta</label><input type="date" name="hasta" value="<?= e($hasta) ?>"></div>
        <div class="form-actions wide"><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m21 21-4.3-4.3M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z"/></svg>Filtrar</button><a class="course-cancel" href="reportes"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7M3 4v6h6"/></svg>Limpiar</a><button type="button" onclick="window.print()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/></svg>Imprimir reporte</button></div>
    </form>
    <div class="panel report-table"><div class="panel-heading"><h2>Listado de certificados</h2><div class="table-search"><input type="search" class="data-search report-search" placeholder="Buscar certificado..."></div></div><div class="table-wrap"><table><thead><tr><th>N.º</th><th>Participante</th><th>Curso</th><th>Tipo</th><th>Fecha emisión</th><th>Horas</th><th>Estado</th></tr></thead><tbody><?php foreach ($certificates as $index => $certificate): ?><tr class="report-row"><td><?= $index + 1 ?></td><td><?= e($certificate['participante']) ?></td><td><?= e($certificate['curso']) ?></td><td><?= e($certificate['tipo']) ?></td><td><?= !empty($certificate['fecha_emision']) ? date('d/m/Y', strtotime((string)$certificate['fecha_emision'])) : '—' ?></td><td><?= (int)$certificate['horas_academicas'] ?></td><td><span class="badge <?= e($certificate['estado']) ?>"><?= e(ucfirst($certificate['estado'])) ?></span></td></tr><?php endforeach; ?><?php if (!$certificates): ?><tr><td colspan="7" class="empty">No se encontraron certificados.</td></tr><?php endif; ?></tbody></table></div><div class="report-pagination"><span class="report-count"></span><div><button type="button" class="report-prev" disabled>‹ Anterior</button><button type="button" class="report-page-number">1</button><button type="button" class="report-next" disabled>Siguiente ›</button></div></div></div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const button = document.querySelector('.report-filters button[onclick="window.print()"]');
  if (!button) return;
  button.removeAttribute('onclick');
  button.type = 'submit';
  button.name = 'export';
  button.value = 'excel';
  button.lastChild.textContent = 'Descargar Excel';
});
</script>
