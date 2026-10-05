<?php

namespace App\Filament\Resources\Interventions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InterventionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Numéro')
                    ->searchable(),
                TextColumn::make('demande_recue_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('patient.libelle')
                    ->label('Patient')
                    ->searchable(query: fn ($query, string $search) => $query->whereHas(
                        'patient',
                        fn ($q) => $q->where('identifiant', 'like', "%{$search}%")
                            ->orWhere('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%"),
                    )),
                TextColumn::make('region.nom')
                    ->label('Région')
                    ->searchable(),
                TextColumn::make('lieu_prise_en_charge')
                    ->label('Lieu de prise en charge')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('niveau_urgence')
                    ->label('Niveau d\'urgence')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critique' => 'danger',
                        'urgent' => 'warning',
                        'modere' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'critique' => 'Critique',
                        'urgent' => 'Urgent',
                        'modere' => 'Modéré',
                        'faible' => 'Faible',
                        default => $state,
                    }),
                TextColumn::make('equipeMobile.nom')
                    ->label('Équipe mobile')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('structureDestination.nom')
                    ->label('Destination')
                    ->searchable(),
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'intervention_terminee' => 'success',
                        'annulee' => 'danger',
                        'demande_recue' => 'gray',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
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
                        default => $state,
                    })
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
