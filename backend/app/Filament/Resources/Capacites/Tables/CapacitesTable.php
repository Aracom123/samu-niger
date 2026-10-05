<?php

namespace App\Filament\Resources\Capacites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CapacitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('structureSanitaire.nom')
                    ->label('Structure')
                    ->searchable(),
                TextColumn::make('lits_totaux')
                    ->label('Lits totaux')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lits_occupes')
                    ->label('Lits occupés')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lits_disponibles')
                    ->label('Lits disponibles')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('statut_saturation')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disponible' => 'success',
                        'capacite_reduite' => 'warning',
                        'presque_saturee' => 'warning',
                        'saturee' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'disponible' => 'Disponible',
                        'capacite_reduite' => 'Capacité réduite',
                        'presque_saturee' => 'Presque saturée',
                        'saturee' => 'Saturée',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Dernière mise à jour')
                    ->dateTime()
                    ->sortable(),
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
