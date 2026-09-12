import { useState } from '@wordpress/element';
import { PanelBody, SelectControl, RangeControl, ToggleControl, Button, Card, CardBody } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const MediaTab = () => {
	const [ webpQuality, setWebpQuality ] = useState( 82 );
	const [ watermarkEnabled, setWatermarkEnabled ] = useState( false );
	const [ watermarkPosition, setWatermarkPosition ] = useState( 'bottom-right' );
	const [ watermarkOpacity, setWatermarkOpacity ] = useState( 80 );

	return (
		<div className="bankai-tab-media" style={{ marginTop: '20px' }}>
			<Card style={{ borderRadius: '8px', marginBottom: '20px' }}>
				<CardBody>
					<h2 style={{ fontSize: '18px', margin: '0 0 16px 0' }}>{ __( 'تنظیمات پردازش رسانه و تصاویر (Media)', 'bankai-core' ) }</h2>

					<PanelBody title={ __( 'تبدیل خودکار به WebP', 'bankai-core' ) } initialOpen={ true }>
						<RangeControl
							label={ __( 'کیفیت تصاویر WebP', 'bankai-core' ) }
							value={ webpQuality }
							onChange={ setWebpQuality }
							min={ 50 }
							max={ 100 }
						/>
					</PanelBody>

					<PanelBody title={ __( 'تنظیمات واترمارک تصویر', 'bankai-core' ) } initialOpen={ true }>
						<ToggleControl
							label={ __( 'اعمال خودکار واترمارک روی تصاویر جدید', 'bankai-core' ) }
							checked={ watermarkEnabled }
							onChange={ setWatermarkEnabled }
						/>
						<SelectControl
							label={ __( 'موقعیت قرارگیری واترمارک', 'bankai-core' ) }
							value={ watermarkPosition }
							options={[
								{ label: __( 'پایین - راست', 'bankai-core' ), value: 'bottom-right' },
								{ label: __( 'پایین - چپ', 'bankai-core' ), value: 'bottom-left' },
								{ label: __( 'بالا - راست', 'bankai-core' ), value: 'top-right' },
								{ label: __( 'بالا - چپ', 'bankai-core' ), value: 'top-left' },
								{ label: __( 'مرکز', 'bankai-core' ), value: 'center' },
							]}
							onChange={ setWatermarkPosition }
						/>
						<RangeControl
							label={ __( 'میزان شفافیت (Opacity)', 'bankai-core' ) }
							value={ watermarkOpacity }
							onChange={ setWatermarkOpacity }
							min={ 10 }
							max={ 100 }
						/>
					</PanelBody>

					<Button isPrimary style={{ marginTop: '16px' }}>
						{ __( 'ذخیره تنظیمات رسانه', 'bankai-core' ) }
					</Button>
				</CardBody>
			</Card>
		</div>
	);
};

export default MediaTab;
