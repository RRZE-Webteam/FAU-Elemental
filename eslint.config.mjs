import { createRequire } from 'node:module';

const require = createRequire( import.meta.url );
const wordpress = require( '@wordpress/eslint-plugin' );

export default [
	{
		ignores: [ 'build/**', 'eslint.config.mjs', 'node_modules/**' ],
	},
	...wordpress.configs.recommended,
	{
		languageOptions: {
			globals: {
				document: 'readonly',
				jQuery: 'readonly',
				MutationObserver: 'readonly',
				window: 'readonly',
			},
		},
		rules: {
			'jsdoc/require-param': 'off',
			'jsdoc/no-undefined-types': 'off',
			'no-console': [ 'error', { allow: [ 'warn', 'error' ] } ],
			'no-nested-ternary': 'off',
			'react-hooks/rules-of-hooks': 'off',
			'react-hooks/exhaustive-deps': 'off',
			'@wordpress/no-unsafe-wp-apis': 'off',
			'import/no-extraneous-dependencies': 'off',
			'import/no-unresolved': [ 'error', { ignore: [ '^@wordpress/' ] } ],
		},
	},
	{
		files: [ '**/@(test|__tests__)/**/*.js', '**/?(*.)test.js' ],
		...wordpress.configs[ 'test-unit' ],
	},
];
