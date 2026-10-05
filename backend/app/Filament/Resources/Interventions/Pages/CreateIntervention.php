<?php

namespace App\Filament\Resources\Interventions\Pages;

use App\Filament\Resources\Interventions\InterventionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntervention extends CreateRecord
{
    protected static string $resource = InterventionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Une équipe mobile ne peut créer une intervention que pour elle-même.
        if (! $user->can('interventions.gerer')) {
            $data['equipe_mobile_id'] = $user->equipe_mobile_id;
        }

        return $data;
    }
}
