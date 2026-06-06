<?php

namespace App\Filament\Resources\Guests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GuestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stage_name')
                    ->label('Stage Name')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('contact')
                    ->searchable(),
                TextColumn::make('phone')
                    ->placeholder('—'),
                TextColumn::make('method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'email'    => 'info',
                        'calendar' => 'warning',
                        'text'     => 'success',
                        default    => 'gray',
                    }),
                TextColumn::make('reminder_time')
                    ->label('Remind')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        '1week' => '1 week before',
                        '1day'  => '1 day before',
                        'dayof' => 'Day of',
                        default => $state,
                    }),
                TextColumn::make('created_at')
                    ->label('Signed Up')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->options([
                        'email'    => 'Email',
                        'calendar' => 'Calendar',
                        'text'     => 'Text',
                    ]),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
