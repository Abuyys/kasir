<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\StockOpnameResource\Pages;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockOpname;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockOpnameResource extends Resource
{
    protected static ?string $model = StockOpname::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Stock Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $product = Product::find($state);
                        $set('system_stock', $product?->stock ?? 0);
                    }),

                Forms\Components\TextInput::make('system_stock')
                    ->disabled(),

                Forms\Components\TextInput::make('physical_stock')
                    ->numeric()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        $set('difference', (int)$state - (int)$get('system_stock'));
                    }),

                Forms\Components\Textarea::make('notes'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('system_stock')
                    ->label('Sistem')
                    ->sortable(),
                Tables\Columns\TextColumn::make('physical_stock')
                    ->label('Fisik')
                    ->sortable(),
                Tables\Columns\TextColumn::make('difference')
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockOpnames::route('/'),
            'create' => Pages\CreateStockOpname::route('/create'),
            'edit' => Pages\EditStockOpname::route('/{record}/edit'),
        ];
    }
}
