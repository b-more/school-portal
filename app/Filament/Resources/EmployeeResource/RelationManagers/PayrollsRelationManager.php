<?php

namespace App\Filament\Resources\EmployeeResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PayrollsRelationManager extends RelationManager
{
    protected static string $relationship = 'payrolls';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('month')
                    ->options([
                        'January' => 'January',
                        'February' => 'February',
                        'March' => 'March',
                        'April' => 'April',
                        'May' => 'May',
                        'June' => 'June',
                        'July' => 'July',
                        'August' => 'August',
                        'September' => 'September',
                        'October' => 'October',
                        'November' => 'November',
                        'December' => 'December',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->required()
                    ->default(date('Y'))
                    ->minValue(2000)
                    ->maxValue(date('Y') + 1),
                Forms\Components\TextInput::make('basic_salary')
                    ->numeric()
                    ->required()
                    ->prefix('ZMW'),
                Forms\Components\Repeater::make('allowances')
                    ->schema([
                        Forms\Components\TextInput::make('type')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW'),
                    ])
                    ->columns(2),
                Forms\Components\Repeater::make('deductions')
                    ->schema([
                        Forms\Components\TextInput::make('type')
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->numeric()
                            ->required()
                            ->prefix('ZMW'),
                    ])
                    ->columns(2),
                Forms\Components\TextInput::make('gross_salary')
                    ->numeric()
                    ->required()
                    ->prefix('ZMW'),
                Forms\Components\TextInput::make('net_salary')
                    ->numeric()
                    ->required()
                    ->prefix('ZMW'),
                Forms\Components\Select::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ])
                    ->required()
                    ->default('pending'),
                Forms\Components\DatePicker::make('payment_date'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('month')
            ->columns([
                Tables\Columns\TextColumn::make('month')
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')
                    ->sortable(),
                Tables\Columns\TextColumn::make('basic_salary')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('gross_salary')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_salary')
                    ->money('ZMW')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('payment_status')
                    ->colors([
                        'danger' => 'pending',
                        'success' => 'paid',
                    ]),
                Tables\Columns\TextColumn::make('payment_date')
                    ->date(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),
                // Tables\Filters\SelectFilter::make('year')
                //     ->options(
                //         fn () => range(date('Y') - 5, date('Y'))
                //             |> array_combine($$, $$)
                //     ),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
