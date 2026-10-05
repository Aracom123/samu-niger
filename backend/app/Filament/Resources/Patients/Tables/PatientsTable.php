<?php

namespace App\Filament\Resources\Patients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('identifiant')
                    ->searchable(),
                TextColumn::make('nom')
                    ->searchable(),
                TextColumn::make('prenom')
                    ->label('Prénom')
                    ->searchable(),
                TextColumn::make('sexe')
                    ->searchable(),
                TextColumn::make('age_approximatif')
                    ->label('Âge')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('telephone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('motif')
                    ->searchable(),
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
                TextColumn::make('structureDestination.nom')
                    ->label('Structure de destination')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Enregistré le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
