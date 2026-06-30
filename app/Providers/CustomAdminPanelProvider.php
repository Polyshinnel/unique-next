<?php

namespace App\Providers;

use App\Providers\Filament\AdminPanelProvider;
use Filament\Panel;

class CustomAdminPanelProvider extends AdminPanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return parent::panel($panel)
            ->favicon(asset('favicon.png'));
    }
}
