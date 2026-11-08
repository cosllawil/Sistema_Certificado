<?php
declare(strict_types=1);
$token = (string)($_GET['token'] ?? '');
?><!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Certificado</title>
<style>html,body{margin:0;background:#eef2f7;font-family:Arial,sans-serif}#pdf{display:block;margin:16px auto;max-width:calc(100% - 24px);height:auto;background:#fff;box-shadow:0 2px 12px #0002}#msg{text-align:center;margin:35vh 16px;color:#52677d}</style></head>
<body><p id="msg">Cargando certificado...</p><canvas id="pdf" hidden></canvas>
<script type="module">
import * as pdfjsLib from './assets/js/pdfjs/pdf.min.mjs';
pdfjsLib.GlobalWorkerOptions.workerSrc=new URL('./assets/js/pdfjs/pdf.worker.min.mjs', document.baseURI).href;
const token=<?= json_encode($token, JSON_UNESCAPED_SLASHES) ?>;
try { const url='certificado_pdf?token='+encodeURIComponent(token); const response=await fetch(url,{credentials:'same-origin'}); if(!response.ok) throw new Error('Respuesta del servidor: '+response.status); const bytes=await response.arrayBuffer(); const pdf=await pdfjsLib.getDocument({data:bytes}).promise; const page=await pdf.getPage(1); const canvas=document.querySelector('#pdf'); const viewport=page.getViewport({scale:1}); const scale=Math.min(1.5,(innerWidth-24)/viewport.width); const view=page.getViewport({scale}); canvas.width=view.width; canvas.height=view.height; canvas.hidden=false; document.querySelector('#msg').remove(); await page.render({canvasContext:canvas.getContext('2d'),viewport:view}).promise; } catch(e) { document.querySelector('#msg').textContent='No se pudo cargar el certificado: '+(e?.message||e); }
</script></body></html>
