<?php

namespace App\Filament\Resources\TypeStructures;

use App\Filament\Resources\TypeStructures\Pages\CreateTypeStructure;
use App\Filament\Resources\TypeStructures\Pages\EditTypeStructure;
use App\Filament\Resources\TypeStructures\Pages\ListTypeStructures;
use App\Filament\Resources\TypeStructures\Schemas\TypeStructureForm;
use App\Filament\Resources\TypeStructures\Tables\TypeStructuresTable;
use App\Models\TypeStructure;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TypeStructureResource extends Resource
{
    protected static ?string $model = TypeStructure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?string $navigationLabel = 'Types de structures';

    protected static string|UnitEnum|null $navigationGroup = 'Référentiels';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'type de structure';

    protected static ?string $pluralModelLabel = 'types de structures';

    public static function form(Schema $schema): Schema
    {
        return TypeStructureForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TypeStructuresTable::configure($table);
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
            'index' => ListTypeStructures::route('/'),
            'create' => CreateTypeStructure::route('/create'),
            'edit' => EditTypeStructure::route('/{record}/edit'),
        ];
    }
}
