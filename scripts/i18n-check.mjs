/* eslint-disable no-console */

import { exec } from 'node:child_process';
import { promisify } from 'node:util';
import { ensureCorrectCwd, fail, success } from './utils.mjs';

ensureCorrectCwd();

// Run make-pot and check output for warnings
const cmd = 'npm run i18n:make-pot';
try {
	const { stdout, stderr } = await promisify( exec )( cmd );
	if ( stderr.toLowerCase().includes( 'warning:' ) ) {
		console.error( stderr );
		fail( `Warnings were thrown during the i18n generation.` );
	}

	console.log( stdout );
} catch {
	fail(
		`The command "${ cmd }" failed.`,
		'Make sure the wp-cli is installed and can be called in this terminal.'
	);
}

success( '===== Translations are ok =====' );
