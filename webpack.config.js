const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const path = require('path');
const fs = require('fs');

// ============================================================================
// DYNAMIC CUSTOM BLOCK DETECTION
// ============================================================================
// Get all custom block folders from components/blocks directory
const customBlockFolders = fs.existsSync(
	path.resolve(process.cwd(), 'components/blocks')
)
	? fs
		.readdirSync(path.resolve(process.cwd(), 'components/blocks'))
		.filter(
			(folder) =>
				fs
					.statSync(
						path.resolve(
							process.cwd(),
							'components/blocks',
							folder
						)
					)
					.isDirectory() && folder.startsWith('fau-')
		)
	: [];

// Create entries for each custom block (WordPress Scripts handles block.json copying)
const customBlockEntries = customBlockFolders.reduce((entries, folder) => {
	const folderPath = path.resolve(
		process.cwd(),
		`components/blocks/${folder}`
	);
	const outputPrefix = `blocks/${folder}`;

	const hasViewScript = fs.existsSync(
		path.resolve(folderPath, 'view.js')
	);
	const hasStyleScss = fs.existsSync(
		path.resolve(folderPath, 'style.scss')
	);
	const hasEditorScss = fs.existsSync(
		path.resolve(folderPath, 'editor.scss')
	);
	const hasIndexJs = fs.existsSync(path.resolve(folderPath, 'index.js'));

	return {
		...entries,
		...(hasIndexJs
			? {
				[`${outputPrefix}/index`]: path.resolve(
					folderPath,
					'index.js'
				),
			}
			: {}),
		...(hasStyleScss
			? {
				[`${outputPrefix}/theme`]: path.resolve(
					folderPath,
					'style.scss'
				),
			}
			: {}),
		...(hasEditorScss
			? {
				[`${outputPrefix}/editor`]: path.resolve(
					folderPath,
					'editor.scss'
				),
			}
			: {}),
		...(hasViewScript
			? {
				[`${outputPrefix}/view`]: path.resolve(
					folderPath,
					'view.js'
				),
			}
			: {}),
	};
}, {});

// Create dynamic copy patterns for block.json and render.php files
const copyPatterns = customBlockFolders.reduce((patterns, folder) => {
	const folderPath = path.resolve(
		process.cwd(),
		`components/blocks/${folder}`
	);

	// Add block.json copy pattern
	if (fs.existsSync(path.resolve(folderPath, 'block.json'))) {
		patterns.push({
			from: `components/blocks/${folder}/block.json`,
			to: `blocks/${folder}/block.json`,
		});
	}

	// Add render.php copy pattern
	if (fs.existsSync(path.resolve(folderPath, 'render.php'))) {
		patterns.push({
			from: `components/blocks/${folder}/render.php`,
			to: `blocks/${folder}/render.php`,
		});
	}

	return patterns;
}, []);

// ============================================================================
// EXTEND DEFAULT WORDPRESS SCRIPTS CONFIG
// ============================================================================
module.exports = {
	...defaultConfig,
	entry: {
		// Keep any existing entries from the default config
		...defaultConfig.entry,

		// ============================================================================
		// MAIN THEME BUNDLE
		// ============================================================================
		// This creates main-theme.css with UI foundation + all component styles (except custom blocks)
		'css/theme': path.resolve(process.cwd(), 'components/ui/theme.scss'),

		// ============================================================================
		// DYNAMIC CUSTOM BLOCKS (Auto-detected from components/blocks/fau-*)
		// ============================================================================
		// Block.json and render.php files are copied via CopyWebpackPlugin below
		...customBlockEntries,

		// ============================================================================
		// EDITOR STYLES BUNDLE
		// ============================================================================
		// This creates editor.css with all theme styles + editor-specific styles
		'css/editor': path.resolve(
			process.cwd(),
			'components/ui/editor/editor.scss'
		),

		// ============================================================================
		// EDITOR WRAPPER STYLES BUNDLE
		// ============================================================================
		// This creates editor-wrapper.css with editor environment styles
		'css/editor-wrapper': path.resolve(
			process.cwd(),
			'components/ui/editor/editor-wrapper.scss'
		),
		// ============================================================================
		// JAVASCRIPT BUNDLES
		// ============================================================================

		// Block Editor Scripts (WordPress admin editor)
		'js/editor': path.resolve(
			process.cwd(),
			'components/ui/editor/editor.js'
		),

		// Frontend View Scripts (individual scripts for interactive features)
		'js/gallery-slider': path.resolve(
			process.cwd(),
			'components/core-blocks/gallery/gallery-slider.js'
		),
		'js/image-aspect-ratio': path.resolve(
			process.cwd(),
			'components/core-blocks/image/image-aspect-ratio.js'
		),
		'js/image-fullscreen': path.resolve(
			process.cwd(),
			'components/core-blocks/image/image-fullscreen.js'
		),
		'js/quote-carousel': path.resolve(
			process.cwd(),
			'components/core-blocks/quote/quote-carousel.js'
		),
		// Add navigation component scripts (unified menu modal system)
		'js/menu-modal': path.resolve(
			process.cwd(),
			'components/ui/navigation/menu-modal.js'
		),

		// Template Part Scripts
		'js/template-parts-post-meta': path.resolve(
			process.cwd(),
			'components/template-parts/post-meta/script.js'
		),
	},

	// ============================================================================
	// PERFORMANCE OPTIMIZATIONS
	// ============================================================================
	optimization: {
		...defaultConfig.optimization,
		splitChunks: {
			...defaultConfig.optimization?.splitChunks,
			cacheGroups: {
				...defaultConfig.optimization?.splitChunks?.cacheGroups,
				// Extract fonts into separate chunk to better manage their size warnings
				fonts: {
					test: /\.(woff|woff2|ttf|eot)$/,
					name: 'fonts',
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},

	// ============================================================================
	// PERFORMANCE BUDGET CONFIGURATION
	// ============================================================================
	// Increase size limits to account for theme fonts and reduce warnings
	performance: {
		...defaultConfig.performance,
		maxAssetSize: 1000000, // 1MB for individual assets (to handle large fonts)
		maxEntrypointSize: 1000000, // 1MB for entrypoints
		hints: 'warning', // Show warnings but don't fail build
	},

	plugins: [
		// Keep all existing plugins from the default config
		...defaultConfig.plugins,
		// Add our custom plugins
		new RemoveEmptyScriptsPlugin({
			stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS,
		}),
		new CopyWebpackPlugin({
			patterns: copyPatterns,
		}),
	],
};
