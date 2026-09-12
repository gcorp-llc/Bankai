import { useState, useEffect } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import DashboardTab from './components/tabs/DashboardTab';

export const App = () => {
	const [ notice, setNotice ] = useState( null );
	const restUrl = ( window.bankaiData && window.bankaiData.restUrl ) || '/bankai/v1';
	const nonce = ( window.bankaiData && window.bankaiData.nonce ) || '';

	useEffect( () => {
		if ( nonce ) {
			apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
		}
	}, [ nonce ] );

	return (
		<div className="bankai-admin-app-wrap" style={{ minHeight: '100vh', backgroundColor: '#080C14' }}>
			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
					style={{ position: 'fixed', top: '40px', right: '20px', zIndex: 99999, maxWidth: '400px' }}
				>
					{ notice.message }
				</Notice>
			) }

			<DashboardTab restUrl={ restUrl } setNotice={ setNotice } />
		</div>
	);
};

export default App;
