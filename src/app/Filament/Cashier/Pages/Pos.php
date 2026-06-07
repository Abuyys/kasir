<?php

namespace App\Filament\Cashier\Pages;

use Filament\Pages\Page;

class Pos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'POS';

    protected static ?string $title = 'Point Of Sale';

    protected static string $view = 'filament.cashier.pages.pos';
}
