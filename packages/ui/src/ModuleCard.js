import { ToggleControl, Card, CardBody, CardHeader } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const ModuleCard = ({ title, description, icon, checked, onChange, disabled }) => {
	return (
		<Card style={{ height: '100%', borderRadius: '8px', border: '1px solid #e2e8f0', boxShadow: '0 1px 3px rgba(0,0,0,0.05)' }}>
			<CardHeader style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', padding: '16px 20px', backgroundColor: '#f8fafc' }}>
				<div style={{ display: 'flex', alignItems: 'center', gap: '10px' }}>
					{ icon && <span style={{ fontSize: '20px', color: '#007cba' }}>{ icon }</span> }
					<strong style={{ fontSize: '16px', color: '#0f172a' }}>{ title }</strong>
				</div>
				<ToggleControl
					checked={ !!checked }
					onChange={ onChange }
					disabled={ !!disabled }
					__nextHasNoMarginBottom
				/>
			</CardHeader>
			<CardBody style={{ padding: '16px 20px', color: '#475569', fontSize: '14px', lineHeight: '1.5' }}>
				<p style={{ margin: 0 }}>{ description }</p>
			</CardBody>
		</Card>
	);
};

export default ModuleCard;
