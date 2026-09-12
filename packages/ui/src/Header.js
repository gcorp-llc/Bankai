import { __ } from '@wordpress/i18n';

export const Header = ({ title, subtitle }) => {
	return (
		<div className="bankai-ui-header" style={{
			display: 'flex',
			alignItems: 'center',
			justifyContent: 'space-between',
			padding: '16px 24px',
			backgroundColor: '#ffffff',
			borderBottom: '1px solid #e0e0e0',
			marginBottom: '24px',
			borderRadius: '8px',
			boxShadow: '0 1px 3px rgba(0,0,0,0.05)'
		}}>
			<div>
				<h1 style={{ margin: 0, fontSize: '24px', fontWeight: 'bold', color: '#1e1e1e' }}>
					{ title || __('Bankai Core Ecosystem', 'bankai-core') }
				</h1>
				{ subtitle && <p style={{ margin: '4px 0 0 0', color: '#666', fontSize: '14px' }}>{ subtitle }</p> }
			</div>
			<div style={{ textAlign: 'right' }}>
				<span style={{ fontSize: '12px', fontWeight: 600, color: '#007cba', letterSpacing: '0.5px' }}>
					DEVELOPED BY GCORP LLC
				</span>
			</div>
		</div>
	);
};

export default Header;
