(() => {
  const init = () => {
  const course = document.querySelector('select[name="curso_id"]');
  const type = document.querySelector('select[name="tipo_certificado_id"]');
  const template = document.querySelector('select[name="plantilla_id"]');
  const form = course?.form;
  if (!course || !type || !template) return;

  const sync = () => {
    const option = course.options[course.selectedIndex];
    type.value = option?.getAttribute('data-tipo-certificado') || '';
    template.value = option?.getAttribute('data-plantilla') || '';
    type.disabled = !type.value;
    template.disabled = !template.value;
    type.dispatchEvent(new Event('change', { bubbles: true }));
    template.dispatchEvent(new Event('change', { bubbles: true }));
  };
  course.addEventListener('change', sync);
  sync();
  form.addEventListener('submit', () => {
    type.disabled = false;
    template.disabled = false;
  });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
