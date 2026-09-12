import { render } from '@wordpress/element';
import App from './App';

document.addEventListener( 'DOMContentLoaded', () => {
	const container = document.getElementById( 'bankai-admin-root' );
	if ( container ) {
		render( <App />, container );
	}
} );
