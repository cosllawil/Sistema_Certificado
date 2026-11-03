<?php $cfg = json_decode((string)($template['configuracion_json'] ?? '{}'), true) ?: [];
$pdfUrl = 'plantillas?pdf_template=' . (int)$template['id']; ?>
<section class="form-page template-config-view">
  <div class="panel-heading">
    <div>
      <h1>Configurar plantilla</h1>
      <p class="intro">Define los campos y el estilo que se mostrarán en el certificado.</p>
    </div>
  </div>
  <div class="form-card template-config-card" id="template-config-modal" data-config="<?= htmlspecialchars(json_encode($cfg, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" data-pdf-url="<?= htmlspecialchars($pdfUrl, ENT_QUOTES, 'UTF-8') ?>">
    <form class="template-config-form form-grid" method="post" action="plantillas?config=<?= (int)$template['id'] ?>"><input type="hidden" name="_token" value="<?= htmlspecialchars((string)($_SESSION['template_csrf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="config_template_id" value="<?= (int)$template['id'] ?>"><input type="hidden" name="configuracion_json">
      <div class="field wide"><label>Campos visibles</label>
        <div class="template-fields"><?php foreach ([['participante', 'Participante'], ['registro_cas', 'Registro CAS'], ['curso', 'Curso'], ['tipo', 'Tipo de certificado'], ['fecha_sesion', 'Fecha de sesión'], ['fecha_inicio', 'Fecha de inicio'], ['fecha_fin', 'Fecha de fin'], ['fecha_sistema', 'Fecha del sistema'], ['horas', 'Horas académicas'], ['firmante', 'Firmante']] as [$key, $label]): ?><label><input type="checkbox" value="<?= $key ?>" <?= !empty($cfg['campos'][$key]) ? 'checked' : '' ?>> <?= $label ?></label><?php endforeach; ?></div>
      </div>
      <div class="template-preview-wrap"><label>Vista previa del PDF original y posición de campos</label>
        <div class="template-preview-canvas"><canvas class="template-preview-render" width="842" height="595"></canvas>
          <div class="template-preview-items"></div>
        </div>
      </div>
      <div class="field wide">
        <div class="template-field-properties">
          <div class="field"><label>Campo que deseas configurar</label><select id="template-style-target" aria-label="Seleccionar campo"></select></div>
          <div class="field"><label>Tipo de letra</label><select name="fuente">
              <?php foreach ($fonts as $font): ?><option value="<?= htmlspecialchars((string)$font, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$font, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select></div>
          <div class="field"><label>Tamaño</label><input name="tamano" type="number" value="<?= (int)($cfg['tamano'] ?? 12) ?>" min="6" max="72"></div>
          <div class="field"><label>Color</label><input name="color" type="color" value="<?= htmlspecialchars((string)($cfg['color'] ?? '#000000'), ENT_QUOTES, 'UTF-8') ?>"></div>
          <div class="field"><label>Estilo de letra</label><select id="template-font-style">
              <?php foreach ($fontStyles as $fontStyle): ?><option value="<?= htmlspecialchars((string)$fontStyle['codigo'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$fontStyle['nombre'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select></div>
          <div class="field"><label>Transformación del texto</label><select id="template-text-case">
              <?php foreach ($textTransforms as $transform): ?><option value="<?= htmlspecialchars((string)$transform['codigo'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string)$transform['nombre'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select></div>
        </div>
      </div>
      <div class="form-actions wide"><button type="button" class="course-cancel" onclick="location.href='plantillas'">Cancelar</button><button type="submit">Guardar configuración</button></div>
    </form>
  </div>
</section>
<script type="module" src="assets/js/template-configure-preview.js?v=1"></script>