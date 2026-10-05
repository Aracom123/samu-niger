<?php

namespace App\Filament\Resources\Capacites\Pages;

use App\Filament\Resources\Capacites\CapaciteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCapacite extends EditRecord
{
    protected static string $resource = CapaciteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }
}
