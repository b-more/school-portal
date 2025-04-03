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
                primary: {
                    50: '#e6edff',
                    100: '#ccdaff',
                    200: '#99b5ff',
                    300: '#668fff',
                    400: '#3366ff',
                    500: '#1D4ED8', // Royal Blue
                    600: '#0033cc',
                    700: '#002699',
                    800: '#001966',
                    900: '#000d33',
                    950: '#000619',
                },
                secondary: {
                    50: '#fefbf0',
                    100: '#fef7e1',
                    200: '#fcefc3',
                    300: '#fbe7a5',
                    400: '#f9df87',
                    500: '#FFC107', // Yellow
                    600: '#cc9a05',
                    700: '#997404',
                    800: '#664d03',
                    900: '#332601',
                    950: '#1a1301',
                }
            }
        }
    }
}
