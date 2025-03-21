const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );
const fs = require( 'fs' );

// Get all block folders from src directory
const blockFolders = fs
	.readdirSync( path.resolve( process.cwd(), 'src' ) )
	.filter( ( folder ) => folder.startsWith( 'fau-' ) );

// Create entries for each block
const blockEntries = blockFolders.reduce( ( entries, folder ) => {
	return {
		...entries,
		[ `${ folder }/index` ]: path.resolve(
			process.cwd(),
			`src/${ folder }/index.js`
		),
		[ `${ folder }/style` ]: path.resolve(
			process.cwd(),
			`src/${ folder }/style.scss`
		),
		[ `${ folder }/editor` ]: path.resolve(
			process.cwd(),
			`src/${ folder }/editor.scss`
		),
	};
}, {} );

const themeStyles = [
	path.resolve( process.cwd(), 'src/scss/theme.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-button.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-paragraph.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-list.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-heading.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-table.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-image.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-code.scss' ),
	path.resolve( process.cwd(), 'src/scss/core-verse.scss' ),
];

const editorStyles = [
	...themeStyles,
	path.resolve( process.cwd(), 'src/scss/editor.scss' ),
];

const editorScripts = [
	path.resolve( process.cwd(), 'src/js/editor.js' ),
	path.resolve( process.cwd(), 'src/js/core-button.js' ),
	path.resolve( process.cwd(), 'src/js/core-paragraph.js' ),
	path.resolve( process.cwd(), 'src/js/core-list.js' ),
	path.resolve( process.cwd(), 'src/js/core-table.js' ),
	path.resolve( process.cwd(), 'src/js/core-image.js' ),
	path.resolve( process.cwd(), 'src/js/core-verse.js' ),
];

module.exports = {
	...defaultConfig,
	entry: {
		// Keep existing block entries
		...defaultConfig.entry,
		// Add all block entries
		...blockEntries,
		// Add theme styles
		'css/theme': themeStyles,
		// Add block editor styles
		'css/editor': editorStyles,
		// Add block editor scripts
		'js/editor': editorScripts,
		// Add the editor wrapper styles
		'css/editor-wrapper': path.resolve(
			process.cwd(),
			'src/scss/editor-wrapper.scss'
		),
		// Add the image fullscreen script
		'js/image-fullscreen': path.resolve(
			process.cwd(),
			'src/js/image-fullscreen.js'
		),
	},
	plugins: [
		...defaultConfig.plugins,
		new RemoveEmptyScriptsPlugin( {
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		} ),
	],
};
