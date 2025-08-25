/**
 * This is a Webpack Plugin that creates a mapping file for the wp-cli tool i18n make-json.
 * This mapping file is used to create the right JED translation files for the gutenberg blocks and other
 * client side scripts.
 *
 * It is based on the following Plugin:
 * https://github.com/cpiber/webpack-bundle-output/blob/27cdfe5541bb8fe47929bd45d6d99a10089035a5/BundleOutputPlugin.js
 * It was adopted to a MJS file and to support windows paths.
 */

import { relative, join } from 'path';
import { cwd } from 'process';

export class I18nMapPlugin {
	constructor() {
		this.options = {
			cwd: cwd(),
			output: 'map.json',
		};
	}

	/**
	 * @param {import('webpack').Compiler} compiler
	 */
	apply( compiler ) {
		const pluginName = I18nMapPlugin.name;
		const { webpack } = compiler;
		const { Compilation, NormalModule } = webpack;
		const { RawSource } = webpack.sources;

		compiler.hooks.thisCompilation.tap( pluginName, ( compilation ) => {
			const filesMap = {}; // Store source-to-output mapping
			const outputPath = compilation.outputOptions.path;

			/**
			 * Adds a mapping from source file to output file.
			 */
			const addMapping = ( sourcePath, outputFile ) => {
				if ( ! sourcePath ) {
					return;
				}
				const relativeSource = relative(
					this.options.cwd,
					sourcePath
				).replaceAll( '\\', '/' );
				filesMap[ relativeSource ] =
					filesMap[ relativeSource ] || new Set();
				filesMap[ relativeSource ].add(
					relative(
						this.options.cwd,
						join( outputPath, outputFile )
					).replaceAll( '\\', '/' )
				);
			};

			/**
			 * Recursively processes modules in a chunk.
			 */
			const processModule = ( chunk, module ) => {
				if ( module instanceof NormalModule && module.resource ) {
					chunk.files.forEach( ( outputFile ) =>
						addMapping( module.resource, outputFile )
					);
				}

				// Ensure dependencies of entry points are also mapped
				if ( module.modules ) {
					module.modules.forEach( ( subModule ) =>
						processModule( chunk, subModule )
					);
				}
			};

			compilation.hooks.processAssets.tap(
				{
					name: pluginName,
					stage: Compilation.PROCESS_ASSETS_STAGE_SUMMARIZE,
				},
				() => {
					compilation.chunks.forEach( ( chunk ) => {
						compilation.chunkGraph
							.getChunkModules( chunk )
							.forEach( ( module ) =>
								processModule( chunk, module )
							);
					} );

					// Convert Set to array for JSON output
					const finalMap = Object.fromEntries(
						Object.entries( filesMap ).map( ( [ key, value ] ) => [
							key,
							[ ...value ],
						] )
					);

					compilation.emitAsset(
						this.options.output,
						new RawSource( JSON.stringify( finalMap, null, 2 ) )
					);
				}
			);
		} );
	}
}
