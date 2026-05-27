import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'sans-serif', ...defaultTheme.fontFamily.sans],
                mono: ['"SF Mono"', 'Monaco', '"Cascadia Code"', '"Roboto Mono"', '"Courier New"', 'monospace'],
            },
            fontSize: {
                'display': ['48px', { lineHeight: '55.2px', fontWeight: '300' }],
                'h2':      ['32px', { lineHeight: '35.2px', fontWeight: '300' }],
                'h3':      ['26px', { lineHeight: '29.12px', fontWeight: '300' }],
                'h4':      ['16px', { lineHeight: '22.4px', fontWeight: '400' }],
                'body':    ['14px', { lineHeight: '21px', fontWeight: '400' }],
                'caption': ['12px', { lineHeight: '16.8px', fontWeight: '400' }],
                'code':    ['13px', { lineHeight: '18.2px', fontWeight: '400' }],
            },
            colors: {
                stripe: {
                    purple:  '#533AFD',
                    'purple-hover': '#4329E8',
                    'purple-active': '#3720D4',
                    'purple-disabled': '#C9C3F0',
                    'purple-tint': '#E8E9FF',
                    'purple-ring': 'rgba(83, 58, 253, 0.1)',
                    navy:    '#061B31',
                    orange:  '#FF6118',
                    slate:   '#273951',
                    'dark-blue': '#0D1738',
                    'navy-slate': '#1A2C44',
                    interactive: '#50617A',
                    'light-slate': '#64748D',
                    border:  '#D4DEE9',
                    'border-hover': '#B8CCDB',
                    surface: '#E5EDF5',
                    bg:      '#F8FAFC',
                    white:   '#FFFFFF',
                },
            },
            borderRadius: {
                'stripe-sm':  '4px',
                'stripe-md':  '5px',
                'stripe-nav': '6px',
            },
            boxShadow: {
                'z1':    '0px 1px 2px rgba(0, 0, 0, 0.04)',
                'z2':    '0px 4px 12px rgba(0, 0, 0, 0.08)',
                'z3':    '0px 10px 40px rgba(0, 0, 0, 0.1)',
                'z4':    '0px 20px 60px rgba(0, 0, 0, 0.15)',
                'focus': '0px 0px 0px 3px rgba(83, 58, 253, 0.1)',
            },
            height: {
                'topbar': '64px',
                'input':  '40px',
            },
            width: {
                'sidebar':          '240px',
                'sidebar-collapsed': '64px',
            },
            spacing: {
                '18': '72px',
                '30': '120px',
            },
        },
    },
    plugins: [],
};
