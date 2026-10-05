<?php

namespace App\Filament\Resources\TypeStructures\Pages;

use App\Filament\Resources\TypeStructures\TypeStructureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTypeStructures extends ListRecords
{
    protected static string $resource = TypeStructureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
