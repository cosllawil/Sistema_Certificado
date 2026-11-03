import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.min.mjs';

pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.worker.min.mjs';

const canvas = document.querySelector('.template-preview-render');
const frame = canvas?.closest('.template-preview-canvas');
const pdfUrl = document.querySelector('.template-config-card')?.dataset.pdfUrl;

if (canvas && frame && pdfUrl) {
  try {
    const pdf = await pdfjsLib.getDocument({ url: pdfUrl }).promise;
    const page = await pdf.getPage(1);
    const source = page.getViewport({ scale: 1 });
    frame.style.aspectRatio = `${source.width} / ${source.height}`;
    canvas.dataset.pdfWidth = String(source.width);
    canvas.dataset.pdfHeight = String(source.height);

    const renderPage = async () => {
      const cssWidth = frame.clientWidth;
      if (!cssWidth) return;
      const pixelRatio = Math.min(window.devicePixelRatio || 1, 3);
      const viewport = page.getViewport({ scale: cssWidth / source.width });
      const outputScale = pixelRatio;
      canvas.width = Math.ceil(viewport.width * outputScale);
      canvas.height = Math.ceil(viewport.height * outputScale);
      const context = canvas.getContext('2d', { alpha: false });
      const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;
      await page.render({ canvasContext: context, viewport, transform }).promise;
      window.dispatchEvent(new Event('template-pdf-rendered'));
    };

    await renderPage();
    let previousWidth = frame.clientWidth;
    new ResizeObserver(() => {
      const nextWidth = frame.clientWidth;
      if (Math.abs(nextWidth - previousWidth) < 2) return;
      previousWidth = nextWidth;
      renderPage().catch(console.error);
    }).observe(frame);
  } catch (error) {
    console.error('No se pudo mostrar el PDF original:', error);
    const warning = document.createElement('p');
    warning.className = 'template-pdf-warning';
    warning.textContent = 'No fue posible mostrar el PDF original. Comprueba el archivo de plantilla.';
    frame.appendChild(warning);
  }
}
