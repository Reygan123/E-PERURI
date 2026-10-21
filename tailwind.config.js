import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                ptkp: {
                    navy: '#20288E',
                    violet: '#D861FF',
                    deep: '#141A5C',
                    mist: '#EEF0FB',
                    border: 'rgba(32, 40, 142, 0.14)',
                    text: '#20288E',
                    body: '#1A1A2E',
                    success: '#166534',
                    'success-bg': '#DCFCE7',
                    warning: '#854D0E',
                    'warning-bg': '#FEF9C3',
                    error: '#991B1B',
                    'error-bg': '#FEE2E2',
                    neutral: '#475569',
                    'neutral-bg': '#F1F5F9',
                    'border-subtle': 'rgba(32, 40, 142, 0.08)',
                },
            },
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                body: ['Inter', ...defaultTheme.fontFamily.sans],
                heading: ['Lexend', ...defaultTheme.fontFamily.sans],
            },
            borderRadius: {
                xs: '4px',
                sm: '6px',
                md: '8px',
                lg: '12px',
                xl: '16px',
                pill: '999px',
            },
            boxShadow: {
                flat: '0 0 0 1px rgba(32, 40, 142, 0.12)',
                card: '0 1px 3px rgba(20, 26, 92, 0.04), 0 1px 2px rgba(20, 26, 92, 0.02)',
                raised: '0 4px 16px rgba(20, 26, 92, 0.08)',
            },
        },
    },

    plugins: [forms],
};
