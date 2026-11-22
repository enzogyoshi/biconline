async function carregarPublicacoesPerfil() {
  const container = document.getElementById('publicacoes-container');
  const urlParams = new URLSearchParams(window.location.search);
  const idUsuario = urlParams.get('pesquisa');

  if (!idUsuario) {
    container.innerHTML = '<p>Selecione um usuário para visualizar as publicações.</p>';
    return;
  }

  try {
    const response = await fetch(`api_publicacoes.php?pesquisa=${encodeURIComponent(idUsuario)}`);
    
    if (!response.ok) {
      throw new Error('Não foi possível carregar as publicações.');
    }

    const publicacoes = await response.json();
    container.innerHTML = '';

    if (publicacoes.length === 0) {
      container.innerHTML = '<p>Não há publicações para exibir.</p>';
      return;
    }

    publicacoes.forEach(pub => {
      const card = document.createElement('div');
      card.className = 'publicacao';

      const dataFormatada = new Date(pub.data_pub).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });

      card.innerHTML = `
        <p>${escaparHtml(pub.conteudo)}</p>
        ${pub.url_midia ? `<img src="${escaparHtml(pub.url_midia)}" alt="Mídia">` : ''}
        ${pub.link ? `<a href="${escaparHtml(pub.link)}" target="_blank" rel="noopener">Link</a>` : ''}
        <p><small>Publicado em: ${dataFormatada}</small></p>
      `;

      container.appendChild(card);
    });

  } catch (error) {
    console.error(error);
    container.innerHTML = `<p class="erro">${error.message}</p>`;
  }
}

function escaparHtml(texto) {
  if (!texto) return '';
  const div = document.createElement('div');
  div.textContent = texto;
  return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', carregarPublicacoesPerfil);