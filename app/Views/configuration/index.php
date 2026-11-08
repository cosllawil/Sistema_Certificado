<section class="form-page">
  <h1>Configuración</h1>
  <p class="intro">Parámetros generales del sistema.</p>
  <div class="participant-actions"><button type="button" class="participant-action participant-action-edit configuration-edit" disabled><svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="m4 16-.8 4.8L8 20l11-11a2.8 2.8 0 0 1-4-4Z" />
        <path d="m13.5 6.5 4 4" />
      </svg>Editar configuración</button></div>
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
            </tr><?php endforeach; ?></tbody>
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
