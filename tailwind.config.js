/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./src/**/*.php'],
  theme: {
    extend: {
      colors: {
        ink: '#2b3a67',
        brand: '#2d6cdf',
        'brand-light': '#eef3ff',
        canvas: '#f5f6fb'
      }
    }
  },
  plugins: []
};
