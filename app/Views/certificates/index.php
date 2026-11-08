<?php $hasPendingCertificates = (bool)array_filter($certificates, static fn(array $c): bool => ($c['estado'] ?? '') === 'borrador'); ?>
<section class="form-page">
  <div class="panel-heading">
    <div>
      <h1>Certificados</h1>
      <p class="intro">Consulta los certificados registrados en el sistema.</p>
      <div class="participant-actions"><a class="participant-action participant-action-new" href="registrar_certificado"><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 5v14M5 12h14" />
          </svg>Nuevo certificado</a><button type="button" class="participant-action participant-action-edit certificate-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 0-4-4Z" />
            <path d="m13.5 6.5 4 4" />
          </svg>Editar certificado</button><button type="button" id="bulk-print-button" class="participant-action"><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 3h9l3 3v15H6zM9 13h6M9 17h6" />
          </svg>Imprimir seleccionados</button><button type="submit" id="bulk-emit-button" form="bulk-emit-form" class="participant-action" style="background:#16a34a" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="m5 12 4 4L19 6" />
          </svg>Emitir seleccionados</button></div>
    </div>
  </div>
  <div class="panel recent">
    <div class="panel-heading">
      <h2>Listado de certificados</h2>
      <div class="table-search"><select id="certificate-course-filter" class="data-search">
          <option value="">Todos los cursos</option><?php foreach (array_unique(array_column($certificates, 'curso')) as $course): ?><option value="<?= htmlspecialchars((string)$course, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$course, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
        </select><input type="search" class="data-search" placeholder="Buscar certificado..."></div>
    </div>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th><input type="checkbox" id="certificate-select-all"></th>
            <th>N°</th>
            <th>Participante</th>
            <th>Curso</th>
            <th>Fecha emisión</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody><?php foreach ($certificates as $index => $certificate): ?><tr class="certificate-row" data-certificate-id="<?= (int)$certificate['id'] ?>" data-state="<?= htmlspecialchars($certificate['estado'], ENT_QUOTES, 'UTF-8') ?>" data-course="<?= htmlspecialchars((string)$certificate['curso'], ENT_QUOTES, 'UTF-8') ?>">
              <td><input type="checkbox" name="ids[]" value="<?= (int)$certificate['id'] ?>" form="bulk-emit-form"></td>
              <td><?= $index + 1 ?></td>
              <td><?= htmlspecialchars($certificate['participante'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= htmlspecialchars($certificate['curso'], ENT_QUOTES, 'UTF-8') ?></td>
              <td><?= !empty($certificate['fecha_emision']) ? date('d/m/Y', strtotime((string)$certificate['fecha_emision'])) : '—' ?></td>
              <td><span class="badge <?= $certificate['estado'] === 'borrador' ? 'pendiente' : ($certificate['estado'] === 'emitido' ? 'activo' : 'anulado') ?>"><?= htmlspecialchars(ucfirst($certificate['estado']), ENT_QUOTES, 'UTF-8') ?></span></td>
            </tr><?php endforeach;
                if (!$certificates): ?><tr>
              <td colspan="6" class="empty">No hay certificados registrados.</td>
            </tr><?php endif; ?></tbody>
      </table>
    </div>
  </div>
</section>
<form id="bulk-emit-form" method="post" action="certificados"><input type="hidden" name="_token" value="<?= htmlspecialchars((string)($_SESSION['certificate_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="emitir" value="1"></form>
<?php $emittedIds = array_values(array_filter(array_map('intval', (array)($_SESSION['certificados_emitidos'] ?? []))));
unset($_SESSION['certificados_emitidos']);
if ($emittedIds): ?><div class="bulk-emit-modal" id="bulk-emit-modal">
    <div class="bulk-emit-card">
      <div class="participant-modal-header">
        <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 3h9l3 3v15H6zM15 3v5h5M9 13h6M9 17h6" />
          </svg>Certificados emitidos</h2><button type="button" class="participant-modal-close" data-close-bulk>&times;</button>
      </div><?php $batchPayload = 'batch|' . (time() + 300) . '|' . implode(',', array_map('intval', $emittedIds)); $batchSecret = (string)(getenv('APP_KEY') ?: 'Sistema_Certificado_pdf_token_v1'); $batchSignature = hash_hmac('sha256', $batchPayload, $batchSecret); $batchToken = rtrim(strtr(base64_encode($batchPayload . '|' . $batchSignature), '+/', '-_'), '='); ?><iframe class="bulk-emit-pdf" src="certificado_pdf?token=<?= urlencode($batchToken) ?>" title="Certificados emitidos"></iframe>
      <div class="modal-footer"><button type="button" class="participant-modal-cancel modal-close-button" data-close-bulk><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 6l12 12M18 6 6 18" />
          </svg>Cerrar</button></div>
    </div>
  </div><?php endif; ?>
<div class="pdf-viewer-modal" id="certificate-pdf-modal" hidden>
  <div class="pdf-viewer-card">
    <div class="pdf-viewer-header">
      <h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true">
          <path d="M6 3h9l3 3v15H6zM15 3v5h5M9 13h6M9 17h6" />
        </svg>Vista previa del certificado</h2><button type="button" class="pdf-viewer-close">&times;</button>
    </div><iframe title="Vista previa del certificado"></iframe>
    <div class="modal-footer pdf-viewer-footer"><button type="button" class="participant-modal-cancel modal-close-button pdf-viewer-close"><svg viewBox="0 0 24 24" aria-hidden="true">
          <path d="M6 6l12 12M18 6 6 18" />
        </svg>Cerrar</button></div>
  </div>
</div>
