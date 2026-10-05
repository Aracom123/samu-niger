<?php

namespace App\Filament\Resources\Patients\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('identifiant')
                    ->helperText('Laisser vide pour générer automatiquement.'),
                TextInput::make('nom'),
                TextInput::make('prenom')
                    ->label('Prénom'),
                Select::make('sexe')
                    ->options(['M' => 'Masculin', 'F' => 'Féminin', 'inconnu' => 'Inconnu'])
                    ->required()
                    ->default('inconnu'),
                DatePicker::make('date_naissance')
                    ->label('Date de naissance'),
                TextInput::make('age_approximatif')
                    ->label('Âge approximatif')
                    ->numeric(),
                TextInput::make('telephone')
                    ->label('Téléphone')
                    ->tel(),
                TextInput::make('localisation_prise_en_charge')
                    ->label('Lieu de prise en charge'),
                TextInput::make('motif'),
                Select::make('niveau_urgence')
                    ->label('Niveau d\'urgence')
                    ->options([
                        'critique' => 'Critique',
                        'urgent' => 'Urgent',
                        'modere' => 'Modéré',
                        'faible' => 'Faible',
                    ])
                    ->required()
                    ->default('modere'),
                Textarea::make('observations')
                    ->columnSpanFull(),
                Select::make('structure_origine_id')
                    ->label('Structure d\'origine')
                    ->relationship('structureOrigine', 'nom')
                    ->searchable()
                    ->preload(),
                Select::make('structure_destination_id')
                    ->label('Structure de destination')
                    ->relationship('structureDestination', 'nom')
                    ->searchable()
                    ->preload(),
            ]);
    }
}
