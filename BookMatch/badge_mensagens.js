// Atualiza o badge de mensagens não lidas no menu lateral (id="badgeMsgs").
// Busca o total real em api/conversas.php (soma de nao_lidas de cada conversa).
(function () {
  async function atualizarBadgeMensagens() {
    const badge = document.getElementById('badgeMsgs');
    if (!badge) return;

    let usuario = null;
    try { usuario = JSON.parse(sessionStorage.getItem('usuario')); } catch (e) {}
    if (!usuario || !usuario.codusuario) { badge.style.display = 'none'; return; }

    try {
      const resp = await fetch('api/conversas.php?usuario=' + usuario.codusuario);
      const dados = await resp.json();
      const total = (dados.conversas || [])
        .reduce((soma, c) => soma + (parseInt(c.nao_lidas) || 0), 0);

      if (total > 0) {
        badge.textContent = total > 99 ? '99+' : total;
        badge.style.display = '';
      } else {
        badge.style.display = 'none';
      }
    } catch (e) {
      badge.style.display = 'none';
    }
  }

  document.addEventListener('DOMContentLoaded', atualizarBadgeMensagens);
  // Reatualiza ao voltar para a aba
  window.addEventListener('focus', atualizarBadgeMensagens);
})();
