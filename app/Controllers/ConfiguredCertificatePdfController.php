<?php

declare(strict_types=1);

final class ConfiguredCertificatePdfController
{
    private function recordPdfAction(PDO $db, int $certificateId, int $userId, string $type): void
    {
        $q = $db->prepare('INSERT INTO impresiones_certificado (certificado_id, usuario_id, tipo, cantidad) VALUES (:certificado_id, :usuario_id, :tipo, 1)');
        $q->execute(['certificado_id' => $certificateId, 'usuario_id' => $userId ?: null, 'tipo' => $type]);
    }

    private function spanishDate(?string $date): string
    {
        if (!$date) return '';
        $time = strtotime($date);
        if (!$time) return $date;
        $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        return (int)date('j', $time) . ' de ' . $months[(int)date('n', $time) - 1] . ' de ' . date('Y', $time);
    }
    private function renderCertificatePage(\setasign\Fpdi\Fpdi $pdf, array $c, string $root): void
    {
        $source = $root . '/' . ltrim((string)$c['fondo_path'], '/');
        if (!is_file($source)) return;
        $cfg = json_decode((string)($c['plantilla_configuracion_json'] ?? '{}'), true);
        $cfg = is_array($cfg) ? $cfg : [];
        $fields = is_array($cfg['campos'] ?? null) ? $cfg['campos'] : [];
        $v = ['participante'=>(string)$c['participante'],'registro_cas'=>(string)($c['registro_cas'] ?? ''),'curso'=>(string)$c['curso'],'tipo'=>(string)$c['tipo'],'fecha_sesion'=>$this->spanishDate($c['fecha_sesion'] ?? null),'fecha_inicio'=>$this->spanishDate($c['fecha_inicio'] ?? null),'fecha_fin'=>$this->spanishDate($c['fecha_fin'] ?? null),'fecha_sistema'=>$this->spanishDate(date('Y-m-d')),'horas'=>(string)($c['horas_academicas'] ?? ''),'codigo_verificacion'=>(string)($c['codigo_verificacion'] ?? ''),'firmante'=>''];
        $templateData = is_array($cfg['datos'] ?? null) ? $cfg['datos'] : [];
        foreach (['horas','fecha_sesion','fecha_inicio','fecha_fin'] as $dataKey) if (array_key_exists($dataKey, $templateData)) $v[$dataKey] = $dataKey === 'horas' ? (string)$templateData[$dataKey] : $this->spanishDate((string)$templateData[$dataKey]);
        $pdf->setSourceFile($source); $tpl = $pdf->importPage(1); $s = $pdf->getTemplateSize($tpl); $pdf->AddPage($s['orientation'], [$s['width'], $s['height']]); $pdf->SetAutoPageBreak(false); $pdf->useTemplate($tpl);
        foreach ($fields as $key => $f) {
            if (!is_array($f) || empty($f['visible']) || !array_key_exists($key, $v)) continue;
            $text = trim($v[$key]); if ($text === '') continue;
            if ($key === 'fecha_sistema') { $text = 'Chimbote, ' . $text; $text = preg_replace_callback('/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\b/u', static fn(array $m): string => ucfirst($m[1]), $text); }
            $x=(float)($f['x']??35); $y=(float)($f['y']??100); $legacy=empty($cfg['coordinate_system'])&&$x<=100&&$y<=100; $cw=(float)($cfg['canvas_width']??842); $ch=(float)($cfg['canvas_height']??595); if($legacy){$x=$x*$s['width']/100;$y=$y*$s['height']/100;}else{$x=$x*$s['width']/$cw;$y=$y*$s['height']/$ch;}
            $family=(string)($f['fuente']??$cfg['fuente']??'Helvetica'); $size=max(6,min(72,(float)($f['tamano']??$cfg['tamano']??12))); $style=in_array(($f['estilo']??''),['','B','I','BI'],true)?($f['estilo']??''):''; CertificateFontService::apply($pdf,$family,$style,$size); $color=ltrim((string)($f['color']??$cfg['color']??'#000000'),'#'); if(preg_match('/^[0-9a-fA-F]{6}$/',$color))$pdf->SetTextColor(hexdec(substr($color,0,2)),hexdec(substr($color,2,2)),hexdec(substr($color,4,2)));
            $tr=(string)($f['transformacion']??'none'); if($tr==='uppercase')$text=mb_strtoupper($text,'UTF-8'); elseif($tr==='lowercase')$text=mb_strtolower($text,'UTF-8'); elseif($tr==='title')$text=mb_convert_case(mb_strtolower($text,'UTF-8'),MB_CASE_TITLE,'UTF-8'); $encoded=iconv('UTF-8','windows-1252//TRANSLIT',$text); if($encoded===false)$encoded=$text; $line=max(3.5,$size*.35278*1.20);
            if($key==='participante'){ $cx=$s['width']*.55; $aw=$s['width']*.68; while($pdf->GetStringWidth($encoded)>$aw-2&&$size>6){$size=max(6,$size-.5); CertificateFontService::apply($pdf,$family,$style,$size);} $line=max(3.5,$size*.35278*1.20); $pdf->SetXY($cx-$aw/2,$y-$line/2); $pdf->Cell($aw,$line,$encoded,0,0,'C'); } else { $w=$pdf->GetStringWidth($encoded)+1; $pdf->SetXY($x-$w/2,$y-$line/2); $pdf->Cell($w,$line,$encoded,0,0,'L'); }
        }
    }
    public function output(): void
    {

        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $publicVerification = isset($_GET['verificar'], $_GET['codigo']) && (string)$_GET['verificar'] === '1';
        if (!isset($_SESSION['auth_user']) && !$publicVerification) {
            http_response_code(401);
            exit;
        }
        $db = databaseConnection();
        if ($publicVerification) {
            $check = $db->prepare('SELECT 1 FROM certificados WHERE id=:id AND codigo_verificacion=:codigo AND estado="emitido" AND deleted_at IS NULL LIMIT 1');
            $check->execute(['id'=>(int)($_GET['pdf'] ?? 0), 'codigo'=>strtoupper(trim((string)$_GET['codigo']))]);
            if (!$check->fetchColumn()) { http_response_code(403); exit('Certificado no válido.'); }
        }
        if (!$publicVerification) (new AuthorizationService($db))->require((int)$_SESSION['auth_user']['id'], 'certificados.imprimir');
        if (!empty($_GET['batch'])) {
            $ids = array_values(array_filter(array_map('intval', explode(',', (string)$_GET['batch']))));
            if (!$ids) { http_response_code(400); exit('Lote vacío'); }
            require_once dirname(__DIR__, 2) . '/vendor/autoload.php'; require_once dirname(__DIR__) . '/Services/CertificateFontService.php';
            $marks = implode(',', array_fill(0, count($ids), '?'));
            $q = $db->prepare("SELECT c.*,CONCAT(p.nombres,' ',p.apellidos) participante,p.nro_registro_cas registro_cas,cu.nombre curso,t.nombre tipo,pl.fondo_path,pl.configuracion_json AS plantilla_configuracion_json FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id INNER JOIN tipos_certificado t ON t.id=c.tipo_certificado_id LEFT JOIN plantillas pl ON pl.id=c.plantilla_id WHERE c.id IN ($marks) AND c.deleted_at IS NULL ORDER BY FIELD(c.id,$marks)");
            $q->execute(array_merge($ids, $ids)); $certificates = $q->fetchAll(); $pdf = new \setasign\Fpdi\Fpdi(); $root = dirname(__DIR__, 2); $action = !empty($_GET['download']) ? 'descarga_pdf' : 'generacion_pdf'; $viewerId = $publicVerification ? 0 : (int)$_SESSION['auth_user']['id']; foreach ($certificates as $certificate) { $this->recordPdfAction($db, (int)$certificate['id'], $viewerId, $action); $this->renderCertificatePage($pdf, $certificate, $root); }
            header('Content-Type:application/pdf'); header('Content-Disposition:' . ($action === 'descarga_pdf' ? 'attachment' : 'inline') . '; filename="certificados_emitidos.pdf"'); $pdf->Output($action === 'descarga_pdf' ? 'D' : 'I','certificados_emitidos.pdf'); exit;
        }
        $id = (int)($_GET['pdf'] ?? 0);
        $q = $db->prepare('SELECT c.*,CONCAT(p.nombres," ",p.apellidos) participante,p.nro_registro_cas registro_cas,cu.nombre curso,t.nombre tipo,pl.fondo_path,pl.configuracion_json AS plantilla_configuracion_json FROM certificados c INNER JOIN participantes p ON p.id=c.participante_id INNER JOIN cursos cu ON cu.id=c.curso_id INNER JOIN tipos_certificado t ON t.id=c.tipo_certificado_id LEFT JOIN plantillas pl ON pl.id=c.plantilla_id WHERE c.id=:id AND c.deleted_at IS NULL');
        $q->execute(['id' => $id]);
        $c = $q->fetch();
        if (!$c) {
            http_response_code(404);
            exit('Certificado no encontrado');
        }
        $action = !empty($_GET['download']) ? 'descarga_pdf' : 'generacion_pdf';
        $this->recordPdfAction($db, (int)$c['id'], $publicVerification ? 0 : (int)$_SESSION['auth_user']['id'], $action);
        $root = dirname(__DIR__, 2);
        $source = $root . '/' . ltrim((string)$c['fondo_path'], '/');
        if (!is_file($source)) {
            http_response_code(404);
            exit('Plantilla no encontrada');
        }
        require_once $root . '/vendor/autoload.php';
        require_once $root . '/app/Services/CertificateFontService.php';
        $cfg = json_decode((string)($c['plantilla_configuracion_json'] ?? '{}'), true);
        $cfg = is_array($cfg) ? $cfg : [];
        $fields = is_array($cfg['campos'] ?? null) ? $cfg['campos'] : [];
        $v = ['participante' => (string)$c['participante'], 'registro_cas' => (string)($c['registro_cas'] ?? ''), 'curso' => (string)$c['curso'], 'tipo' => (string)$c['tipo'], 'fecha_sesion' => $this->spanishDate($c['fecha_sesion'] ?? null), 'fecha_inicio' => $this->spanishDate($c['fecha_inicio'] ?? null), 'fecha_fin' => $this->spanishDate($c['fecha_fin'] ?? null), 'fecha_sistema' => $this->spanishDate(date('Y-m-d')), 'horas' => (string)($c['horas_academicas'] ?? ''), 'codigo_verificacion' => (string)($c['codigo_verificacion'] ?? ''), 'firmante' => ''];
        $templateData = is_array($cfg['datos'] ?? null) ? $cfg['datos'] : [];
        foreach (['horas', 'fecha_sesion', 'fecha_inicio', 'fecha_fin'] as $dataKey) {
            if (array_key_exists($dataKey, $templateData)) $v[$dataKey] = $dataKey === 'horas' ? (string)$templateData[$dataKey] : $this->spanishDate((string)$templateData[$dataKey]);
        }
        $pdf = new \setasign\Fpdi\Fpdi();
        $pdf->setSourceFile($source);
        $tpl = $pdf->importPage(1);
        $s = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($s['orientation'], [$s['width'], $s['height']]);
        $pdf->SetAutoPageBreak(false);
        $pdf->useTemplate($tpl);
        $font = in_array(($cfg['fuente'] ?? 'Helvetica'), ['Helvetica', 'Courier', 'Times'], true) ? $cfg['fuente'] : 'Helvetica';
        $pdf->SetFont($font, '', max(6, min(72, (float)($cfg['tamano'] ?? 12))));
        $hex = ltrim((string)($cfg['color'] ?? '#000000'), '#');
        if (preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            $pdf->SetTextColor(hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        }
        foreach ($fields as $key => $f) {
            if (!is_array($f) || empty($f['visible']) || !array_key_exists($key, $v)) continue;
            $text = trim($v[$key]);
            if ($text === '') continue;
            if ($key === 'fecha_sistema') {
                $text = 'Chimbote, ' . $text;
                $text = preg_replace_callback('/\b(enero|febrero|marzo|abril|mayo|junio|julio|agosto|septiembre|octubre|noviembre|diciembre)\b/u', static fn(array $match): string => ucfirst($match[1]), $text);
            }
            $x = (float)($f['x'] ?? 35);
            $y = (float)($f['y'] ?? 100);
            $legacyPercent = empty($cfg['coordinate_system']) && $x <= 100 && $y <= 100;
            $canvasWidth = (float)($cfg['canvas_width'] ?? 842);
            $canvasHeight = (float)($cfg['canvas_height'] ?? 595);
            if ($legacyPercent) {
                $x = $x * $s['width'] / 100;
                $y = $y * $s['height'] / 100;
            } else {
                $x = $x * $s['width'] / $canvasWidth;
                $y = $y * $s['height'] / $canvasHeight;
            }
            $fieldFont = (string)($f['fuente'] ?? $cfg['fuente'] ?? 'Helvetica');
            $fieldSize = max(6, min(72, (float)($f['tamano'] ?? $cfg['tamano'] ?? 12)));
            $fieldStyle = in_array(($f['estilo'] ?? ''), ['', 'B', 'I', 'BI'], true) ? ($f['estilo'] ?? '') : '';
            CertificateFontService::apply($pdf, $fieldFont, $fieldStyle, $fieldSize);
            $fieldColor = ltrim((string)($f['color'] ?? $cfg['color'] ?? '#000000'), '#');
            if (preg_match('/^[0-9a-fA-F]{6}$/', $fieldColor)) {
                $pdf->SetTextColor(hexdec(substr($fieldColor, 0, 2)), hexdec(substr($fieldColor, 2, 2)), hexdec(substr($fieldColor, 4, 2)));
            }
            $transformacion = (string)($f['transformacion'] ?? 'none');
            if ($transformacion === 'uppercase') $text = mb_strtoupper($text, 'UTF-8');
            elseif ($transformacion === 'lowercase') $text = mb_strtolower($text, 'UTF-8');
            elseif ($transformacion === 'title') $text = mb_convert_case(mb_strtolower($text, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $encoded = iconv('UTF-8', 'windows-1252//TRANSLIT', $text);
            if ($encoded === false) $encoded = $text;
            $fontSize = $fieldSize;
            $lineHeight = max(3.5, $fontSize * 0.35278 * 1.20);
            if ($key === 'participante') {
                $centerX = $s['width'] * 0.55;
                $areaWidth = $s['width'] * 0.68;
                $available = $areaWidth - 2.0;
                while ($pdf->GetStringWidth($encoded) > $available && $fontSize > 6.0) {
                    $fontSize = max(6.0, $fontSize - 0.5);
                    CertificateFontService::apply($pdf, $fieldFont, $fieldStyle, $fontSize);
                }
                $lineHeight = max(3.5, $fontSize * 0.35278 * 1.20);
                $pdf->SetXY($centerX - $areaWidth / 2, $y - $lineHeight / 2);
                $pdf->Cell($areaWidth, $lineHeight, $encoded, 0, 0, 'C');
            } else {
                $cellWidth = $pdf->GetStringWidth($encoded) + 1;
                $pdf->SetXY($x - $cellWidth / 2, $y - $lineHeight / 2);
                $pdf->Cell($cellWidth, $lineHeight, $encoded, 0, 0, 'L');
            }
        }
        header('Cache-Control:no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma:no-cache');
        header('Content-Type:application/pdf');
        header('Content-Disposition:' . ($action === 'descarga_pdf' ? 'attachment' : 'inline') . '; filename="certificado_' . $id . '.pdf"');
        $pdf->Output($action === 'descarga_pdf' ? 'D' : 'I', 'certificado_' . $id . '.pdf');
        exit;
    }
}
