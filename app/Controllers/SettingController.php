<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Setting;

class SettingController extends BaseController
{
    public function index(): void
    {
        $this->requireAdmin();
        $this->view('admin.settings.index', [
            'title' => 'Configurações',
            'cartEnabled' => (new Setting())->cartEnabled(),
        ]);
    }

    public function update(): void
    {
        $this->requireAdmin();
        $this->requireValidCsrf();
        $enabled = !empty($_POST['cart_enabled']);
        (new Setting())->setCartEnabled($enabled);
        flash($enabled ? 'Carrinho ligado: o site voltou a aceitar pedidos pelo WhatsApp.' : 'Carrinho desligado: o site mostra só os produtos e os preços.');
        $this->redirect('/admin/settings');
    }
}
