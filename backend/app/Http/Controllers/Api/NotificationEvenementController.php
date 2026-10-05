<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationEvenement;
use Illuminate\Http\Request;

class NotificationEvenementController extends Controller
{
    public function index(Request $request)
    {
        return NotificationEvenement::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 20));
    }

    public function update(Request $request, NotificationEvenement $notificationEvenement)
    {
        $this->authorizeProprietaire($request, $notificationEvenement);

        $notificationEvenement->marquerCommeLue();

        return $notificationEvenement;
    }

    private function authorizeProprietaire(Request $request, NotificationEvenement $notification): void
    {
        abort_if($notification->user_id !== $request->user()->id, 403);
    }
}
