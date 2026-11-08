document.addEventListener("DOMContentLoaded", () => {
  document
    .querySelectorAll(
      ".form-card select, .participant-form select, .signer-form select.user-document-type, #template-modal select, #type-modal select, #course-modal select, #user-modal select.user-document-type, #certificate-course-filter",
    )
    .forEach((select) => {
      if (select.closest(".select2-lite")) return;
      const wrapper = document.createElement("div");
      wrapper.className = "select2-lite";
      select.parentNode.insertBefore(wrapper, select);
      wrapper.appendChild(select);
      select.classList.add("select2-native");
      const trigger = document.createElement("button");
      trigger.type = "button";
      trigger.className = "select2-trigger";
      wrapper.appendChild(trigger);
      const dropdown = document.createElement("div");
      dropdown.className = "select2-dropdown";
      const search = document.createElement("input");
      search.type = "search";
      search.placeholder = "Buscar...";
      dropdown.appendChild(search);
      const list = document.createElement("div");
      list.className = "select2-options";
      dropdown.appendChild(list);
      wrapper.appendChild(dropdown);
      const normalize = (value) =>
        value
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .toLowerCase();
      const sync = () => {
        trigger.textContent = select.selectedOptions[0]?.textContent || "";
        const query = normalize(search.value.trim());
        list.replaceChildren();
        [...select.options].forEach((option) => {
          const item = document.createElement("button");
          item.type = "button";
          item.className = "select2-option";
          item.textContent = option.text;
          item.hidden = !normalize(option.text).includes(query);
          item.addEventListener("click", () => {
            select.value = option.value;
            select.dispatchEvent(new Event("change", { bubbles: true }));
            dropdown.classList.remove("open");
            sync();
          });
          list.appendChild(item);
        });
      };
      sync();
      new MutationObserver(sync).observe(select, {
        childList: true,
        subtree: true,
        characterData: true,
      });
      select.addEventListener("change", sync);
      trigger.addEventListener("click", () =>
        dropdown.classList.toggle("open"),
      );
      search.addEventListener("input", sync);
      document.addEventListener("click", (event) => {
        if (!wrapper.contains(event.target)) dropdown.classList.remove("open");
      });
    });
});
