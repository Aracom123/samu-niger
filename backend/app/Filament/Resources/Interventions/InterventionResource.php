<?php

namespace App\Filament\Resources\Interventions;

use App\Filament\Resources\Interventions\Pages\CreateIntervention;
use App\Filament\Resources\Interventions\Pages\EditIntervention;
use App\Filament\Resources\Interventions\Pages\ListInterventions;
use App\Filament\Resources\Interventions\Schemas\InterventionForm;
use App\Filament\Resources\Interventions\Tables\InterventionsTable;
use App\Models\Intervention;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class InterventionResource extends Resource
{
    protected static ?string $model = Intervention::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?string $navigationLabel = 'Interventions';

    protected static string|UnitEnum|null $navigationGroup = 'Opérations SAMU';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'intervention';

    protected static ?string $pluralModelLabel = 'interventions';

    public static function form(Schema $schema): Schema
    {
        return InterventionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InterventionsTable::configure($table);
    }

    /**
     * Portée des données selon le rôle (section 20) : une équipe mobile ne voit
     * que les interventions qui lui sont affectées ; un responsable régional,
     * que celles de sa région.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user === null || $user->can('interventions.gerer')) {
            return $query;
        }

        if ($user->hasRole('Équipe mobile SAMU')) {
            return $query->where('equipe_mobile_id', $user->equipe_mobile_id);
        }

        if ($user->hasRole('Responsable régional') && $user->region_id) {
            return $query->where('region_id', $user->region_id);
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
            'index' => ListInterventions::route('/'),
            'create' => CreateIntervention::route('/create'),
            'edit' => EditIntervention::route('/{record}/edit'),
        ];
    }
}
