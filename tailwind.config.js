/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./*.php', './inc/**/*.php', './page-templates/**/*.php', './template-parts/**/*.php', './src/js/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        ink: {
          950: '#08080a',
          900: '#0e0e12',
          850: '#131318',
          800: '#18181f',
          700: '#24242e',
          600: '#34343f',
          500: '#5a5a66',
          400: '#8a8a96',
          300: '#b4b4bf',
          200: '#d9d9e0',
          100: '#f1f1f4',
        },
        paper: '#f2efe8',
        signal: {
          DEFAULT: '#7c3aff',
          300: '#b597ff',
          400: '#9d6bff',
          600: '#6420e8',
          700: '#4f12c4',
        },
        lime: '#d7ff3a',
        live: '#ff3b4e',
      },
      fontFamily: {
        display: ['"Bricolage Grotesque Variable"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        sans: ['"Inter Variable"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      fontSize: {
        'display-xl': ['clamp(3.5rem, 11vw, 11rem)', { lineHeight: '0.86', letterSpacing: '-0.045em' }],
        'display-lg': ['clamp(2.75rem, 7vw, 6.5rem)', { lineHeight: '0.9', letterSpacing: '-0.04em' }],
        'display-md': ['clamp(2rem, 4.5vw, 4rem)', { lineHeight: '0.95', letterSpacing: '-0.035em' }],
        'display-sm': ['clamp(1.5rem, 2.6vw, 2.25rem)', { lineHeight: '1.05', letterSpacing: '-0.025em' }],
      },
      maxWidth: {
        site: '1440px',
      },
      borderRadius: {
        '4xl': '2rem',
      },
      transitionTimingFunction: {
        expo: 'cubic-bezier(0.16, 1, 0.3, 1)',
        quint: 'cubic-bezier(0.83, 0, 0.17, 1)',
      },
      keyframes: {
        marquee: { to: { transform: 'translateX(-50%)' } },
        pulse_live: {
          '0%, 100%': { opacity: 1, transform: 'scale(1)' },
          '50%': { opacity: 0.35, transform: 'scale(0.85)' },
        },
        meter: {
          '0%, 100%': { transform: 'scaleY(0.25)' },
          '20%': { transform: 'scaleY(0.9)' },
          '45%': { transform: 'scaleY(0.5)' },
          '70%': { transform: 'scaleY(1)' },
        },
        spin_slow: { to: { transform: 'rotate(360deg)' } },
        float: {
          '0%, 100%': { transform: 'translate3d(0,0,0)' },
          '50%': { transform: 'translate3d(0,-14px,0)' },
        },
      },
      animation: {
        marquee: 'marquee 38s linear infinite',
        live: 'pulse_live 1.4s ease-in-out infinite',
        meter: 'meter 1.1s ease-in-out infinite',
        'spin-slow': 'spin_slow 18s linear infinite',
        float: 'float 7s ease-in-out infinite',
      },
    },
  },
  plugins: [],
};
