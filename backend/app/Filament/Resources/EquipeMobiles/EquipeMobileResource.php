<?php

namespace App\Filament\Resources\EquipeMobiles;

use App\Filament\Resources\EquipeMobiles\Pages\CreateEquipeMobile;
use App\Filament\Resources\EquipeMobiles\Pages\EditEquipeMobile;
use App\Filament\Resources\EquipeMobiles\Pages\ListEquipeMobiles;
use App\Filament\Resources\EquipeMobiles\Schemas\EquipeMobileForm;
use App\Filament\Resources\EquipeMobiles\Tables\EquipeMobilesTable;
use App\Models\EquipeMobile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class EquipeMobileResource extends Resource
{
    protected static ?string $model = EquipeMobile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Équipes mobiles';

    protected static string|UnitEnum|null $navigationGroup = 'Opérations SAMU';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'équipe mobile';

    protected static ?string $pluralModelLabel = 'équipes mobiles';

    public static function form(Schema $schema): Schema
    {
        return EquipeMobileForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EquipeMobilesTable::configure($table);
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
            'index' => ListEquipeMobiles::route('/'),
            'create' => CreateEquipeMobile::route('/create'),
            'edit' => EditEquipeMobile::route('/{record}/edit'),
        ];
    }
}
