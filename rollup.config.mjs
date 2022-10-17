import { terser } from '@wwa/rollup-plugin-terser';

/**
 * @type {import('rollup').RollupOptions[]}
 */
const config = [
    {
        input: {
            'yotp-login': 'assets/yotp-login.js',
            'yotp-user': 'assets/yotp-user.js',
        },
        output: {
            dir: 'assets',
            preserveModules: true,
            entryFileNames: '[name].min.js',
            format: 'esm',
            plugins: [
                terser(),
            ],
            compact: true,
            sourcemap: 'hidden',
            strict: false,
        },
        strictDeprecations: true,
    }
];

export default config;
