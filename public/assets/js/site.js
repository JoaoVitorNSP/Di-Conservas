// Catálogo da Di Conservas: produtos, filtros e carrinho enviado pelo WhatsApp.
(function () {
    'use strict';

    // ---------- Configurações da loja ----------
    const WHATSAPP = '5541996122750';
    const PAGE_SIZE = 12;
    const WHOLESALE_MIN = 6;
    // 'produto': o atacado vale com 6 unidades do mesmo produto.
    // 'pedido':  o atacado vale com 6 unidades somando todo o carrinho.
    const WHOLESALE_SCOPE = 'produto';
    const MAX_QTY = 999;
    const CART_KEY = 'dc-carrinho-v1';

    // ---------- Elementos ----------
    const $ = (id) => document.getElementById(id);
    const grid = $('productGrid');
    const search = $('productSearch');
    const count = $('resultCount');
    const loadMore = $('loadMore');
    const sortSelect = $('sortSelect');
    const productDialog = $('productDialog');
    const filterDialog = $('filterDialog');
    const cartDialog = $('cartDialog');

    // ---------- Estado ----------
    let products = [];
    let category = 'all';
    let query = '';
    let sort = 'default';
    let visible = PAGE_SIZE;
    let dialogProduct = null;
    let cart = new Map(); // id do produto => quantidade
    let orderSent = false;

    const brl = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    const money = (value) => brl.format(value).replace(/ /g, ' ');

    // Ignora acentos e maiúsculas: "jilo" encontra "Jiló".
    const normalize = (text) => String(text || '')
        .normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();

    const whatsUrl = (message) => `https://wa.me/${WHATSAPP}?text=${encodeURIComponent(message)}`;
    const findProduct = (id) => products.find((p) => p.id === id);
    const hasWholesale = (p) => p.wholesalePrice > 0 && p.wholesalePrice < p.retailPrice;

    function el(tag, className, text) {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    function icon(name) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('class', 'icon');
        svg.setAttribute('aria-hidden', 'true');
        const use = document.createElementNS('http://www.w3.org/2000/svg', 'use');
        use.setAttribute('href', `#i-${name}`);
        svg.appendChild(use);
        return svg;
    }

    function noPhoto() {
        const box = el('div', 'no-photo');
        box.append(icon('jar'), el('span', '', 'Foto em breve'));
        return box;
    }

    function fillMedia(container, product, lazy) {
        container.replaceChildren();
        if (!product.image) {
            container.appendChild(noPhoto());
            return;
        }
        const img = el('img');
        img.src = product.image;
        img.alt = [product.name, product.weight].filter(Boolean).join(', ');
        img.width = 800;
        img.height = 800;
        img.decoding = 'async';
        if (lazy) img.loading = 'lazy';
        img.addEventListener('error', () => container.replaceChildren(noPhoto()), { once: true });
        container.appendChild(img);
    }

    // ---------- Carrinho ----------
    function loadCart() {
        try {
            const saved = JSON.parse(localStorage.getItem(CART_KEY) || '[]');
            if (Array.isArray(saved)) cart = new Map(saved);
        } catch (error) {
            cart = new Map();
        }
    }

    function saveCart() {
        try {
            localStorage.setItem(CART_KEY, JSON.stringify([...cart]));
        } catch (error) {
            // Sem armazenamento (ex.: navegação privada): o carrinho vale até fechar a página.
        }
    }

    // Só guardamos id e quantidade: preços sempre vêm do catálogo atual.
    function pruneCart() {
        for (const [id, qty] of cart) {
            if (!findProduct(id) || !Number.isInteger(qty) || qty < 1) cart.delete(id);
        }
    }

    const totalItems = () => [...cart.values()].reduce((sum, qty) => sum + qty, 0);

    function qualifiesWholesale(product, qty) {
        if (!hasWholesale(product)) return false;
        return (WHOLESALE_SCOPE === 'pedido' ? totalItems() : qty) >= WHOLESALE_MIN;
    }

    const unitPrice = (product, qty) => (qualifiesWholesale(product, qty) ? product.wholesalePrice : product.retailPrice);

    function cartLines() {
        return [...cart].map(([id, qty]) => {
            const product = findProduct(id);
            const price = unitPrice(product, qty);
            return { product, qty, price, wholesale: qualifiesWholesale(product, qty), subtotal: price * qty };
        });
    }

    const cartTotal = () => cartLines().reduce((sum, line) => sum + line.subtotal, 0);

    function orderMessage() {
        const lines = cartLines().map((line) => {
            const name = line.product.weight ? `${line.product.name} (${line.product.weight})` : line.product.name;
            const kind = line.wholesale ? ' (atacado)' : '';
            return `• ${line.qty}x ${name}: ${money(line.price)} cada = ${money(line.subtotal)}${kind}`;
        });
        const parts = ['Olá! Gostaria de fazer este pedido:', '', ...lines, '', `Total estimado: ${money(cartTotal())}`];
        const name = $('cartName').value.trim();
        const notes = $('cartNotes').value.trim();
        if (name) parts.push(`Nome: ${name}`);
        if (notes) parts.push(`Observações: ${notes}`);
        return parts.join('\n');
    }

    function setQty(id, qty) {
        const product = findProduct(id);
        if (!product) return;
        const value = Math.max(0, Math.min(MAX_QTY, Math.floor(Number(qty)) || 0));
        if (value === 0) cart.delete(id);
        else cart.set(id, value);
        orderSent = false;
        saveCart();
        $('cartStatus').textContent = value === 0
            ? `${product.name} removido do carrinho.`
            : `${product.name}: ${value} no carrinho.`;
        refreshCartViews();
    }

    // Controle de compra: "Adicionar" ou o seletor de quantidade.
    function renderBuy(container, product) {
        container.replaceChildren();
        const qty = cart.get(product.id) || 0;

        if (qty === 0) {
            const add = el('button', 'add-btn', 'Adicionar');
            add.type = 'button';
            add.dataset.role = 'add';
            add.setAttribute('aria-label', `Adicionar ${product.name} ao carrinho`);
            add.addEventListener('click', () => setQty(product.id, 1));
            container.appendChild(add);
            return;
        }
        container.appendChild(stepper(product, qty));
    }

    function stepper(product, qty) {
        const box = el('div', 'stepper');
        box.setAttribute('role', 'group');
        box.setAttribute('aria-label', `Quantidade de ${product.name}`);

        const minus = el('button', '', '−');
        minus.type = 'button';
        minus.dataset.role = 'minus';
        minus.setAttribute('aria-label', 'Diminuir quantidade');
        minus.addEventListener('click', () => setQty(product.id, (cart.get(product.id) || 0) - 1));

        const input = el('input');
        input.type = 'number';
        input.min = '0';
        input.max = String(MAX_QTY);
        input.inputMode = 'numeric';
        input.value = String(qty);
        input.dataset.role = 'qty';
        input.setAttribute('aria-label', 'Quantidade');
        input.addEventListener('focus', () => input.select());
        input.addEventListener('change', () => setQty(product.id, input.value));

        const plus = el('button', '', '+');
        plus.type = 'button';
        plus.dataset.role = 'plus';
        plus.setAttribute('aria-label', 'Aumentar quantidade');
        plus.addEventListener('click', () => setQty(product.id, (cart.get(product.id) || 0) + 1));

        box.append(minus, input, plus);
        return box;
    }

    // Atualiza tudo que mostra o carrinho, mantendo o foco no controle que estava em uso.
    function refreshCartViews() {
        const active = document.activeElement;
        const slot = active && active.closest ? active.closest('[data-slot]') : null;
        const focusSlot = slot ? slot.dataset.slot : null;
        const focusRole = active && active.dataset ? active.dataset.role : null;

        const items = totalItems();
        const badge = $('cartCount');
        badge.textContent = items > 99 ? '99+' : String(items);
        badge.hidden = items === 0;
        $('openCart').setAttribute('aria-label', items ? `Abrir carrinho, ${items} ${items === 1 ? 'item' : 'itens'}` : 'Abrir carrinho');

        document.querySelectorAll('.card__buy').forEach((box) => {
            const product = findProduct(Number(box.dataset.id));
            if (product) renderBuy(box, product);
        });
        if (dialogProduct) {
            renderBuy($('dialogBuy'), dialogProduct);
            $('dialogToCart').hidden = items === 0;
            $('dialogToCart').textContent = `Ver carrinho (${items})`;
        }
        if (cartDialog.open) renderCart();

        if (focusSlot && focusRole) {
            const scope = document.querySelector(`[data-slot="${focusSlot}"]`);
            if (scope) {
                const target = scope.querySelector(`[data-role="${focusRole}"]`) || scope.querySelector('[data-role="add"], [data-role="plus"]');
                if (target) target.focus();
                else if (cartDialog.open) cartDialog.querySelector('[data-close]').focus();
            } else if (cartDialog.open) {
                cartDialog.querySelector('[data-close]').focus();
            }
        }
    }

    function renderCart() {
        const lines = cartLines();
        const empty = lines.length === 0;
        $('cartEmpty').hidden = !empty;
        $('cartFields').hidden = empty;
        $('cartFoot').hidden = empty;

        $('cartItems').replaceChildren(...lines.map((line) => {
            const { product } = line;
            const item = el('li', 'cart__item');
            item.dataset.slot = `cart-${product.id}`;

            const thumb = el('div', 'cart__thumb');
            fillMedia(thumb, product, false);

            const name = el('p', 'cart__name', product.name);
            if (product.weight) name.appendChild(el('small', '', product.weight));

            const unit = el('p', 'cart__unit');
            unit.append(`${money(line.price)} cada`);
            if (line.wholesale) {
                unit.append(' · ', el('b', '', 'atacado'));
            } else if (hasWholesale(product)) {
                const missing = WHOLESALE_MIN - (WHOLESALE_SCOPE === 'pedido' ? totalItems() : line.qty);
                if (missing > 0 && missing <= WHOLESALE_MIN / 2) {
                    unit.appendChild(el('span', 'cart__tip', `Faltam ${missing} para o atacado: ${money(product.wholesalePrice)} cada`));
                }
            }

            const row = el('div', 'cart__row');
            row.append(stepper(product, line.qty), el('span', 'cart__subtotal', money(line.subtotal)));

            item.append(thumb, name, unit, row);
            return item;
        }));

        $('cartTotal').textContent = money(cartTotal());
        updateSendLink();
        renderSentNote();
    }

    function updateSendLink() {
        if (cart.size) $('cartSend').href = whatsUrl(orderMessage());
    }

    function renderSentNote() {
        const note = $('cartNote');
        note.classList.toggle('cart__note--sent', orderSent);
        note.textContent = orderSent
            ? 'Abrimos o WhatsApp com o seu pedido. Depois de enviar a mensagem, você pode esvaziar o carrinho.'
            : 'Nada é cobrado aqui: você confere a mensagem no WhatsApp e combina o restante com a gente.';
    }

    function openCart() {
        if (productDialog.open) productDialog.close();
        renderCart();
        cartDialog.showModal();
        cartDialog.querySelector('.cart__scroll').scrollTop = 0;
    }

    // ---------- Catálogo ----------
    function renderCard(product, index) {
        const card = el('article', 'card');

        const media = el('div', 'media');
        fillMedia(media, product, index >= 4);

        const body = el('div', 'card__body');
        const name = el('h3', 'card__name');
        const open = el('button', 'card__open', product.name);
        open.type = 'button';
        open.addEventListener('click', () => openDialog(product));
        name.appendChild(open);

        const prices = el('p', 'card__prices');
        prices.appendChild(el('span', 'card__retail', money(product.retailPrice)));
        if (hasWholesale(product)) {
            const wholesale = el('span', 'card__wholesale');
            wholesale.append(el('b', '', money(product.wholesalePrice)), ' no atacado');
            prices.appendChild(wholesale);
        }

        const buy = el('div', 'card__buy');
        buy.dataset.id = String(product.id);
        buy.dataset.slot = `card-${product.id}`;
        renderBuy(buy, product);

        body.append(name, el('p', 'card__weight', product.weight || ''), prices, buy);
        card.append(media, body);
        return card;
    }

    function matches(product) {
        if (category !== 'all' && product.category !== category) return false;
        if (!query) return true;
        return normalize(`${product.name} ${product.description} ${product.category}`).includes(query);
    }

    function sorted(list) {
        const copy = [...list];
        if (sort === 'name') copy.sort((a, b) => a.name.localeCompare(b.name, 'pt-BR'));
        if (sort === 'price-asc') copy.sort((a, b) => a.retailPrice - b.retailPrice);
        if (sort === 'price-desc') copy.sort((a, b) => b.retailPrice - a.retailPrice);
        return copy;
    }

    function resetFilters() {
        search.value = '';
        query = '';
        category = 'all';
        visible = PAGE_SIZE;
        renderCategories();
        render();
    }

    function render() {
        const found = sorted(products.filter(matches));
        count.textContent = found.length === 1 ? '1 produto' : `${found.length} produtos`;
        grid.replaceChildren();

        if (!found.length) {
            const empty = el('div', 'empty');
            empty.appendChild(el('p', '', products.length ? 'Não encontramos nenhum produto com essa busca.' : 'Nenhum produto disponível no momento.'));
            if (products.length) {
                const clear = el('button', 'btn btn--line', 'Limpar busca');
                clear.type = 'button';
                clear.addEventListener('click', () => {
                    resetFilters();
                    search.focus();
                });
                empty.appendChild(clear);
            }
            grid.appendChild(empty);
        } else {
            found.slice(0, visible).forEach((product, index) => grid.appendChild(renderCard(product, index)));
        }

        loadMore.hidden = found.length <= visible;
    }

    function renderCategories() {
        const names = [...new Set(products.map((p) => p.category).filter(Boolean))]
            .sort((a, b) => a.localeCompare(b, 'pt-BR'));
        const options = [['all', 'Todos', products.length]]
            .concat(names.map((name) => [name, name, products.filter((p) => p.category === name).length]));

        document.querySelectorAll('[data-categories]').forEach((list) => {
            list.replaceChildren(...options.map(([value, label, total]) => {
                const button = el('button', 'cat-btn');
                button.type = 'button';
                button.setAttribute('aria-pressed', String(value === category));
                button.append(el('span', '', label), el('small', '', String(total)));
                button.addEventListener('click', () => selectCategory(value));
                return button;
            }));
        });

        const filtered = category !== 'all';
        $('filterDot').hidden = !filtered;
        $('activeFilter').hidden = !filtered;
        if (filtered) {
            $('activeFilter').textContent = category;
            $('activeFilter').setAttribute('aria-label', `Remover filtro ${category}`);
        }
    }

    function selectCategory(value) {
        category = value;
        visible = PAGE_SIZE;
        renderCategories();
        render();
        if (filterDialog.open) filterDialog.close();
        // Se a pessoa já desceu a lista, volta para o começo dos resultados.
        const results = $('results');
        if (results.getBoundingClientRect().top < 0) results.scrollIntoView();
    }

    function openDialog(product) {
        dialogProduct = product;
        fillMedia($('dialogMedia'), product, false);
        $('dialogCategory').textContent = product.category || '';
        $('dialogName').textContent = product.name;
        $('dialogWeight').textContent = product.weight || '';
        $('dialogDesc').textContent = product.description || '';
        $('dialogRetail').textContent = money(product.retailPrice);
        $('dialogWholesaleBox').hidden = !hasWholesale(product);
        $('dialogWholesale').textContent = money(product.wholesalePrice);
        $('dialogWholesaleLabel').textContent = `Atacado (${WHOLESALE_MIN} ou mais)`;
        $('dialogBuy').dataset.slot = 'dialog';
        refreshCartViews();
        productDialog.showModal();
        productDialog.scrollTop = 0;
    }

    function showLoadError() {
        count.textContent = '';
        const box = el('div', 'empty');
        box.appendChild(el('p', '', 'Não conseguimos carregar os produtos agora. Tente de novo em instantes ou chame a gente no WhatsApp.'));
        const link = el('a', 'btn btn--whats');
        link.href = `https://wa.me/${WHATSAPP}`;
        link.target = '_blank';
        link.rel = 'noopener';
        link.append(icon('whatsapp'), 'Falar no WhatsApp');
        box.appendChild(link);
        grid.replaceChildren(box);
    }

    async function fetchProducts() {
        const response = await fetch('/api/products', { cache: 'no-cache' });
        if (!response.ok) throw new Error('Catálogo indisponível');
        const data = await response.json();
        if (!Array.isArray(data)) throw new Error('Catálogo indisponível');
        return data;
    }

    // ---------- Início ----------
    function bindEvents() {
        document.querySelectorAll('.js-whats').forEach((link) => {
            link.href = whatsUrl('Olá! Gostaria de mais informações.');
        });

        search.addEventListener('input', () => {
            query = normalize(search.value);
            visible = PAGE_SIZE;
            render();
        });
        sortSelect.addEventListener('change', () => {
            sort = sortSelect.value;
            visible = PAGE_SIZE;
            render();
        });
        loadMore.addEventListener('click', () => {
            visible += PAGE_SIZE;
            render();
        });

        $('openFilters').addEventListener('click', () => filterDialog.showModal());
        $('activeFilter').addEventListener('click', () => selectCategory('all'));

        $('openCart').addEventListener('click', openCart);
        $('dialogToCart').addEventListener('click', openCart);
        $('cartName').addEventListener('input', updateSendLink);
        $('cartNotes').addEventListener('input', updateSendLink);
        $('cartClear').addEventListener('click', () => {
            cart.clear();
            orderSent = false;
            saveCart();
            $('cartNotes').value = '';
            $('cartStatus').textContent = 'Carrinho esvaziado.';
            refreshCartViews();
            cartDialog.querySelector('[data-close]').focus();
        });
        $('cartSend').addEventListener('click', () => {
            orderSent = true;
            renderSentNote();
        });

        // Fecha pelo X ou tocando fora do painel.
        document.querySelectorAll('dialog').forEach((dialog) => {
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog || event.target.closest('[data-close]')) dialog.close();
            });
        });
        productDialog.addEventListener('close', () => { dialogProduct = null; });
    }

    async function init() {
        $('year').textContent = new Date().getFullYear();
        $('wholesaleRule').textContent = WHOLESALE_SCOPE === 'pedido'
            ? `Preço menor, a partir de ${WHOLESALE_MIN} unidades no pedido.`
            : `Preço menor, a partir de ${WHOLESALE_MIN} unidades do mesmo produto.`;
        loadCart();
        bindEvents();

        try {
            const data = await fetchProducts();
            // Produtos com foto aparecem primeiro; o resto mantém a ordem do cadastro.
            products = data
                .map((p, i) => ({
                    ...p,
                    retailPrice: Number(p.retailPrice) || 0,
                    wholesalePrice: Number(p.wholesalePrice) || 0,
                    order: i,
                }))
                .sort((a, b) => (Boolean(b.image) - Boolean(a.image)) || (a.order - b.order));
            pruneCart();
            saveCart();
            renderCategories();
            render();
            refreshCartViews();
        } catch (error) {
            console.error(error);
            showLoadError();
        }
    }

    init();
})();
