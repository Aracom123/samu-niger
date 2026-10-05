<?php

namespace App\Filament\Widgets;

use App\Models\Capacite;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class StructuresEnTensionWidget extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.consulter');
    }

    public function getTableHeading(): string
    {
        return 'Structures en tension';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn (): Builder => Capacite::query()
                    ->whereIn('statut_saturation', ['presque_saturee', 'saturee'])
                    ->with('structureSanitaire.region')
                    ->orderBy('lits_disponibles')
            )
            ->columns([
                TextColumn::make('structureSanitaire.nom')
                    ->label('Structure'),
                TextColumn::make('structureSanitaire.region.nom')
                    ->label('Région'),
                TextColumn::make('lits_disponibles')
                    ->label('Lits disponibles')
                    ->numeric(),
                TextColumn::make('lits_totaux')
                    ->label('Lits totaux')
                    ->numeric(),
                TextColumn::make('statut_saturation')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'presque_saturee' => 'warning',
                        'saturee' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'presque_saturee' => 'Presque saturée',
                        'saturee' => 'Saturée',
                        default => $state,
                    }),
                TextColumn::make('updated_at')
                    ->label('Dernière mise à jour')
                    ->since(),
            ])
            ->recordActions([
                Action::make('voir')
                    ->label('Voir')
                    ->url(fn (Capacite $record): string => route('filament.admin.resources.structure-sanitaires.edit', ['record' => $record->structure_sanitaire_id])),
            ])
            ->paginated(false)
            ->emptyStateHeading('Aucune structure en tension')
            ->emptyStateDescription('Toutes les structures disposent d\'une capacité suffisante.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }
}
