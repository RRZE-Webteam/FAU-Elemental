/* eslint-disable no-console */

import { existsSync } from 'node:fs';
import { execSync } from 'node:child_process';

/**
 * Fail the execution of the current script, outputing an error message and
 * exit with an error code.
 *
 * @param {string}      reason
 * @param {string|null} comment
 * @param {number}      exitCode
 */
export function fail( reason, comment = null, exitCode = 1 ) {
	console.error( `ERROR: ${ reason }` );
	if ( comment ) {
		console.error( `       ${ comment }` );
	}
	console.error( 'Script aborted!' );
	process.exit( exitCode );
}

/**
 * Exit the script successfully with a 0 exit code.
 *
 * @param {string|null} msg
 */
export function success( msg = null ) {
	console.log();
	if ( msg ) {
		console.log( msg );
	} else {
		console.log( 'Script finished successfully!' );
	}
	console.log();
	process.exit( 0 );
}

/**
 * Ensure the script is called from the projects root directory.
 */
export function ensureCorrectCwd() {
	const requiredFiles = [ './package.json', './style.css', './theme.json' ];
	for ( const f of requiredFiles ) {
		if ( ! existsSync( f ) ) {
			fail(
				`Expected to find file ${ f }`,
				'Did you ran this command from the project root directory?'
			);
		}
	}
}

/**
 * Check if a boolen flag was provided in the command arguments.
 *
 * @param {string} flag the flag to check for
 * @return {boolean} true, if the flag is found in the arguments, false if not
 */
export function hasFlag( flag ) {
	flag = flag.trim().toLowerCase();
	return !! process.argv.find( ( x ) => x.trim().toLowerCase() === flag );
}

/**
 * Run a command and fail if it exits with a non-zero code.
 *
 * @param {string} cmd the command to run
 */
export function runCmd( cmd ) {
	try {
		execSync( cmd, { stdio: 'inherit' } );
	} catch {
		fail( `The command "${ cmd }" failed.` );
	}
}
