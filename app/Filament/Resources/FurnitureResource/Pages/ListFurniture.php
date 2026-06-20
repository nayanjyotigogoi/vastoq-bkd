<?php

namespace App\Filament\Resources\FurnitureResource\Pages;

use App\Filament\Resources\FurnitureResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFurniture extends ListRecords
{
    protected static string $resource = FurnitureResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
