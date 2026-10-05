<?php

namespace App\Filament\Widgets;

use App\Models\Capacite;
use App\Models\Intervention;
use App\Models\StructureSanitaire;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SamuApercuWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('dashboard.consulter');
    }

    protected function getStats(): array
    {
        $interventionsAujourdHui = Intervention::whereDate('created_at', today())->count();
        $interventionsEnCours = Intervention::whereNotIn('statut', ['intervention_terminee', 'annulee'])->count();

        $totalStructures = StructureSanitaire::where('statut', 'active')->count();
        $structuresSaturees = Capacite::where('statut_saturation', 'saturee')->count();

        $litsDisponibles = Capacite::sum('lits_disponibles');
        $litsTotaux = Capacite::sum('lits_totaux');

        return [
            Stat::make('Interventions aujourd\'hui', $interventionsAujourdHui)
                ->description($interventionsEnCours.' en cours')
                ->descriptionIcon('heroicon-m-truck')
                ->color('primary'),

            Stat::make('Structures opérationnelles', $totalStructures)
                ->description($structuresSaturees.' en saturation')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color($structuresSaturees > 0 ? 'danger' : 'success'),

            Stat::make('Lits disponibles', $litsDisponibles)
                ->description('sur '.$litsTotaux.' lits au total')
                ->descriptionIcon('heroicon-m-heart')
                ->color($litsDisponibles > 0 ? 'success' : 'danger'),
        ];
    }
}
