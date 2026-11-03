<!doctype html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="icon" type="image/png" href="assets/images/icono-birrete-diploma.png">
    <title>Verificación de certificados</title>
    <link rel="stylesheet" href="assets/css/verify-certificate.css?v=10">
    <link rel="stylesheet" href="assets/css/verify-icon.css?v=3">
    <link rel="stylesheet" href="assets/css/compact.css?v=15">
</head>

<body class="verification-page">
    <canvas id="space-background" aria-hidden="true"></canvas>
    <main class="verification-shell">
        <header class="verification-header">
            <h1><svg viewBox="0 0 24 24">
                    <circle cx="10.5" cy="10.5" r="6.5" />
                    <path d="m16 16 5 5" />
                </svg>Verificación de certificados</h1>
            <p>Consulta la autenticidad y validez de un certificado emitido por el Colegio de Abogados del Santa.</p>
        </header>
        <form class="verification-search" method="post" action="verificar">
            <div class="verification-input"><input name="codigo" value="<?= e($code) ?>" placeholder="Ingrese el código de verificación (Ej.: CERT-2026-ABC123)" autocomplete="off"></div><button type="submit"><svg viewBox="0 0 24 24">
                    <circle cx="10.5" cy="10.5" r="6.5" />
                    <path d="m16 16 5 5" />
                </svg>Verificar</button>
        </form><?php if ($searched && $certificate && $certificate['estado'] !== 'anulado'): ?><section class="verification-card valid">
                <div class="verification-card-title"><span class="status-icon">✓</span>
                    <div>
                        <h2>Certificado válido</h2>
                        <p>El certificado se encuentra registrado en el sistema.</p>
                    </div>
                </div>
                <div class="certificate-details">
                    <div class="certificate-preview"><span>Certificado emitido</span></div>
                    <dl>
                        <div>
                            <dt>Participante:</dt>
                            <dd><?= e($certificate['participante']) ?></dd>
                        </div>
                        <div>
                            <dt>Evento / Curso:</dt>
                            <dd><?= e($certificate['curso']) ?></dd>
                        </div>
                        <div>
                            <dt>Tipo:</dt>
                            <dd><?= e($certificate['tipo']) ?></dd>
                        </div>
                        <div>
                            <dt>Fecha de emisión:</dt>
                            <dd><?= e(date('d/m/Y', strtotime((string) $certificate['fecha_emision']))) ?></dd>
                        </div>
                        <div>
                            <dt>Código:</dt>
                            <dd><b class="code-badge"><?= e($certificate['codigo_verificacion']) ?></b></dd>
                        </div>
                    </dl>
                </div>
                <div class="verification-actions"><a href="certificado_pdf?pdf=<?= (int)$certificate['id'] ?>" target="_blank">Ver certificado</a><a href="certificado_pdf?pdf=<?= (int)$certificate['id'] ?>&download=1">Descargar PDF</a></div>
            </section><?php elseif ($searched): ?><section class="verification-card invalid">
                <div class="verification-card-title"><span class="status-icon">×</span>
                    <div>
                        <h2><?= $certificate ? 'Certificado anulado' : 'Certificado no encontrado' ?></h2>
                        <p><?= $certificate ? 'El certificado fue anulado y ya no es válido.' : 'El código ingresado no se encuentra registrado en el sistema.' ?></p>
                    </div>
                </div>
            </section><?php endif; ?><div class="verification-note"><b>ⓘ Importante:</b> El código se encuentra en la parte inferior del certificado emitido.</div>
    </main>
    <script src="assets/js/security.js" defer></script>
    <script src="assets/js/login.js?v=10" defer></script>
    <script src="assets/js/verify-certificate.js?v=14" defer></script>
    <script type="module" src="assets/js/verify-preview.mjs?v=1"></script>
</body>

</html>
