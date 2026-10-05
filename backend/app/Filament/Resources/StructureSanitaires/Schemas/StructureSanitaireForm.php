<?php

namespace App\Filament\Resources\StructureSanitaires\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class StructureSanitaireForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('nom')
                    ->required(),
                Select::make('region_id')
                    ->label('Région')
                    ->relationship('region', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('type_structure_id')
                    ->label('Type de structure')
                    ->relationship('typeStructure', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('departement')
                    ->label('Département'),
                TextInput::make('commune'),
                TextInput::make('localite')
                    ->label('Localité'),
                TextInput::make('adresse'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                TextInput::make('telephone')
                    ->label('Téléphone')
                    ->tel(),
                TextInput::make('email')
                    ->label('Adresse e-mail')
                    ->email(),
                Select::make('responsable_user_id')
                    ->label('Responsable')
                    ->relationship('responsable', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->required()
                    ->default('active'),
            ]);
    }
}
