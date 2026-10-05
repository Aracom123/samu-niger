<?php

namespace App\Filament\Resources\Orientations\Tables;

use App\Models\Orientation;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrientationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('intervention.numero')
                    ->label('Intervention')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('structureSanitaire.nom')
                    ->label('Structure sanitaire')
                    ->searchable(),
                TextColumn::make('statut')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'acceptee' => 'success',
                        'refusee' => 'danger',
                        default => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'proposee' => 'Proposée',
                        'acceptee' => 'Acceptée',
                        'refusee' => 'Refusée',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('motif_refus')
                    ->label('Motif du refus')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.name')
                    ->label('Créée par')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('accepter')
                    ->label('Accepter')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (Orientation $record): bool => $record->statut === 'proposee'
                        && auth()->user()->can('update', $record))
                    ->requiresConfirmation()
                    ->action(function (Orientation $record) {
                        $record->confirmerAccueil('acceptee', null, auth()->id());

                        Notification::make()->title('Accueil confirmé')->success()->send();
                    }),
                Action::make('refuser')
                    ->label('Refuser')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (Orientation $record): bool => $record->statut === 'proposee'
                        && auth()->user()->can('update', $record))
                    ->schema([
                        Textarea::make('motif')
                            ->label('Motif du refus')
                            ->required(),
                    ])
                    ->action(function (Orientation $record, array $data) {
                        $record->confirmerAccueil('refusee', $data['motif'], auth()->id());

                        Notification::make()->title('Accueil refusé')->warning()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
