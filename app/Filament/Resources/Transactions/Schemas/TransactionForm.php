<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Models\Asset;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transaction Details')
                    ->description('Enter the transaction information')
                    ->schema([
                        
                        Select::make('type')
                            ->label('Transaction Type')
                            ->options([
                                'buy' => 'Buy',
                                'sell' => 'Sell',
                                'dividend' => 'Dividend',
                                'tax' => 'Tax',
                                'interest' => 'Interest',
                            ])
                            ->required()
                            ->live(),
                        
                        Select::make('asset_id')
                            ->relationship('asset', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Asset Name')
                                    ->required()
                                    ->placeholder('es: Apple Inc.'),
                                TextInput::make('ticker')
                                    ->label('Ticker')
                                    ->required()
                                    ->placeholder('es: AAPL'),
                                Select::make('asset_type')
                                    ->options([
                                        'stock' => 'Stock',
                                        'etf' => 'ETF',
                                        'crypto' => 'Cryptocurrency',
                                    ])
                                    ->placeholder('Select asset type'),
                            ])
                            ->hidden(fn ($get) => in_array($get('type'), ['tax', 'interest'])),
                        
                        Select::make('broker_id')
                            ->relationship('broker', 'name')
                            ->searchable()
                            ->preload(),
                        
                        DateTimePicker::make('traded_at')
                            ->label('Transaction Date')
                            ->required(),
                        
                    ])->columns(2),
                
                Section::make('Amount Details')
                    ->schema([
                        
                        TextInput::make('quantity')
                            ->numeric()
                            ->label('Quantity')
                            ->step(0.00000001)
                            ->hidden(fn ($get) => in_array($get('type'), ['tax', 'interest'])),
                        
                        TextInput::make('price_per_unit')
                            ->numeric()
                            ->label('Unit Price (€)')
                            ->step(0.01)
                            ->hidden(fn ($get) => in_array($get('type'), ['tax', 'interest'])),
                        
                        TextInput::make('fees')
                            ->numeric()
                            ->label(fn ($get) => match($get('type')) {
                                'tax' => 'Tax Amount (€)',
                                'interest' => 'Interest Earned (€)',
                                default => 'Commission (€)',
                            })
                            ->step(0.01)
                            ->default(0),
                        
                        Select::make('currency')
                            ->options([
                                'EUR' => 'EUR (€)',
                                'USD' => 'USD ($)',
                                'GBP' => 'GBP (£)',
                            ])
                            ->default('EUR')
                            ->required(),
                        
                    ])->columns(2),
                
                Section::make('Additional Information')
                    ->collapsed()
                    ->schema([
                        
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'settled' => 'Settled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('settled'),
                        
                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Add any additional notes about this transaction...')
                            ->rows(3),
                        
                    ])->columns(1),
            ]);
    }
}