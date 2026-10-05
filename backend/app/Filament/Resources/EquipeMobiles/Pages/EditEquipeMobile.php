<?php

namespace App\Filament\Resources\EquipeMobiles\Pages;

use App\Filament\Resources\EquipeMobiles\EquipeMobileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEquipeMobile extends EditRecord
{
    protected static string $resource = EquipeMobileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
