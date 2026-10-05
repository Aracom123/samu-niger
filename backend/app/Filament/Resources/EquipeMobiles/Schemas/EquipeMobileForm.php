<?php

namespace App\Filament\Resources\EquipeMobiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EquipeMobileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->required(),
                Select::make('region_id')
                    ->label('Région')
                    ->relationship('region', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('actif')
                    ->default(true)
                    ->required(),
            ]);
    }
}
