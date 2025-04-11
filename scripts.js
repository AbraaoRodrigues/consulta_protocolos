document.addEventListener('DOMContentLoaded', function () {
  const forms = document.querySelectorAll('form');

  forms.forEach(form => {
    form.addEventListener('submit', function () {
      const loader = document.getElementById('loader');
      if (loader) loader.style.display = 'block';
    });
  });

  // Força maiúsculas nos campos
  ['interessado', 'assunto'].forEach(id => {
    const campo = document.getElementById(id);
    if (campo) {
      campo.setAttribute('autocomplete', 'off');
      campo.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
      });
    }
  });

  // Ordenação por colunas
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

// Modal
function abrirModal(id) {
  const modal = document.getElementById('detalhesModal');
  const conteudo = document.getElementById('modalContent');
  conteudo.innerHTML = '<p>Carregando...</p>';
  modal.style.display = 'block';

  fetch('detalhes.php?id=' + id)
    .then(resp => resp.text())
    .then(html => conteudo.innerHTML = html)
    .catch(() => conteudo.innerHTML = '<p>Erro ao carregar detalhes.</p>');
}

function fecharModal() {
  document.getElementById('detalhesModal').style.display = 'none';
}
