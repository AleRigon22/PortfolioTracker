<?php

namespace App\Filament\Resources\Transactions;

use App\Filament\Resources\Transactions\Pages\CreateTransaction;
use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\Pages\ListTransactions;
use App\Filament\Resources\Transactions\Tables\TransactionsTable;
use App\Models\Transaction;
use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $pluralModelLabel = 'Transactions';
    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $recordTitleAttribute = 'Transaction';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(2)->schema([
                
                Section::make('Transaction Details')
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
                        
                    ]),
                
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
                        
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'settled' => 'Settled',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('settled'),
                        
                        Textarea::make('notes')
                            ->label('Notes')
                            ->placeholder('Add any additional notes...')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                    ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return TransactionsTable::configure($table);
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
            'index' => ListTransactions::route('/'),
            'create' => CreateTransaction::route('/create'),
            'edit' => EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}