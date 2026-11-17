<section class="form-page">
  <h1>Configuración</h1>
  <p class="intro">Parámetros generales del sistema.</p>
  <div class="configuration-tabs" role="tablist" aria-label="Secciones de configuración">
    <button type="button" class="configuration-tab is-active" role="tab" aria-selected="true" aria-controls="configuration-general" data-configuration-tab="configuration-general"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM8 9h8M8 13h5M8 17h8"/></svg><span>Configuración general</span></button>
    <button type="button" class="configuration-tab" role="tab" aria-selected="false" aria-controls="configuration-branding" data-configuration-tab="configuration-branding"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 4 6 6-9 9-5 1 1-5 9-9Z"/><path d="m12 6 6 6"/></svg><span>Identidad visual</span></button>
    <button type="button" class="configuration-tab" role="tab" aria-selected="false" aria-controls="configuration-database" data-configuration-tab="configuration-database"><svg viewBox="0 0 24 24" aria-hidden="true"><ellipse cx="12" cy="5" rx="7" ry="3"/><path d="M5 5v7c0 1.7 3.1 3 7 3s7-1.3 7-3V5M5 12v7c0 1.7 3.1 3 7 3s7-1.3 7-3v-7"/></svg><span>Base de datos</span></button>
  </div>
  <section id="configuration-general" class="configuration-tab-panel is-active" role="tabpanel">
  <div class="participant-actions"><button type="button" class="participant-action participant-action-edit configuration-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 1-4-4Z" />
        <path d="m13.5 6.5 4 4" />
      </svg>Editar configuración</button></div>
  </section>
  <section id="configuration-database" class="configuration-tab-panel" role="tabpanel" hidden>
    <div class="database-backup-panel">
      <?php if (!empty($databaseNotice)): ?><div class="form-alert success-alert database-server-alert" role="alert"><div class="alert-card"><div class="alert-main"><div class="alert-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></div><div class="alert-content"><strong>Éxito</strong><span><?= e($databaseNotice) ?></span></div></div><div class="alert-actions"><button type="button" class="alert-confirm">✓&nbsp; Entendido</button></div></div></div><?php endif; ?>
      <?php if (!empty($databaseError)): ?><div class="form-alert"><div class="alert-card"><div class="alert-content"><strong>Error</strong><span><?= e($databaseError) ?></span></div></div></div><?php endif; ?>
      <div class="database-actions"><form method="post" action="configuracion" class="database-create-form"><input type="hidden" name="_token" value="<?= e((string)$_SESSION['configuration_csrf']) ?>"><input type="hidden" name="database_action" value="backup"><button type="submit" class="participant-action participant-action-new"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>Crear backup</button></form><button type="button" class="participant-action database-restore-trigger" disabled><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12a8 8 0 1 0 2.3-5.7"/><path d="M4 5v7h7"/></svg>Restaurar backup</button></div>
      <div class="panel database-backup-list-panel"><div class="panel-heading database-backup-heading"><h2><svg class="modal-title-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h10l4 4v13H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg>Listado de backups</h2><div class="table-search"><input type="search" class="data-search database-backup-search" placeholder="Buscar backup..."></div></div>
      <div class="table-wrap database-backup-table-wrap">
        <table>
          <thead><tr><th>Fecha</th><th>Nombre del archivo</th><th>Tamaño</th><th>Acción</th></tr></thead>
          <tbody>
          <?php if (!$databaseBackups): ?><tr class="database-empty-row"><td colspan="4"><span class="database-empty-message database-empty-initial"><span class="database-empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 3h10l4 4v14H5z"/><path d="M15 3v5h5M8 12h8M8 16h6"/></svg></span><span>Aún no hay backups registrados.</span></span><span class="database-empty-message database-empty-search" hidden><span class="database-empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></span><span>No se encontraron registros</span></span></td></tr><?php endif; ?>
          <?php foreach ($databaseBackups as $backup): ?><tr class="database-backup-row" data-backup-name="<?= e($backup['name']) ?>"><td><?= e($backup['created_at']) ?></td><td><?= e($backup['name']) ?></td><td><?= e(number_format($backup['size'] / 1048576, 2)) ?> MB</td><td><span class="database-restore-badge"><?= (int) $backup['restore_count'] ?> <?= (int) $backup['restore_count'] === 1 ? 'restauración' : 'restauraciones' ?></span></td></tr><?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="table-footer database-backup-footer" hidden></div>
      </div>
    </div>
  </section>
  <div class="participant-modal participant-confirm" id="database-restore-confirm" hidden><div class="participant-modal-card participant-confirm-card"><h2>Restaurar backup</h2><p>¿Deseas restaurar el backup seleccionado?</p><form method="post" action="configuracion"><input type="hidden" name="_token" value="<?= e((string)$_SESSION['configuration_csrf']) ?>"><input type="hidden" name="database_action" value="restore"><input type="hidden" name="backup_name"><div class="form-actions"><button type="button" class="participant-confirm-cancel"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>No, cancelar</button><button type="submit" class="participant-confirm-ok"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg>Sí, restaurar</button></div></form></div></div>
  <section id="configuration-branding" class="configuration-tab-panel" role="tabpanel" hidden>
  <div class="panel branding-panel">
    <?php if (!empty($brandingNotice)): ?><div class="server-success-alert" data-message="<?= e((string)$brandingNotice) ?>"></div><?php endif; ?>
    <div class="panel-heading"><h2>Identidad visual</h2></div>
    <form method="post" enctype="multipart/form-data" class="branding-form"><input type="hidden" name="_token" value="<?= e((string)($_SESSION['configuration_csrf'] ?? '')) ?>"><input type="hidden" name="branding_action" value="1">
        <?php foreach ([['branding_logo_principal','logo_principal','Logo institucional principal','367×193','logo-colegio-abogados.png'],['branding_logo_secundario','logo_secundario','Logo del dashboard / menú','270×81','logoblanco.png'],['branding_icono','icono','Logo de login / CertiForma','218×143','icono-birrete-diploma.png'],['branding_logo_movil','logo_movil','Logo del dashboard móvil','270×81','logoblanco-mobil.png']] as [$field,$key,$label,$size,$filename]): $imageSrc = 'assets/images/'.$filename; ?><label class="branding-field"><span><?= e($label) ?> <small>(<?= e($size) ?>)</small></span><img src="<?= e($imageSrc) ?>?v=<?= time() ?>" alt="<?= e($label) ?>"><input class="branding-file-input" type="file" name="<?= e($key) ?>" accept="image/png,image/jpeg,image/webp"><span class="branding-file-picker"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5M5 20h14"/></svg><strong>Subir imagen</strong><span class="branding-file-name">Ningún archivo seleccionado</span></span></label><?php endforeach; ?>
      <label class="field"><span>Nombre del sistema</span><input name="branding_nombre" value="<?= e($branding['branding.nombre'] ?? 'CertiForma') ?>" maxlength="80"></label>
      <label class="field"><span>Subtítulo</span><input name="branding_subtitulo" value="<?= e($branding['branding.subtitulo'] ?? 'Sistema de Gestión de Cursos y Certificados') ?>" maxlength="150"></label>
      <label class="field"><span>Nombre de la institución</span><input name="branding_institucion" value="<?= e($branding['branding.institucion'] ?? 'Colegio de Abogados del Santa') ?>" maxlength="120"></label>
      <label class="field"><span>Texto de verificación</span><input name="branding_verificacion" value="<?= e($branding['branding.verificacion'] ?? 'Consulta la autenticidad y validez de un certificado emitido por el Colegio de Abogados del Santa.') ?>" maxlength="220"></label>
      <button type="submit" class="branding-submit"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h11l3 3v13H5z"/><path d="M8 4v5h8V4M8 16h8"/></svg>Guardar identidad visual</button>
    </form>
  </div>
  </section>
  <div class="panel recent">
    <div class="table-wrap">
      <div class="panel-heading configuration-table-heading">
        <h2>Listado de configuración</h2>
        <div class="table-search"><input type="search" class="data-search" placeholder="Buscar configuración..."></div>
      </div>
      <table>
        <thead>
          <tr>
            <th>Clave</th>
            <th>Valor</th>
            <th>Tipo</th>
            <th>Actualizado</th>
          </tr>
        </thead>
        <tbody><?php foreach ($settings as $setting): ?><tr class="configuration-row" data-key="<?= e($setting['clave']) ?>" data-value="<?= e((string)$setting['valor']) ?>">
              <td><?= e($setting['clave']) ?></td>
              <td><?= e((string)$setting['valor']) ?></td>
              <td><?= e($setting['tipo']) ?></td>
              <td><?= e($setting['updated_at']) ?></td>
            </tr><?php endforeach; ?><tr class="database-search-empty-row" hidden><td colspan="4"><span class="database-empty-message"><span class="database-empty-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></span><span>No se encontraron registros</span></span></td></tr></tbody>
      </table>
    </div>
  </div>
</section>
<div class="participant-modal" id="configuration-modal" hidden>
  <div class="participant-modal-card">
    <div class="participant-modal-header">
      <h2>Editar configuración</h2><button type="button" class="participant-modal-close" data-configuration-close>&times;</button>
    </div>
    <form method="post" action="configuracion" class="form-grid" autocomplete="off"><input type="hidden" name="_token" value="<?= e((string)($_SESSION['configuration_csrf'] ?? '')) ?>"><input type="hidden" name="clave">
      <div class="field wide"><label>Clave</label><input name="clave_visible" readonly></div>
      <div class="field wide"><label>Valor</label><textarea name="valor"></textarea></div>
      <div class="form-actions wide"><button type="button" class="course-cancel" data-configuration-close><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M6 6l12 12M18 6 6 18" />
          </svg>Cancelar</button><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 4h11l3 3v13H5zM8 4v5h8V4M8 16h8" />
          </svg>Guardar cambios</button></div>
    </form>
  </div>
</div>
