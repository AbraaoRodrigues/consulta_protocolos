document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const campos = form.querySelectorAll('input[type="text"]');

    campos.forEach(campo => {
      campo.setAttribute('autocomplete', 'off');
    });

    const table = document.querySelector('table');
    if (table) {
      const headers = table.querySelectorAll('th');
      headers.forEach((th, index) => {
        th.addEventListener('click', () => {
          const rows = Array.from(table.querySelectorAll('tr')).slice(1);
          const asc = th.classList.toggle('asc');
          rows.sort((a, b) => {
            const aText = a.children[index].innerText;
            const bText = b.children[index].innerText;
            return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
          });
          rows.forEach(row => table.appendChild(row));
        });
      });
    }
  });

  document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const campos = form.querySelectorAll('input[type="text"]');

    campos.forEach(campo => {
      campo.setAttribute('autocomplete', 'off');
    });

    const table = document.querySelector('table');
    if (table) {
      const headers = table.querySelectorAll('th');
      headers.forEach((th, index) => {
        th.addEventListener('click', () => {
          const rows = Array.from(table.querySelectorAll('tr')).slice(1);
          const asc = th.classList.toggle('asc');
          rows.sort((a, b) => {
            const aText = a.children[index].innerText;
            const bText = b.children[index].innerText;
            return asc ? aText.localeCompare(bText) : bText.localeCompare(aText);
          });
          rows.forEach(row => table.appendChild(row));
        });
      });
    }
  });