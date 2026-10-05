<?php

namespace App\Filament\Resources\Ambulances\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AmbulanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('immatriculation')
                    ->required(),
                Select::make('equipe_mobile_id')
                    ->label('Équipe mobile')
                    ->relationship('equipeMobile', 'nom')
                    ->searchable()
                    ->preload(),
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'disponible' => 'Disponible',
                        'en_intervention' => 'En intervention',
                        'hors_service' => 'Hors service',
                    ])
                    ->required()
                    ->default('disponible'),
            ]);
    }
}
