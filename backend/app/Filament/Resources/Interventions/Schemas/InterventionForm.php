<?php

namespace App\Filament\Resources\Interventions\Schemas;

use App\Models\Patient;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class InterventionForm
{
    public static function configure(Schema $schema): Schema
    {
        $peutGererToutesLesEquipes = fn (): bool => (bool) auth()->user()?->can('interventions.gerer');

        return $schema
            ->components([
                TextInput::make('numero')
                    ->label('Numéro')
                    ->helperText('Laisser vide pour générer automatiquement.'),
                Select::make('patient_id')
                    ->label('Patient')
                    ->relationship('patient', 'identifiant')
                    ->getOptionLabelFromRecordUsing(fn (Patient $record): string => $record->libelle)
                    ->searchable(['identifiant', 'nom', 'prenom'])
                    ->preload()
                    ->required(),
                Select::make('region_id')
                    ->label('Région')
                    ->relationship('region', 'nom')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('lieu_prise_en_charge')
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
                Select::make('equipe_mobile_id')
                    ->label('Équipe mobile')
                    ->relationship('equipeMobile', 'nom')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()?->equipe_mobile_id)
                    ->disabled(fn () => ! $peutGererToutesLesEquipes())
                    ->dehydrated()
                    ->helperText(fn (): ?string => $peutGererToutesLesEquipes()
                        ? null
                        : 'Une équipe mobile ne peut créer une intervention que pour elle-même.'),
                Select::make('ambulance_id')
                    ->label('Ambulance')
                    ->relationship('ambulance', 'immatriculation')
                    ->searchable()
                    ->preload(),
                Select::make('structure_destination_id')
                    ->label('Structure de destination')
                    ->relationship('structureDestination', 'nom')
                    ->searchable()
                    ->preload(),
                Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'demande_recue' => 'Demande reçue',
                        'intervention_affectee' => 'Intervention affectée',
                        'equipe_en_route' => 'Équipe en route',
                        'patient_pris_en_charge' => 'Patient pris en charge',
                        'recherche_structure' => 'Recherche de structure',
                        'structure_selectionnee' => 'Structure sélectionnée',
                        'en_route_structure' => 'En route vers la structure',
                        'patient_arrive' => 'Patient arrivé',
                        'intervention_terminee' => 'Intervention terminée',
                        'annulee' => 'Annulée',
                    ])
                    ->required()
                    ->default('demande_recue'),
                DateTimePicker::make('demande_recue_at')
                    ->label('Demande reçue le'),
                DateTimePicker::make('affectee_at')
                    ->label('Affectée le'),
                DateTimePicker::make('equipe_en_route_at')
                    ->label('Équipe en route le'),
                DateTimePicker::make('patient_pris_en_charge_at')
                    ->label('Patient pris en charge le'),
                DateTimePicker::make('structure_selectionnee_at')
                    ->label('Structure sélectionnée le'),
                DateTimePicker::make('en_route_structure_at')
                    ->label('En route vers la structure le'),
                DateTimePicker::make('patient_arrive_at')
                    ->label('Patient arrivé le'),
                DateTimePicker::make('intervention_terminee_at')
                    ->label('Intervention terminée le'),
                DateTimePicker::make('annulee_at')
                    ->label('Annulée le'),
            ]);
    }
}
