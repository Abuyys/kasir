<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('transaction_code')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name'),

                Tables\Columns\TextColumn::make('final_price')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('status')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('cancel_transaction')
                    ->label('Batalkan')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'success')
                    ->action(function ($record) {
                        \DB::transaction(function () use ($record) {
                            $record->update(['status' => 'cancel']);
                            
                            foreach ($record->details as $detail) {
                                $product = $detail->product;
                                if ($product) {
                                    $before = $product->stock;
                                    $product->increment('stock', $detail->quantity);
                                    
                                    \App\Models\StockMovement::create([
                                        'product_id'   => $product->id,
                                        'user_id'      => auth()->id(),
                                        'type'         => 'cancel',
                                        'qty'          => $detail->quantity,
                                        'before_stock' => $before,
                                        'after_stock'  => $product->stock,
                                        'notes'        => 'Pembatalan transaksi - ' . $record->transaction_code,
                                    ]);
                                }
                            }
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Transaksi berhasil dibatalkan')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('refund_transaction')
                    ->label('Refund')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'success')
                    ->action(function ($record) {
                        \DB::transaction(function () use ($record) {
                            $record->update(['status' => 'refund']);
                            
                            foreach ($record->details as $detail) {
                                $product = $detail->product;
                                if ($product) {
                                    $before = $product->stock;
                                    $product->increment('stock', $detail->quantity);
                                    
                                    \App\Models\StockMovement::create([
                                        'product_id'   => $product->id,
                                        'user_id'      => auth()->id(),
                                        'type'         => 'refund',
                                        'qty'          => $detail->quantity,
                                        'before_stock' => $before,
                                        'after_stock'  => $product->stock,
                                        'notes'        => 'Refund transaksi - ' . $record->transaction_code,
                                    ]);
                                }
                            }
                        });

                        \Filament\Notifications\Notification::make()
                            ->title('Transaksi berhasil direfund')
                            ->success()
                            ->send();
                    })
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
