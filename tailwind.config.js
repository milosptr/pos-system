const path = require('path')
const colors = require('tailwindcss/colors');
const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./index.html",
        './resources/**/*.{vue,js,ts,jsx,tsx}',
        './node_modules/litepie-datepicker/**/*.js',
        "./resources/**/*.blade.php",
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
              primary: '#e86423',
              'litepie-primary': colors.indigo,
              'litepie-secondary': {
                '50': "#f5f5f5",
                '100': "#666666",
                '200': "#666666",
                '300': "#aaaaaa",
                '400': "#d5d5d5",
                '500': "#d2d2d2",
                '600': "#666666",
                '700': "#e5e5e5",
                '800': "#ffffff",
                '900': "#ffffff",
              },
            },
        },
    },

    plugins: [
      require('@tailwindcss/forms'),
      require('@tailwindcss/typography'),
      require('@tailwindcss/aspect-ratio'),
    ],
};
