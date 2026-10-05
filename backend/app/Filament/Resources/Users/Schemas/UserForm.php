<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        $isAdmin = fn (): bool => (bool) auth()->user()?->can('utilisateurs.gerer');

        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom complet')
                    ->required(),
                TextInput::make('email')
                    ->label('Adresse e-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('password')
                    ->label('Mot de passe')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->helperText('Laisser vide pour ne pas modifier le mot de passe.'),
                TextInput::make('telephone')
                    ->label('Téléphone')
                    ->tel(),
                Toggle::make('actif')
                    ->default(true)
                    ->required(),
                Select::make('roles')
                    ->label('Rôles')
                    ->relationship(
                        'roles',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => $isAdmin()
                            ? $query
                            : $query->where('name', 'Agent de structure sanitaire'),
                    )
                    ->helperText(fn (): ?string => $isAdmin() ? null : 'Vous ne pouvez créer que des comptes agent pour votre structure.')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('region_id')
                    ->label('Région')
                    ->relationship('region', 'nom')
                    ->searchable()
                    ->preload()
                    ->visible($isAdmin),
                Select::make('structure_sanitaire_id')
                    ->label('Structure sanitaire')
                    ->relationship('structureSanitaire', 'nom')
                    ->searchable()
                    ->preload()
                    ->default(fn () => auth()->user()?->structure_sanitaire_id)
                    ->disabled(fn () => ! $isAdmin())
                    ->dehydrated(),
                Select::make('equipe_mobile_id')
                    ->label('Équipe mobile')
                    ->relationship('equipeMobile', 'nom')
                    ->searchable()
                    ->preload()
                    ->visible($isAdmin),
            ]);
    }
}
