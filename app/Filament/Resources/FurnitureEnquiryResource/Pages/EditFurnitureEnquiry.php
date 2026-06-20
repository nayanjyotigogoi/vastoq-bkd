<?php

namespace App\Filament\Resources\FurnitureEnquiryResource\Pages;

use App\Filament\Resources\FurnitureEnquiryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFurnitureEnquiry extends EditRecord
{
    protected static string $resource = FurnitureEnquiryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
