<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RecurringTransactionResource\Pages;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;


class RecurringTransactionResource extends Resource
{
    protected static ?string $model = RecurringTransaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Category')
                    ->options(
                        Category::where('user_id', Auth::id())
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('description')
                    ->required(),

                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->prefix('€')
                    ->required(),

                Forms\Components\Select::make('type')
                    ->options([
                        'income'  => 'Income',
                        'expense' => 'Expense',
                    ])
                    ->required(),

                Forms\Components\Select::make('frequency')
                    ->options([
                        'daily'   => 'Daily',
                        'weekly'  => 'Weekly',
                        'monthly' => 'Monthly',
                        'yearly'  => 'Yearly',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('starts_at')
                    ->required(),

                Forms\Components\DatePicker::make('ends_at')
                    ->after('starts_at'),

                Forms\Components\Toggle::make('is_active')
                    ->default(true),

                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),

                Forms\Components\Hidden::make('next_due_date')
                    ->default(now()->toDateString()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('category.badge_html')
                    ->label('Category')
                    ->html(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'income'  => 'success',
                        'expense' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('frequency')
                    ->badge(),

                Tables\Columns\TextColumn::make('starts_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'income'  => 'Income',
                        'expense' => 'Expense',
                    ]),

                Tables\Filters\SelectFilter::make('frequency')
                    ->options([
                        'daily'   => 'Daily',
                        'weekly'  => 'Weekly',
                        'monthly' => 'Monthly',
                        'yearly'  => 'Yearly',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListRecurringTransactions::route('/'),
            'create' => Pages\CreateRecurringTransaction::route('/create'),
            'edit'   => Pages\EditRecurringTransaction::route('/{record}/edit'),
        ];
    }
}