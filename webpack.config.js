const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const path = require('path');
const fs = require('fs');

// Get all block folders from src directory
const blockFolders = fs.readdirSync(path.resolve(process.cwd(), 'src'))
    .filter(folder => folder.startsWith('fau-'));

// Create entries for each block
const blockEntries = blockFolders.reduce((entries, folder) => {
    return {
        ...entries,
        [`${folder}/index`]: path.resolve(process.cwd(), `src/${folder}/index.js`),
        [`${folder}/style`]: path.resolve(process.cwd(), `src/${folder}/style.scss`),
        [`${folder}/editor`]: path.resolve(process.cwd(), `src/${folder}/editor.scss`),
    };
}, {});

module.exports = {
    ...defaultConfig,
    entry: {
        // Keep existing block entries
        ...defaultConfig.entry,
        // Add all block entries
        ...blockEntries,
        // Add theme style entries
        'css/theme': path.resolve(process.cwd(), 'src/scss/theme.scss'),
        'css/editor-style': path.resolve(process.cwd(), 'src/scss/editor-style.scss'),
    },
    plugins: [
        ...defaultConfig.plugins,
        new RemoveEmptyScriptsPlugin({
            stage: RemoveEmptyScriptsPlugin.STAGE_AFTER_PROCESS_PLUGINS
        })
    ]
}; 