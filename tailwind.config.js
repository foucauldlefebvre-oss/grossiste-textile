import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Livewire/**/*.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                bordeaux: {
                    DEFAULT: '#8B1A1A',
                    dark: '#6B1414',
                    light: '#A52A2A',
                    50: '#FDF2F2',
                    100: '#FCE4E4',
                    200: '#F9CDCD',
                    500: '#8B1A1A',
                    600: '#7A1717',
                    700: '#6B1414',
                    800: '#5A1111',
                    900: '#4A0E0E',
                },
            },
        },
    },
    plugins: [],
};
