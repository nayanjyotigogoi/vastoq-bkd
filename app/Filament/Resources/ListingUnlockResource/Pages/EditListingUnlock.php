<?php

namespace App\Filament\Resources\ListingUnlockResource\Pages;

use App\Filament\Resources\ListingUnlockResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditListingUnlock extends EditRecord
{
    protected static string $resource = ListingUnlockResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
