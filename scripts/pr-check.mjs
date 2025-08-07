import { ensureCorrectCwd, hasFlag, runCmd, success } from './utils.mjs';

ensureCorrectCwd();

const noWpScripts = hasFlag( '--no-wp' );

runCmd( 'npm run format' );
runCmd( 'npm run lint:js' );
runCmd( 'npm run lint:css' );
if ( ! noWpScripts ) {
	runCmd( 'npm run i18n:check' );
}
runCmd( 'npm run build -- --fail-on-warnings' );

success( '<<<<<<<<<<< Ready to create a PR! >>>>>>>>>>>' );
