<?php

namespace App\Filament\Resources\Orientations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrientationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('intervention_id')
                    ->label('Intervention')
                    ->relationship('intervention', 'numero')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('structure_sanitaire_id')
                    ->label('Structure sanitaire')
                    ->relationship('structureSanitaire', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'proposee' => 'Proposée',
                        'acceptee' => 'Acceptée',
                        'refusee' => 'Refusée',
                    ])
                    ->required()
                    ->default('proposee'),
                TextInput::make('motif_refus')
                    ->label('Motif du refus'),
            ]);
    }
}
