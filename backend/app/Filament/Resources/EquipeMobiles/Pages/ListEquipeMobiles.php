<?php

namespace App\Filament\Resources\EquipeMobiles\Pages;

use App\Filament\Resources\EquipeMobiles\EquipeMobileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEquipeMobiles extends ListRecords
{
    protected static string $resource = EquipeMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
