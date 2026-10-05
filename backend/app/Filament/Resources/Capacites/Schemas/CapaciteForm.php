<?php

namespace App\Filament\Resources\Capacites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CapaciteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('structure_sanitaire_id')
                    ->label('Structure sanitaire')
                    ->relationship('structureSanitaire', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('lits_totaux')
                    ->label('Lits totaux')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('lits_occupes')
                    ->label('Lits occupés')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('lits_reserves')
                    ->label('Lits réservés')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
                TextInput::make('lits_indisponibles')
                    ->label('Lits indisponibles')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ]);
    }
}
