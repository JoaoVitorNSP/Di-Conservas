<?php include __DIR__ . '/../_header.php'; ?>
        <div class="page__head">
            <h1>Configurações</h1>
        </div>

        <form method="post" action="/admin/settings" class="panel form form--narrow">
            <?= csrf_field() ?>
            <label class="check">
                <input type="checkbox" name="cart_enabled" value="1"<?= $cartEnabled ? ' checked' : '' ?>>
                <span>Carrinho e pedido pelo WhatsApp</span>
            </label>
            <p class="muted small">Desmarcado, o site mostra só a lista de produtos e os preços: saem o botão Adicionar, o carrinho e o envio do pedido. Os contatos da loja continuam, incluindo o WhatsApp.</p>
            <div class="form__actions">
                <button type="submit" class="btn btn--primary">Salvar</button>
            </div>
        </form>
<?php include __DIR__ . '/../_footer.php'; ?>
