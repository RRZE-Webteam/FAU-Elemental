const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        // Keep existing block entries
        ...defaultConfig.entry,
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