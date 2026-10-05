<style>
    :root {
        --samu-sidebar-bg: #1E4620;
        --samu-sidebar-bg-active: #2E6631;
        --samu-sidebar-bg-hover: #295C2B;
        --samu-sidebar-text: rgba(255, 255, 255, 0.85);
        --samu-sidebar-text-active: #FFFFFF;
        --samu-sidebar-icon: rgba(255, 255, 255, 0.7);
        --samu-sidebar-icon-active: #FFFFFF;
        --samu-sidebar-group-label: rgba(255, 255, 255, 0.55);
        --samu-sidebar-border: rgba(255, 255, 255, 0.12);
    }

    /* Fond du sidebar — couleur fixe, indépendante du mode clair/sombre */
    .fi-sidebar,
    .fi-sidebar-header {
        background-color: var(--samu-sidebar-bg) !important;
        box-shadow: none !important;
    }

    /* Libellés des groupes de navigation (Référentiels, Structures & capacités, ...) */
    .fi-sidebar-group-label {
        color: var(--samu-sidebar-group-label) !important;
    }

    .fi-sidebar-group-btn .fi-icon {
        color: var(--samu-sidebar-icon) !important;
    }

    /* Liens de navigation : texte et icônes */
    .fi-sidebar-item-label {
        color: var(--samu-sidebar-text) !important;
    }

    .fi-sidebar-item-btn > .fi-icon {
        color: var(--samu-sidebar-icon) !important;
    }

    .fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover {
        background-color: var(--samu-sidebar-bg-hover) !important;
    }

    .fi-sidebar-item-has-url > .fi-sidebar-item-btn:hover .fi-sidebar-item-label {
        color: var(--samu-sidebar-text-active) !important;
    }

    /* Élément actif */
    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background-color: var(--samu-sidebar-bg-active) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-icon {
        color: var(--samu-sidebar-icon-active) !important;
    }

    .fi-sidebar-item.fi-active > .fi-sidebar-item-btn > .fi-sidebar-item-label {
        color: var(--samu-sidebar-text-active) !important;
        font-weight: 600;
    }

    /* Pied du sidebar */
    .fi-sidebar-footer {
        border-color: var(--samu-sidebar-border) !important;
    }

    .fi-sidebar-header-controls {
        --tw-border-opacity: 1;
    }

    .fi-sidebar-header-controls::after {
        border-color: var(--samu-sidebar-border) !important;
    }
</style>
