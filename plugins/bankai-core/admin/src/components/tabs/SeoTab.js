import { useState } from '@wordpress/element';
import { PanelBody, TextControl, TextareaControl, ToggleControl, Button, Card, CardBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const SeoTab = () => {
	const [ schemaEnabled, setSchemaEnabled ] = useState( true );
	const [ sitemapEnabled, setSitemapEnabled ] = useState( true );
	const [ defaultTitle, setDefaultTitle ] = useState( '%title% - %sitename%' );
	const [ defaultDesc, setDefaultDesc ] = useState( '' );

	return (
		<div className="bankai-tab-seo" style={{ marginTop: '20px' }}>
			<Card style={{ borderRadius: '8px', marginBottom: '20px' }}>
				<CardBody>
					<h2 style={{ fontSize: '18px', margin: '0 0 16px 0' }}>{ __( 'تنظیمات عمومی سئو (SEO)', 'bankai-core' ) }</h2>
					
					<PanelBody title={ __( 'الگوی متاتگ‌های عمومی', 'bankai-core' ) } initialOpen={ true }>
						<TextControl
							label={ __( 'الگوی عنوان صفحات (Title Template)', 'bankai-core' ) }
							value={ defaultTitle }
							onChange={ setDefaultTitle }
							help={ __( 'متغیرهای مجاز: %title%, %sitename%, %sep%', 'bankai-core' ) }
						/>
						<TextareaControl
							label={ __( 'توضیحات متای پیش‌فرض (Default Meta Description)', 'bankai-core' ) }
							value={ defaultDesc }
							onChange={ setDefaultDesc }
							rows={ 3 }
						/>
					</PanelBody>

					<PanelBody title={ __( 'اسکیما و سایت‌مپ XML', 'bankai-core' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'فعال‌سازی ساختار اسکیما (JSON-LD Schema)', 'bankai-core' ) }
							checked={ schemaEnabled }
							onChange={ setSchemaEnabled }
							help={ __( 'تولید خودکار کد اسکیما برای مقالات، صفحات و سازمان', 'bankai-core' ) }
						/>
						<ToggleControl
							label={ __( 'فعال‌سازی نقشه سایت (XML Sitemap)', 'bankai-core' ) }
							checked={ sitemapEnabled }
							onChange={ setSitemapEnabled }
							help={ __( 'آدرس نقشه سایت: /sitemap.xml', 'bankai-core' ) }
						/>
					</PanelBody>

					<Button isPrimary style={{ marginTop: '16px' }}>
						{ __( 'ذخیره تنظیمات سئو', 'bankai-core' ) }
					</Button>
				</CardBody>
			</Card>
		</div>
	);
};

export default SeoTab;
