<?php

namespace App\Filament\Resources\Capacites;

use App\Filament\Resources\Capacites\Pages\CreateCapacite;
use App\Filament\Resources\Capacites\Pages\EditCapacite;
use App\Filament\Resources\Capacites\Pages\ListCapacites;
use App\Filament\Resources\Capacites\Schemas\CapaciteForm;
use App\Filament\Resources\Capacites\Tables\CapacitesTable;
use App\Models\Capacite;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CapaciteResource extends Resource
{
    protected static ?string $model = Capacite::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $navigationLabel = 'Capacités';

    protected static string|UnitEnum|null $navigationGroup = 'Structures & capacités';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'capacité';

    protected static ?string $pluralModelLabel = 'capacités';

    public static function form(Schema $schema): Schema
    {
        return CapaciteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CapacitesTable::configure($table);
    }

    /**
     * Portée des données selon le rôle (section 20) : un responsable/agent de
     * structure ne voit que la capacité de sa structure ; un responsable
     * régional, que celles de sa région. Les autres profils habilités à
     * consulter les capacités (Responsable SAMU, Équipe mobile) voient tout,
     * en lecture, pour orienter les patients.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user === null || $user->can('structures.gerer')) {
            return $query;
        }

        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $query->where('structure_sanitaire_id', $user->structure_sanitaire_id);
        }

        if ($user->hasRole('Responsable régional') && $user->region_id) {
            return $query->whereHas(
                'structureSanitaire',
                fn (Builder $q) => $q->where('region_id', $user->region_id),
            );
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCapacites::route('/'),
            'create' => CreateCapacite::route('/create'),
            'edit' => EditCapacite::route('/{record}/edit'),
        ];
    }
}
