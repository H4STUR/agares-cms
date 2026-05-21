import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './storage/framework/views/*.php',
    './resources/views/**/*.blade.php', // Ensure all your Blade files are included
    './resources/js/**/*.js', // Include JS files if you're using Tailwind classes in them
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: ['Figtree', ...defaultTheme.fontFamily.sans],
      },
      spacing: {
        '6': '1.5rem', // 24px padding
        '12': '3rem',  // 48px padding
      },
    },
  },

  plugins: [forms],
};
