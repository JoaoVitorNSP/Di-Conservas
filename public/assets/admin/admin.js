// Painel: confirmação de exclusão, filtro da lista e enquadramento da foto no quadrado.
(function () {
    'use strict';

    const $ = (id) => document.getElementById(id);

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) event.preventDefault();
        });
    });

    // ---------- Filtro da lista ----------
    const normalize = (text) => String(text || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
    const list = $('productList');
    if (list) {
        const search = $('listSearch');
        const category = $('listCategory');
        const apply = () => {
            const query = normalize(search.value);
            let shown = 0;
            list.querySelectorAll('.pitem').forEach((item) => {
                const match = normalize(item.dataset.name).includes(query)
                    && (!category.value || item.dataset.category === category.value);
                item.hidden = !match;
                if (match) shown += 1;
            });
            $('listEmpty').hidden = shown > 0;
        };
        search.addEventListener('input', apply);
        category.addEventListener('change', apply);
    }

    // ---------- Enquadramento da foto ----------
    const input = $('photoInput');
    if (!input) return;

    const canvas = $('photoCanvas');
    const ctx = canvas.getContext('2d');
    const zoomInput = $('photoZoom');
    const current = $('photoCurrent');
    const errorBox = $('photoError');
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
                if (!blob || typeof DataTransfer === 'undefined') return; // sem suporte: envia a foto original
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
            current.hidden = true;
            canvas.hidden = false;
            $('photoZoomBox').hidden = false;
            $('photoHint').hidden = false;
            $('photoButtonText').textContent = 'Trocar foto';
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
