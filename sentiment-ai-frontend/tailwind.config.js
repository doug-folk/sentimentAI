/** @type {import('tailwindcss').Config} */
import tailwindcss from '@tailwindcss/postcss'; // Adicione esta linha

export default {
  content: [
    "./index.html",
    "./src/**/*.{js,ts,jsx,tsx}"
  ],
  theme: {
    extend: {},
  },
  plugins: [tailwindcss()], 
}
