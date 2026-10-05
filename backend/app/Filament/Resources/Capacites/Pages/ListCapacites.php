<?php

namespace App\Filament\Resources\Capacites\Pages;

use App\Filament\Resources\Capacites\CapaciteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCapacites extends ListRecords
{
    protected static string $resource = CapaciteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
