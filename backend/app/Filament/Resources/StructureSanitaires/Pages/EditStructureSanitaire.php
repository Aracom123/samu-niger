<?php

namespace App\Filament\Resources\StructureSanitaires\Pages;

use App\Filament\Resources\StructureSanitaires\StructureSanitaireResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStructureSanitaire extends EditRecord
{
    protected static string $resource = StructureSanitaireResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
