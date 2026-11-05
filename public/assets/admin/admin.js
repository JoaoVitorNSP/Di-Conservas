// Painel: tema, ações sem recarregar, seleção em massa, filtro da lista e enquadramento da foto.
(function () {
    'use strict';

    const $ = (id) => document.getElementById(id);

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    // ---------- Tema claro e escuro ----------
    const themeButton = $('themeToggle');
    if (themeButton) {
        const showTheme = () => {
            const next = document.documentElement.dataset.theme === 'dark' ? 'Ativar modo claro' : 'Ativar modo escuro';
            themeButton.title = next;
            $('themeLabel').textContent = next;
        };
        showTheme();
        themeButton.addEventListener('click', () => {
            const theme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = theme;
            try {
                window.localStorage.setItem('dc-admin-theme', theme);
            } catch (error) {
                // Sem armazenamento: o tema vale só até sair da página.
            }
            showTheme();
        });
    }

    // ---------- Avisos e envio sem recarregar ----------
    const toast = $('toast');
    let toastTimer = 0;
    function showToast(message, type) {
        window.clearTimeout(toastTimer);
        toast.textContent = message;
        toast.classList.toggle('toast--error', type === 'error');
        toast.hidden = false;
        toastTimer = window.setTimeout(() => { toast.hidden = true; }, type === 'error' ? 7000 : 4000);
    }

    // Retorna a resposta do servidor ou null, depois de avisar o que deu errado.
    async function send(url, data) {
        let response;
        try {
            response = await fetch(url, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
        } catch (error) {
            showToast('Sem conexão com o servidor. Tente de novo.', 'error');
            return null;
        }
        const isJson = (response.headers.get('content-type') || '').includes('application/json');
        if (!isJson) {
            if (response.redirected) {
                window.location.assign('/admin'); // a sessão expirou
            } else {
                showToast(response.status === 403 ? 'Sessão inválida. Recarregue a página.' : 'Não foi possível concluir. Tente de novo.', 'error');
            }
            return null;
        }
        const body = await response.json();
        if (!response.ok) {
            showToast(body.message, 'error');
            return null;
        }
        return body;
    }

    // ---------- Itens da lista: situação, seleção e filtro ----------
    const items = () => Array.from(document.querySelectorAll('[data-item]'));
    const shownItems = () => items().filter((item) => !item.hidden);
    const bulkBar = $('bulkBar');
    const pickAll = $('pickAll');

    function refreshSelection() {
        if (!bulkBar) return;
        const shown = shownItems();
        const picked = shown.filter((item) => item.querySelector('.js-pick').checked);
        bulkBar.hidden = picked.length === 0;
        $('bulkCount').textContent = picked.length === 1 ? bulkBar.dataset.one : `${picked.length} ${bulkBar.dataset.many}`;
        pickAll.checked = shown.length > 0 && picked.length === shown.length;
        pickAll.indeterminate = picked.length > 0 && picked.length < shown.length;
        $('shownCount').textContent = String(shown.length);
    }

    function setItemStatus(item, status) {
        const active = status === 'active';
        const text = active ? 'Ocultar' : 'Mostrar';
        item.dataset.status = status;
        item.classList.toggle('pitem--hidden', !active && item.classList.contains('pitem'));
        item.classList.toggle('citem--hidden', !active && item.classList.contains('citem'));
        item.querySelector('.js-tag').hidden = active;

        const form = item.querySelector('.js-toggle');
        const button = form.querySelector('button');
        const nextStatus = form.querySelector('[name="status"]');
        if (nextStatus) nextStatus.value = active ? 'inactive' : 'active';
        button.querySelector('.btn__text').textContent = text;
        if (button.title) button.title = text;
        const icon = button.querySelector('use');
        if (icon) icon.setAttribute('href', active ? '#i-eye-off' : '#i-eye');
    }

    function applyFilter() {
        const list = $('productList');
        if (!list) return;
        const query = normalize($('listSearch').value);
        const category = $('listCategory').value;
        const status = $('listStatus').value;
        let shown = 0;
        items().forEach((item) => {
            const match = normalize(item.dataset.name).includes(query)
                && (!category || item.dataset.category === category)
                && (!status || item.dataset.status === status);
            item.hidden = !match;
            // O que saiu da tela sai da seleção: a ação em massa só vale para o que se vê.
            if (!match) item.querySelector('.js-pick').checked = false;
            if (match) shown += 1;
        });
        $('listEmpty').hidden = shown > 0;
    }

    const normalize = (text) => String(text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

    function afterChange(body) {
        (body.changed || []).forEach((id) => {
            const item = document.querySelector(`[data-item][data-id="${id}"]`);
            if (!item) return;
            setItemStatus(item, body.status);
            item.querySelector('.js-pick').checked = false;
        });
        const hiddenCount = $('statHidden');
        if (hiddenCount) hiddenCount.textContent = String(items().filter((item) => item.dataset.status !== 'active').length);
        applyFilter();
        refreshSelection();

        const blocked = body.blocked || [];
        if (blocked.length) showBlocked(blocked);
        if (!blocked.length || (body.changed || []).length) {
            showToast(blocked.length ? `${body.changed.length} ${body.changed.length === 1 ? 'categoria ocultada' : 'categorias ocultadas'}.` : body.message, body.type);
        }
    }

    function showBlocked(blocked) {
        const dialog = $('blockedDialog');
        const list = $('blockedList');
        $('blockedTitle').textContent = blocked.length === 1
            ? `“${blocked[0].name}” não pode ser ocultada`
            : `${blocked.length} categorias não podem ser ocultadas`;
        list.replaceChildren(...blocked.map((category) => {
            const section = document.createElement('section');
            const title = document.createElement('h3');
            title.textContent = `${category.name} (${category.products.length})`;
            const names = document.createElement('ul');
            category.products.forEach((name) => {
                const row = document.createElement('li');
                row.textContent = name;
                names.append(row);
            });
            const link = document.createElement('a');
            link.href = `/admin/products?category=${encodeURIComponent(category.name)}&status=active`;
            link.textContent = 'Ver estes produtos para ocultar';
            section.append(title, names, link);
            return section;
        }));
        dialog.showModal();
    }

    const blockedClose = $('blockedClose');
    if (blockedClose) blockedClose.addEventListener('click', () => $('blockedDialog').close());

    // Ocultar e mostrar um item, sem sair do lugar na página.
    document.querySelectorAll('form.js-toggle').forEach((form) => {
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('button');
            button.disabled = true;
            const body = await send(form.action, new FormData(form));
            button.disabled = false;
            if (body) afterChange(body);
        });
    });

    if (bulkBar) {
        document.querySelectorAll('.js-pick').forEach((pick) => pick.addEventListener('change', refreshSelection));
        pickAll.addEventListener('change', () => {
            shownItems().forEach((item) => { item.querySelector('.js-pick').checked = pickAll.checked; });
            refreshSelection();
        });
        $('bulkClear').addEventListener('click', () => {
            items().forEach((item) => { item.querySelector('.js-pick').checked = false; });
            refreshSelection();
        });
        bulkBar.addEventListener('submit', (event) => event.preventDefault());
        const buttons = Array.from(bulkBar.querySelectorAll('button'));
        bulkBar.querySelectorAll('[data-bulk-status]').forEach((button) => {
            button.addEventListener('click', async () => {
                const data = new FormData(bulkBar);
                data.append('status', button.dataset.bulkStatus);
                shownItems().forEach((item) => {
                    const pick = item.querySelector('.js-pick');
                    if (pick.checked) data.append('ids[]', pick.value);
                });
                buttons.forEach((b) => { b.disabled = true; });
                const body = await send(bulkBar.action, data);
                buttons.forEach((b) => { b.disabled = false; });
                if (body) afterChange(body);
            });
        });
    }

    if ($('productList')) {
        // Chegando pelo aviso de categorias, a lista já abre filtrada.
        const params = new URLSearchParams(window.location.search);
        const category = $('listCategory');
        const wanted = params.get('category');
        if (wanted && Array.from(category.options).some((option) => option.value === wanted)) category.value = wanted;
        if (['active', 'inactive'].includes(params.get('status'))) $('listStatus').value = params.get('status');

        const update = () => { applyFilter(); refreshSelection(); };
        $('listSearch').addEventListener('input', update);
        category.addEventListener('change', update);
        $('listStatus').addEventListener('change', update);
        update();
    }

    // ---------- Enquadramento da foto ----------
    const input = $('photoInput');
    if (!input) return;

    const canvas = $('photoCanvas');
    const ctx = canvas.getContext('2d');
    const zoomInput = $('photoZoom');
    const current = $('photoCurrent');
    const empty = $('photoEmpty');
    const errorBox = $('photoError');
    const removeButton = $('photoRemove');
    const removeFlag = $('photoRemoveFlag');
    const removedNote = $('photoRemoved');
    let image = null;
    let originalName = 'foto.jpg';
    let zoom = 1;
    let centerX = 0;
    let centerY = 0;
    let exportTimer = 0;

    function showError(message) {
        errorBox.textContent = message;
        errorBox.hidden = !message;
    }

    // Tamanho, na foto original, do quadrado visível.
    const cropSide = () => Math.min(image.naturalWidth, image.naturalHeight) / zoom;

    function clampCenter() {
        const half = cropSide() / 2;
        centerX = Math.min(image.naturalWidth - half, Math.max(half, centerX));
        centerY = Math.min(image.naturalHeight - half, Math.max(half, centerY));
    }

    function draw() {
        clampCenter();
        const side = cropSide();
        ctx.drawImage(image, centerX - side / 2, centerY - side / 2, side, side, 0, 0, canvas.width, canvas.height);
    }

    // Coloca a versão enquadrada no campo do formulário: é ela que vai para o servidor.
    function exportCrop() {
        window.clearTimeout(exportTimer);
        exportTimer = window.setTimeout(() => {
            canvas.toBlob((blob) => {
                if (!image || !blob || typeof DataTransfer === 'undefined') return; // removida ou sem suporte
                try {
                    const file = new File([blob], originalName.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
                    const transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                } catch (error) {
                    // Navegador antigo: envia a foto original.
                }
            }, 'image/jpeg', 0.9);
        }, 120);
    }

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;
        showError('');
        const url = URL.createObjectURL(file);
        const next = new Image();
        next.onload = () => {
            image = next;
            originalName = file.name || originalName;
            zoom = 1;
            zoomInput.value = '1';
            centerX = image.naturalWidth / 2;
            centerY = image.naturalHeight / 2;
            if (current) current.hidden = true;
            empty.hidden = true;
            canvas.hidden = false;
            $('photoZoomBox').hidden = false;
            $('photoHint').hidden = false;
            $('photoButtonText').textContent = 'Trocar foto';
            removeButton.hidden = false;
            removeFlag.value = '';
            removedNote.hidden = true;
            draw();
            exportCrop();
            URL.revokeObjectURL(url);
        };
        next.onerror = () => {
            URL.revokeObjectURL(url);
            input.value = '';
            showError('Este navegador não abriu a foto. Use JPG, PNG ou WEBP.');
        };
        next.src = url;
    });

    // A foto salva só é apagada quando o formulário for salvo.
    removeButton.addEventListener('click', () => {
        window.clearTimeout(exportTimer);
        image = null;
        input.value = '';
        showError('');
        canvas.hidden = true;
        $('photoZoomBox').hidden = true;
        $('photoHint').hidden = true;
        if (current) current.hidden = true;
        empty.hidden = false;
        $('photoButtonText').textContent = 'Escolher foto';
        removeButton.hidden = true;
        removeFlag.value = current ? '1' : '';
        removedNote.hidden = !current;
    });

    zoomInput.addEventListener('input', () => {
        if (!image) return;
        zoom = Number(zoomInput.value);
        draw();
        exportCrop();
    });

    let dragging = null;
    canvas.addEventListener('pointerdown', (event) => {
        if (!image) return;
        canvas.setPointerCapture(event.pointerId);
        dragging = { x: event.clientX, y: event.clientY };
    });
    canvas.addEventListener('pointermove', (event) => {
        if (!dragging) return;
        const scale = cropSide() / canvas.getBoundingClientRect().width;
        centerX -= (event.clientX - dragging.x) * scale;
        centerY -= (event.clientY - dragging.y) * scale;
        dragging = { x: event.clientX, y: event.clientY };
        draw();
    });
    const endDrag = () => {
        if (!dragging) return;
        dragging = null;
        exportCrop();
    };
    canvas.addEventListener('pointerup', endDrag);
    canvas.addEventListener('pointercancel', endDrag);
})();
