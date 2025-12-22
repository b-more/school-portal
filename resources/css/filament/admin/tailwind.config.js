import preset from '../../../../vendor/filament/support/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                // St. Francis of Assisi School Corporate Colors
                // Navy Blue Primary, Crimson Red Secondary
                primary: {
                    50: '#f0f4f8',
                    100: '#d9e2ec',
                    200: '#bcccdc',
                    300: '#9fb3c8',
                    400: '#829ab1',
                    500: '#2c5282',     // Lighter navy
                    600: '#1e3a5f',     // Main Navy Blue
                    700: '#1a3352',
                    800: '#162c46',
                    900: '#12243a',
                    950: '#0d1a2a',
                },
                secondary: {
                    50: '#fef2f2',
                    100: '#fee2e2',
                    200: '#fecaca',
                    300: '#fca5a5',
                    400: '#f87171',
                    500: '#dc2626',     // School Crimson Red
                    600: '#b91c1c',
                    700: '#991b1b',
                    800: '#7f1d1d',
                    900: '#6b1a1a',
                    950: '#450a0a',
                },
                // Additional school-themed colors
                'school-navy': '#1e3a5f',
                'school-red': '#dc2626',
                'school-gold': '#d97706',
            }
        }
    }
}
