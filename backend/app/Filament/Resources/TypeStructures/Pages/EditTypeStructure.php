<?php

namespace App\Filament\Resources\TypeStructures\Pages;

use App\Filament\Resources\TypeStructures\TypeStructureResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTypeStructure extends EditRecord
{
    protected static string $resource = TypeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
