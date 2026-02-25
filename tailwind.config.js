// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'seaguard': {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
                    950: '#082f49',
                },
                'tide': {
                    'high': '#ef4444',
                    'medium': '#3b82f6',
                    'low': '#10b981',
                }
            },
            animation: {
                'tide-flow': 'tideFlow 3s ease-in-out infinite',
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'wave': 'wave 12s linear infinite',
                'float': 'float 6s ease-in-out infinite',
            },
            keyframes: {
                tideFlow: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-8px)' },
                },
                wave: {
                    '0%': { transform: 'translateX(0) translateZ(0) scaleY(1)' },
                    '50%': { transform: 'translateX(-25%) translateZ(0) scaleY(0.85)' },
                    '100%': { transform: 'translateX(-50%) translateZ(0) scaleY(1)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0) rotate(0deg)' },
                    '50%': { transform: 'translateY(-20px) rotate(5deg)' },
                }
            },
            backgroundImage: {
                'ocean-gradient': 'linear-gradient(135deg, #1e3a8a 0%, #0ea5e9 50%, #22d3ee 100%)',
                'wave-gradient': 'linear-gradient(180deg, transparent 60%, rgba(14, 165, 233, 0.1) 100%)',
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
            boxShadow: {
                'seaguard': '0 10px 40px rgba(14, 165, 233, 0.15)',
                'seaguard-lg': '0 20px 60px rgba(14, 165, 233, 0.25)',
            },
            backdropBlur: {
                xs: '2px',
            },
            transitionProperty: {
                'height': 'height',
                'spacing': 'margin, padding',
            }
        },
    },

    plugins: [
        forms,
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),
    ],
    
    // Dark mode class-based
    darkMode: 'class',
    
    // PurgeCSS settings for production
    safelist: [
        'bg-tide-high',
        'bg-tide-medium',
        'bg-tide-low',
        'text-tide-high',
        'text-tide-medium',
        'text-tide-low',
        'border-tide-high',
        'border-tide-medium',
        'border-tide-low',
        'animate-tide-flow',
        'animate-wave',
    ],
};