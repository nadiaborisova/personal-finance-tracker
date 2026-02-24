<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BudgetResource\Pages;
use App\Models\Budget;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class BudgetResource extends Resource
{
    protected static ?string $model = Budget::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Budget Details')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->prefix('€')
                            ->required(),

                        Forms\Components\DatePicker::make('starts_at')
                            ->label('Start Date')
                            ->default(now()->startOfMonth())
                            ->required(),

                        Forms\Components\DatePicker::make('ends_at')
                            ->label('End Date')
                            ->default(now()->endOfMonth())
                            ->required()
                            ->after('starts_at'),

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.badge_html')
                    ->label('Category')
                    ->html(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('EUR')
                    ->label('Limit'),

                Tables\Columns\TextColumn::make('spent')
                    ->label('Spent')
                    ->money('EUR')
                    ->state(function ($record) {
                        return \App\Models\Transaction::where('category_id', $record->category_id)
                            ->where('user_id', $record->user_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$record->starts_at, $record->ends_at])
                            ->sum('amount');
                    })
                    ->color(fn ($state, $record) => $state > $record->amount ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('usage')
                    ->label('% Usage')
                    ->state(function ($record) {
                        $spent = \App\Models\Transaction::where('category_id', $record->category_id)
                            ->where('user_id', $record->user_id)
                            ->where('type', 'expense')
                            ->whereBetween('transaction_date', [$record->starts_at, $record->ends_at])
                            ->sum('amount');

                        if ($record->amount <= 0) return '0%';
                        
                        $percentage = round(($spent / $record->amount) * 100);
                        return "{$percentage}%";
                    })
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        (int)$state >= 100 => 'danger',
                        (int)$state >= 80 => 'warning',
                        default => 'success',
                    }),

                Tables\Columns\TextColumn::make('starts_at')->date(),
                Tables\Columns\TextColumn::make('ends_at')->date(),
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
            'index' => Pages\ListBudgets::route('/'),
            'create' => Pages\CreateBudget::route('/create'),
            'edit' => Pages\EditBudget::route('/{record}/edit'),
        ];
    }
}
