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
	const folderPath = path.resolve( process.cwd(), `src/${ folder }` );
	const hasViewScript = fs.existsSync(
		path.resolve( folderPath, 'view.js' )
	);

	return {
		...entries,
		[ `${ folder }/index` ]: path.resolve( folderPath, 'index.js' ),
		[ `${ folder }/style` ]: path.resolve( folderPath, 'style.scss' ),
		[ `${ folder }/editor` ]: path.resolve( folderPath, 'editor.scss' ),
		...( hasViewScript
			? { [ `${ folder }/view` ]: path.resolve( folderPath, 'view.js' ) }
			: {} ),
	};
}, {} );

const editorScripts = [
	path.resolve( process.cwd(), 'src/editor/editor.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-button/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-details/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-file/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-group/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-image/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-list/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-media-text/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-paragraph/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-quote/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-separator/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-table/index.js' ),
	path.resolve( process.cwd(), 'src/blocks/core-tag-cloud/index.js' ),
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
