// Tema do painel: a escolha salva ou, na primeira visita, a preferência do aparelho.
(function () {
    'use strict';

    let theme = null;
    try {
        theme = window.localStorage.getItem('dc-admin-theme');
    } catch (error) {
        // Navegador sem armazenamento: segue a preferência do aparelho.
    }
    if (theme !== 'light' && theme !== 'dark') {
        theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    document.documentElement.dataset.theme = theme;
})();
