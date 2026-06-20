<?php

namespace App\Filament\Resources\ListingUnlockResource\Pages;

use App\Filament\Resources\ListingUnlockResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListListingUnlocks extends ListRecords
{
    protected static string $resource = ListingUnlockResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
