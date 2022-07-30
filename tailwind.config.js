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
              'litepie-secondary': colors.gray,
            },
        },
    },

    plugins: [
      require('@tailwindcss/forms'),
      require('@tailwindcss/typography'),
      require('@tailwindcss/line-clamp'),
      require('@tailwindcss/aspect-ratio'),
    ],
};
