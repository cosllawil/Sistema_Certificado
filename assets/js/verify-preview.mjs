import * as pdfjsLib from "./pdfjs/pdf.min.mjs";

pdfjsLib.GlobalWorkerOptions.workerSrc = "./pdf.worker.min.mjs";

const preview = document.querySelector(".certificate-preview");
const button = document.querySelector(".verification-actions a:first-child");

if (preview && button) {
  button.addEventListener("click", async (event) => {
    event.preventDefault();
    const code =
      document.querySelector(".code-badge")?.textContent.trim() || "";
    preview.replaceChildren();
    preview.classList.add("is-loading");
    preview.insertAdjacentHTML(
      "afterbegin",
      "<span>Cargando certificado...</span>",
    );

    try {
      const url = `${button.href}&verificar=1&codigo=${encodeURIComponent(code)}`;
      const response = await fetch(url, { credentials: "same-origin" });
      if (!response.ok)
        throw new Error(`No se pudo obtener el PDF (${response.status})`);

      const pdf = await pdfjsLib.getDocument({
        data: await response.arrayBuffer(),
        disableWorker: true,
      }).promise;
      const page = await pdf.getPage(1);
      const source = page.getViewport({ scale: 1 });
      const cssWidth = preview.clientWidth;
      const cssHeight = preview.clientHeight;
      const scale = Math.min(
        cssWidth / source.width,
        cssHeight / source.height,
      );
      const viewport = page.getViewport({ scale });
      const ratio = Math.min(window.devicePixelRatio || 1, 2);
      const canvas = document.createElement("canvas");
      canvas.width = Math.ceil(viewport.width * ratio);
      canvas.height = Math.ceil(viewport.height * ratio);
      canvas.style.cssText =
        "display:block;width:100%;height:auto;border-radius:7px;background:#fff";
      const context = canvas.getContext("2d", { alpha: false });
      preview.replaceChildren(canvas);
      preview.classList.remove("is-loading");
      await page.render({
        canvasContext: context,
        viewport,
        transform: [ratio, 0, 0, ratio, 0, 0],
      }).promise;
    } catch (error) {
      console.error("Vista previa del certificado:", error);
      preview.classList.remove("is-loading");
      preview.textContent = "No se pudo cargar la vista previa.";
    }
  });
}
