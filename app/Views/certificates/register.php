<section class="form-page">
    <h1>Asignar certificado</h1>
    <p class="intro"><?= $certificateEdit ? 'Edita el certificado en estado borrador.' : 'Crea un certificado en estado borrador para revisarlo y emitirlo posteriormente.' ?></p><?php if (isset($_GET['ok'])): ?><div class="server-success-alert" data-message="Certificado registrado correctamente."></div><?php endif; ?><form class="form-card" method="post" action="registrar_certificado" novalidate><input type="hidden" name="_token" value="<?= e($_SESSION['certificate_csrf']) ?>"><input type="hidden" name="certificate_id" value="<?= (int)($certificateEdit['id'] ?? 0) ?>">
        <div class="form-grid">
            <div class="field"><label>Participante </label><select name="participante_id" required>
                    <option value="">Seleccionar participante</option><?php foreach ($participants as $r): ?><option value="<?= $r['id'] ?>" <?= ((int)($certificateEdit['participante_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>><?= e($r['apellidos'] . ', ' . $r['nombres'] . ' - ' . $r['numero_documento']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="field"><label>Curso </label><select name="curso_id" required>
                    <option value="">Seleccionar curso</option><?php foreach ($courses as $r): ?><option value="<?= $r['id'] ?>" data-tipo-certificado="<?= (int)($r['tipo_certificado_id'] ?? 0) ?>" data-plantilla="<?= (int)($r['plantilla_id'] ?? 0) ?>" <?= ((int)($certificateEdit['curso_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>><?= e($r['codigo'] . ' - ' . $r['nombre']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="field"><label>Tipo de certificado (automático)</label><select name="tipo_certificado_id" required disabled>
                    <option value="">Seleccionar tipo</option><?php foreach ($types as $r): ?><option value="<?= $r['id'] ?>" <?= ((int)($certificateEdit['tipo_certificado_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="field"><label>Plantilla (automática)</label><select name="plantilla_id" disabled>
                    <option value="">Sin plantilla</option><?php foreach ($templates as $r): ?><option value="<?= $r['id'] ?>" <?= ((int)($certificateEdit['plantilla_id'] ?? 0) === (int)$r['id']) ? 'selected' : '' ?>><?= e($r['nombre']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="field wide"><label>Observaciones</label><textarea name="observaciones" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()" onchange="this.value=this.value.toUpperCase()" autocomplete="off"><?= e((string)($certificateEdit['observaciones'] ?? '')) ?></textarea></div>
        </div>
        <div class="form-actions"><a class="cancel-action" href="dashboard"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>Cancelar</a><button type="submit"><svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 4h11l3 3v13H5z" />
                    <path d="M8 4v6h8V4M8 20v-6h8v6" />
                </svg>Guardar borrador</button></div>
    </form>
</section>
<script src="assets/js/certificate-register.js?v=4" defer></script>
