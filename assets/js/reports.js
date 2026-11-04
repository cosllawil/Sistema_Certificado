document.addEventListener('DOMContentLoaded', function () {
  const button = document.querySelector('.report-filters button[onclick="window.print()"]');
  if (!button) return;
  button.removeAttribute('onclick');
  button.type = 'submit';
  button.name = 'export';
  button.value = 'excel';
  button.lastChild.textContent = 'Descargar Excel';
});
