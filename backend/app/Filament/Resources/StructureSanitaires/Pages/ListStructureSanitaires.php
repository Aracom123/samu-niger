<?php

namespace App\Filament\Resources\StructureSanitaires\Pages;

use App\Filament\Resources\StructureSanitaires\StructureSanitaireResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStructureSanitaires extends ListRecords
{
    protected static string $resource = StructureSanitaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
