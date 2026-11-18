document.addEventListener("DOMContentLoaded", () => {
  document
    .querySelectorAll(
      ".form-card select, .participant-form select, .signer-form select.user-document-type, .signer-form select[name=curso_id], #template-modal select, #type-modal select, #course-modal select, #user-modal select.user-document-type, #certificate-course-filter",
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
      const isParticipantPicker = select.name === "participante_id";
      const selectedIds = new Set();
      const hiddenContainer = select.closest(".certificate-single-participant")?.querySelector("[data-participant-ids]");
      const normalize = (value) =>
        value
          .normalize("NFD")
          .replace(/[\u0300-\u036f]/g, "")
          .toLowerCase();
      const sync = () => {
        const bulk = isParticipantPicker;
        trigger.textContent = bulk ? selectedIds.size + " participante" + (selectedIds.size === 1 ? "" : "s") + " seleccionado" + (selectedIds.size === 1 ? "" : "s") : (select.selectedOptions[0]?.textContent || "");
        wrapper.classList.toggle("select2-multi", bulk);
        const count = select.closest(".certificate-single-participant")?.querySelector("[data-participant-count]");
        if (count) { count.hidden = !bulk; count.textContent = trigger.textContent; }
        const query = normalize(search.value.trim());
        list.replaceChildren();
        [...select.options].forEach((option) => {
          const item = document.createElement("button");
          item.type = "button";
          item.className = "select2-option";
          if (bulk && option.value) {
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.tabIndex = -1;
            checkbox.checked = selectedIds.has(option.value);
            item.append(checkbox, document.createTextNode(option.text));
          } else {
            item.textContent = option.text;
          }
          item.hidden = !normalize(option.text).includes(query);
          item.addEventListener("click", () => {
            if (bulk && option.value) {
              if (selectedIds.has(option.value)) selectedIds.delete(option.value);
              else selectedIds.add(option.value);
              if (hiddenContainer) {
                hiddenContainer.replaceChildren(...[...selectedIds].map((id) => {
                  const input = document.createElement("input");
                  input.type = "hidden";
                  input.name = "participant_ids[]";
                  input.value = id;
                  return input;
                }));
              }
              select.value = [...selectedIds][0] || "";
            } else {
              select.value = option.value;
              select.dispatchEvent(new Event("change", { bubbles: true }));
              dropdown.classList.remove("open");
            }
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
      trigger.addEventListener("click", () => {
        const willOpen = !dropdown.classList.contains("open");
        dropdown.classList.toggle("open", willOpen);
        if (willOpen) {
          const card = wrapper.closest(".participant-modal-card");
          const roomBelow = card ? card.getBoundingClientRect().bottom - wrapper.getBoundingClientRect().bottom : window.innerHeight - wrapper.getBoundingClientRect().bottom;
          dropdown.classList.toggle("drop-up", roomBelow < 285);
        }
      });
      search.addEventListener("input", sync);
      document.querySelectorAll('input[name="assignment_mode"]').forEach((input) => input.addEventListener("change", () => {
        if (input.value === "single" && input.checked) {
          selectedIds.clear();
          hiddenContainer?.replaceChildren();
        }
        sync();
      }));
      document.addEventListener("click", (event) => {
        if (!wrapper.contains(event.target)) dropdown.classList.remove("open");
      });
    });
});
