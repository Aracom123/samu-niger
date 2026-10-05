<?php

namespace App\Filament\Resources\Orientations\Pages;

use App\Filament\Resources\Orientations\OrientationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrientation extends EditRecord
{
    protected static string $resource = OrientationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
