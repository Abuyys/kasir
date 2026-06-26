<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ProductResource\Pages;
use App\Filament\Admin\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
            Forms\Components\Section::make('Product Information')
                ->schema([

                    Forms\Components\Select::make('category_id')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\TextInput::make('barcode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Forms\Components\TextInput::make('name')
                        ->required(),

                    Forms\Components\FileUpload::make('image')
                        ->image()
                        ->directory('products')
                        ->label('Product Image')
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull(),

                ])
                ->columns(2),

            Forms\Components\Section::make('Pricing')
                ->schema([

                    Forms\Components\TextInput::make('purchase_price')
                        ->numeric()
                        ->required(),

                    Forms\Components\TextInput::make('selling_price')
                        ->numeric()
                        ->required(),

                ])
                ->columns(2),

            Forms\Components\Section::make('Inventory')
                ->schema([

                    Forms\Components\TextInput::make('stock')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('min_stock')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Toggle::make('is_active')
                        ->default(true),

                ])
                ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

            Tables\Columns\ImageColumn::make('image')
                ->circular(),

            Tables\Columns\TextColumn::make('barcode')
                ->searchable(),

            Tables\Columns\TextColumn::make('name')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('category.name')
                ->badge(),

            Tables\Columns\TextColumn::make('purchase_price')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('selling_price')
                ->money('IDR'),

            Tables\Columns\TextColumn::make('stock')
                ->badge()
                ->color(fn ($record) =>
                    $record->stock <= $record->min_stock
                        ? 'danger'
                        : 'success'
                ),

            Tables\Columns\IconColumn::make('is_active')
                ->boolean(),

            ])
            ->filters([

                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),

                Tables\Filters\TernaryFilter::make('is_active'),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print_barcode')
                    ->label('Barcode')
                    ->icon('heroicon-o-qr-code')
                    ->color('warning')
                    ->url(fn ($record) => route('product.barcode', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
