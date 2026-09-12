import { useState } from '@wordpress/element';
import { PanelBody, ToggleControl, Button, Card, CardBody, Notice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const SpeedTab = () => {
	const [ minifyCss, setMinifyCss ] = useState( true );
	const [ deferJs, setDeferJs ] = useState( true );
	const [ lazyLoad, setLazyLoad ] = useState( true );
	const [ notice, setNotice ] = useState( null );

	const handlePurgeCache = () => {
		setNotice( { status: 'success', message: __( 'کش سیستم با موفقیت پاک‌سازی شد.', 'bankai-core' ) } );
	};

	return (
		<div className="bankai-tab-speed" style={{ marginTop: '20px' }}>
			{ notice && (
				<Notice status={ notice.status } onRemove={ () => setNotice( null ) } style={{ marginBottom: '16px' }}>
					{ notice.message }
				</Notice>
			) }

			<Card style={{ borderRadius: '8px', marginBottom: '20px' }}>
				<CardBody>
					<h2 style={{ fontSize: '18px', margin: '0 0 16px 0' }}>{ __( 'تنظیمات بهینه‌سازی و سرعت (Speed)', 'bankai-core' ) }</h2>

					<PanelBody title={ __( 'بهینه‌سازی دارایی‌ها (Asset Optimization)', 'bankai-core' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'مینیفای فایل‌های CSS و JS', 'bankai-core' ) }
							checked={ minifyCss }
							onChange={ setMinifyCss }
						/>
						<ToggleControl
							label={ __( 'بارگذاری غیرهمزمان اسکریپت‌ها (Defer JS)', 'bankai-core' ) }
							checked={ deferJs }
							onChange={ setDeferJs }
						/>
						<ToggleControl
							label={ __( 'بارگذاری تنبل تصاویر و ویدیوها (Lazy Loading)', 'bankai-core' ) }
							checked={ lazyLoad }
							onChange={ setLazyLoad }
						/>
					</PanelBody>

					<div style={{ marginTop: '20px', display: 'flex', gap: '12px' }}>
						<Button isPrimary>{ __( 'ذخیره تنظیمات سرعت', 'bankai-core' ) }</Button>
						<Button isSecondary onClick={ handlePurgeCache }>{ __( 'پاک‌سازی کامل کش', 'bankai-core' ) }</Button>
					</div>
				</CardBody>
			</Card>
		</div>
	);
};

export default SpeedTab;
