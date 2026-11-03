document.addEventListener("DOMContentLoaded", () => {
  const input = document.querySelector("#audit-search");
  const rows = [...document.querySelectorAll(".audit-row")];
  const empty = document.querySelector("#audit-no-results");
  input?.addEventListener("input", () => {
    const term = input.value.trim().toLocaleLowerCase("es");
    let visible = 0;
    rows.forEach((row) => { const match = row.textContent.toLocaleLowerCase("es").includes(term); row.hidden = !match; if (match) visible++; });
    if (empty) empty.hidden = visible !== 0 || rows.length === 0;
  });
});
