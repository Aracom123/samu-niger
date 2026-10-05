<?php

namespace App\Filament\Resources\StructureSanitaires;

use App\Filament\Resources\StructureSanitaires\Pages\CreateStructureSanitaire;
use App\Filament\Resources\StructureSanitaires\Pages\EditStructureSanitaire;
use App\Filament\Resources\StructureSanitaires\Pages\ListStructureSanitaires;
use App\Filament\Resources\StructureSanitaires\Schemas\StructureSanitaireForm;
use App\Filament\Resources\StructureSanitaires\Tables\StructureSanitairesTable;
use App\Models\StructureSanitaire;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StructureSanitaireResource extends Resource
{
    protected static ?string $model = StructureSanitaire::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Structures sanitaires';

    protected static string|UnitEnum|null $navigationGroup = 'Structures & capacités';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'structure sanitaire';

    protected static ?string $pluralModelLabel = 'structures sanitaires';

    public static function form(Schema $schema): Schema
    {
        return StructureSanitaireForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StructureSanitairesTable::configure($table);
    }

    /**
     * Portée des données selon le rôle (section 20 du cahier des charges) :
     * un responsable régional ne voit que sa région, un responsable/agent
     * de structure ne voit que sa propre structure.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user === null || $user->can('structures.gerer')) {
            return $query;
        }

        if ($user->hasRole('Responsable régional') && $user->region_id) {
            return $query->where('region_id', $user->region_id);
        }

        if ($user->structure_sanitaire_id) {
            return $query->where('id', $user->structure_sanitaire_id);
        }

        // Responsable SAMU, équipe mobile : lecture globale nécessaire pour
        // suivre les disponibilités et orienter les patients (section 11).
        if ($user->can('capacite.consulter')) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
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
            'index' => ListStructureSanitaires::route('/'),
            'create' => CreateStructureSanitaire::route('/create'),
            'edit' => EditStructureSanitaire::route('/{record}/edit'),
        ];
    }
}
