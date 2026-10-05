<?php

namespace App\Filament\Resources\Orientations;

use App\Filament\Resources\Orientations\Pages\CreateOrientation;
use App\Filament\Resources\Orientations\Pages\EditOrientation;
use App\Filament\Resources\Orientations\Pages\ListOrientations;
use App\Filament\Resources\Orientations\Schemas\OrientationForm;
use App\Filament\Resources\Orientations\Tables\OrientationsTable;
use App\Models\Orientation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class OrientationResource extends Resource
{
    protected static ?string $model = Orientation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?string $navigationLabel = 'Orientations';

    protected static string|UnitEnum|null $navigationGroup = 'Opérations SAMU';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'orientation';

    protected static ?string $pluralModelLabel = 'orientations';

    public static function form(Schema $schema): Schema
    {
        return OrientationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrientationsTable::configure($table);
    }

    /**
     * Portée des données selon le rôle (section 20) : un responsable/agent de
     * structure ne voit que les orientations qui la concernent ; une équipe
     * mobile, que celles de ses propres interventions.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user === null || $user->can('orientations.suivre')) {
            return $query;
        }

        if ($user->hasAnyRole(['Responsable de structure sanitaire', 'Agent de structure sanitaire'])) {
            return $query->where('structure_sanitaire_id', $user->structure_sanitaire_id);
        }

        if ($user->hasRole('Équipe mobile SAMU')) {
            return $query->whereHas(
                'intervention',
                fn (Builder $q) => $q->where('equipe_mobile_id', $user->equipe_mobile_id),
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
            'index' => ListOrientations::route('/'),
            'create' => CreateOrientation::route('/create'),
            'edit' => EditOrientation::route('/{record}/edit'),
        ];
    }
}
