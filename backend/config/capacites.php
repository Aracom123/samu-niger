<?php

return [

    /*
     * Seuils (proportion de lits disponibles sur lits totaux) déterminant
     * le statut de saturation d'un service. Paramétrable sans changement
     * de code, conformément au cahier des charges (section 8).
     */
    'seuils' => [
        'saturee' => 0.0,
        'presque_saturee' => 0.10,
        'capacite_reduite' => 0.30,
    ],

    /*
     * Au-delà de ce délai (en minutes) sans mise à jour, une capacité
     * est considérée comme périmée (section 8.2).
     */
    'fraicheur_max_minutes' => 60,

];
