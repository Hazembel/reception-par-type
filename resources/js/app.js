/**
 * app.js — Point d'entrée Vite
 * reception-par-type.ch
 *
 * Charge Alpine.js et les plugins nécessaires.
 * Alpine.js est initialisé AVANT window.load pour éviter le FOUC (Flash Of Unstyled Content).
 */

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

// ── Plugins Alpine.js ─────────────────────────────────────────────────────────
Alpine.plugin(focus);    // Gestion du focus (modales, dropdowns accessibles)
Alpine.plugin(collapse); // Animation d'accordéon (collapse/expand)

// ── Initialisation anticipée du dark mode ─────────────────────────────────────
/**
 * Injecte la classe 'dark' sur <html> AVANT que le DOM soit peint
 * pour éviter le flash de thème clair en mode sombre.
 *
 * Ce script doit être exécuté de manière synchrone dans le <head>,
 * mais ici nous le faisons via Alpine x-data="themeManager()" dans le layout.
 * Pour encore plus de fluidité, ajouter dans <head> :
 *
 * <script>
 *   if (localStorage.theme === 'dark' ||
 *       (!localStorage.theme && matchMedia('(prefers-color-scheme: dark)').matches)) {
 *     document.documentElement.classList.add('dark')
 *   }
 * </script>
 */

// ── Démarrage Alpine ──────────────────────────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();

// ── Utilitaires globaux ───────────────────────────────────────────────────────

/**
 * Formate un nombre avec séparateur d'espace suisse (fr-CH).
 * Disponible globalement pour les templates Alpine.
 * Exemple : formatCH(500000) → "500'000"
 */
window.formatCH = (n) => new Intl.NumberFormat('fr-CH').format(n);

/**
 * Debounce — Réduit la fréquence d'exécution des handlers coûteux.
 * Utilisé pour les champs de recherche en temps réel.
 */
window.debounce = (fn, delay = 300) => {
    let timer;
    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
};
