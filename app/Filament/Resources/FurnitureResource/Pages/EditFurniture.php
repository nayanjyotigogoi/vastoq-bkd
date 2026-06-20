<?php

namespace App\Filament\Resources\FurnitureResource\Pages;

use App\Filament\Resources\FurnitureResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFurniture extends EditRecord
{
    protected static string $resource = FurnitureResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
