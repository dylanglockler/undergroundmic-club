<?php

namespace App\Filament\Resources\Guests\Pages;

use App\Filament\Resources\Guests\GuestResource;
use App\Models\Guest;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGuests extends ListRecords
{
    protected static string $resource = GuestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyPhones')
                ->label('Copy Phone Numbers')
                ->icon('heroicon-o-phone')
                ->color('gray')
                ->form([
                    Textarea::make('numbers')
                        ->label('Phone Numbers')
                        ->default(fn () => Guest::whereNotNull('phone')->where('phone', '!=', '')->pluck('phone')->join(', '))
                        ->rows(4)
                        ->readOnly(),
                ])
                ->modalSubmitActionLabel('Done')
                ->modalCancelAction(false),

            Action::make('copyEmails')
                ->label('Copy Email List')
                ->icon('heroicon-o-envelope')
                ->color('gray')
                ->form([
                    Textarea::make('emails')
                        ->label('Email Addresses')
                        ->default(fn () => Guest::whereIn('method', ['email', 'calendar'])->pluck('contact')->join(', '))
                        ->rows(4)
                        ->readOnly(),
                ])
                ->modalSubmitActionLabel('Done')
                ->modalCancelAction(false),
        ];
    }
}
