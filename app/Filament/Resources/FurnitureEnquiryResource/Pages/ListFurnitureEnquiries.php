<?php

namespace App\Filament\Resources\FurnitureEnquiryResource\Pages;

use App\Filament\Resources\FurnitureEnquiryResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFurnitureEnquiries extends ListRecords
{
    protected static string $resource = FurnitureEnquiryResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
