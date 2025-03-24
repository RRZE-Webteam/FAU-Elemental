const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const RemoveEmptyScriptsPlugin = require( 'webpack-remove-empty-scripts' );
const path = require( 'path' );
const fs = require( 'fs' );

// Get all block folders from src/blocks directory
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

const editorScripts = [
	path.resolve( process.cwd(), 'src/editor/editor.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-group/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-button/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-heading/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-paragraph/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-list/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-table/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-image/index.js' ),
];

module.exports = {
	...defaultConfig,
	entry: {
		// Keep existing block entries
		...defaultConfig.entry,
		// Add all block entries
		...blockEntries,
		// Add theme styles
		'css/theme': path.resolve( process.cwd(), 'src/theme.scss' ),
		// Add block editor styles
		'css/editor': path.resolve( process.cwd(), 'src/editor/editor.scss' ),
		// Add block editor scripts
		'js/editor': editorScripts,
		// Add admin styles
		'css/admin': path.resolve( process.cwd(), 'src/admin/admin.scss' ),
		// Add admin scripts
		'js/admin': path.resolve( process.cwd(), 'src/admin/admin.js' ),
		// Add the editor wrapper styles
		'css/editor-wrapper': path.resolve(
			process.cwd(),
			'src/editor/editor-wrapper.scss'
		),
		// Add the image fullscreen script
		'js/image-fullscreen': path.resolve(
			process.cwd(),
			'src/blocks/core-image/image-fullscreen.js'
		),
	},
	plugins: [
		...defaultConfig.plugins,
		new RemoveEmptyScriptsPlugin( {
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		} ),
	],
};
