/** @type {import('tailwindcss').Config} */

/**
 * Configuration Tailwind CSS — reception-par-type.ch
 *
 * Design System : Nuit Alpine × Précision Helvétique
 * Typographie  : DM Serif Display (headlines) + Inter (data/UI)
 * Palette      : Bleu ASTRA, Nuit alpine, Blanc glacier, Cyan électrique
 */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.js',
    './resources/js/**/*.vue',
  ],

  // Mode sombre basé sur la classe (contrôlé par Alpine.js + préférence système)
  darkMode: 'class',

  theme: {
    extend: {

      // ── Palette de couleurs ─────────────────────────────────────────────────
      colors: {
        // Fond principal (dark)
        'night':    { DEFAULT: '#0A0F1E', 800: '#0D1528', 700: '#111B35', 600: '#162040' },
        // Fond principal (light)
        'glacier':  { DEFAULT: '#F0F4FF', 100: '#FAFBFF', 200: '#EEF2FF' },
        // Accent primaire (bleu ASTRA — immatriculer, officiel)
        'astra':    { DEFAULT: '#2563EB', 400: '#60A5FA', 600: '#1D4ED8', 700: '#1E40AF' },
        // Accent secondaire (cyan électrique — énergie, dynamisme)
        'spark':    { DEFAULT: '#38BDF8', 400: '#7DD3FC', 600: '#0EA5E9' },
        // Texte secondaire
        'slate':    { DEFAULT: '#64748B', 300: '#CBD5E1', 700: '#334155' },
        // Cartes dark (marine profond)
        'marine':   { DEFAULT: '#1E3A5F', 800: '#162B47', 900: '#0F1F33' },
      },

      // ── Typographie ─────────────────────────────────────────────────────────
      fontFamily: {
        'display': ['"DM Serif Display"', 'Georgia', 'serif'],
        'body':    ['Inter', 'system-ui', 'sans-serif'],
        'mono':    ['"JetBrains Mono"', '"Fira Code"', 'monospace'],
      },

      fontSize: {
        // Échelle typographique sur-mesure
        '2xs': ['0.625rem', { lineHeight: '1rem' }],       // 10px
        'hero': ['clamp(2.5rem, 6vw, 4.5rem)', { lineHeight: '1.1', letterSpacing: '-0.03em' }],
        'display': ['clamp(1.75rem, 3vw, 2.5rem)', { lineHeight: '1.2', letterSpacing: '-0.02em' }],
      },

      // ── Animations ──────────────────────────────────────────────────────────
      keyframes: {
        // Apparition vers le haut (Hero text reveal)
        'fade-in-up': {
          '0%':   { opacity: '0', transform: 'translateY(24px)' },
          '100%': { opacity: '1', transform: 'translateY(0)' },
        },
        // Fade simple
        'fade-in': {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' },
        },
        // Rotation lente du compteur kilométrique (élément signature)
        'spin-slow': {
          '0%':   { transform: 'rotate(0deg)' },
          '100%': { transform: 'rotate(360deg)' },
        },
        // Pulsation douce pour les accents
        'glow': {
          '0%, 100%': { boxShadow: '0 0 20px 0px rgba(37, 99, 235, 0.3)' },
          '50%':      { boxShadow: '0 0 40px 8px rgba(56, 189, 248, 0.4)' },
        },
        // Dégradé animé pour le fond Hero
        'gradient-shift': {
          '0%':   { backgroundPosition: '0% 50%' },
          '50%':  { backgroundPosition: '100% 50%' },
          '100%': { backgroundPosition: '0% 50%' },
        },
        // Shimmer pour le skeleton loader
        'shimmer': {
          '0%':   { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        // Illumination du bouton "Débloquer"
        'unlock-glow': {
          '0%, 100%': { boxShadow: '0 0 0 0 rgba(37, 99, 235, 0)' },
          '50%':      { boxShadow: '0 0 24px 4px rgba(56, 189, 248, 0.5)' },
        },
      },

      animation: {
        'fade-in-up':      'fade-in-up 0.6s ease-out forwards',
        'fade-in-up-slow': 'fade-in-up 0.9s ease-out forwards',
        'fade-in':         'fade-in 0.4s ease-out forwards',
        'spin-slow':       'spin-slow 40s linear infinite',
        'glow':            'glow 3s ease-in-out infinite',
        'gradient-shift':  'gradient-shift 8s ease infinite',
        'shimmer':         'shimmer 1.8s linear infinite',
        'unlock-glow':     'unlock-glow 2s ease-in-out infinite',
      },

      // ── Effets visuels ──────────────────────────────────────────────────────
      backdropBlur: {
        'xs': '4px',
        '2xl': '40px',
        '3xl': '64px',
      },

      backgroundSize: {
        '200%': '200% 200%',
        '300%': '300% 300%',
      },

      // ── Delay pour les animations échelonnées ───────────────────────────────
      transitionDelay: {
        '50':  '50ms',
        '150': '150ms',
        '250': '250ms',
        '400': '400ms',
        '600': '600ms',
        '800': '800ms',
      },
    },
  },

  plugins: [
    // Plugin pour les variantes d'animation delay
    function({ addUtilities }) {
      const delays = { 100: '100ms', 200: '200ms', 300: '300ms', 400: '400ms', 500: '500ms', 600: '600ms', 700: '700ms', 800: '800ms' };
      const utilities = {};
      Object.entries(delays).forEach(([key, val]) => {
        utilities[`.animation-delay-${key}`] = { 'animation-delay': val };
      });
      addUtilities(utilities);
    },

    // Plugin opacity-0 initial pour les animations
    function({ addUtilities }) {
      addUtilities({
        '.opacity-0-init': { opacity: '0' },
      });
    },
  ],
};
