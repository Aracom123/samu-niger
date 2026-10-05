<?php

namespace App\Filament\Resources\Regions\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RegionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                Toggle::make('actif')
                    ->required(),
            ]);
    }
}
