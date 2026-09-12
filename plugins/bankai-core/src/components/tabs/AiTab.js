import { useState } from '@wordpress/element';
import { PanelBody, SelectControl, TextControl, Button, Card, CardBody, Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const AiTab = () => {
	const [ provider, setProvider ] = useState( 'openai' );
	const [ apiKey, setApiKey ] = useState( '' );
	const [ loading, setLoading ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const restUrl = ( window.bankaiData && window.bankaiData.restUrl ) || '/bankai/v1';

	const handleSaveKey = async () => {
		if ( ! apiKey ) {
			setNotice( { status: 'error', message: __( 'لطفاً کلید API را وارد کنید.', 'bankai-core' ) } );
			return;
		}

		setLoading( true );
		setNotice( null );

		try {
			const res = await apiFetch( {
				url: `${restUrl}/ai/keys`,
				method: 'POST',
				data: { provider, key: apiKey },
			} );

			if ( res && res.success ) {
				setNotice( { status: 'success', message: res.message || __( 'کلید API با موفقیت و به‌صورت رمزنگاری‌شده ذخیره شد.', 'bankai-core' ) } );
				setApiKey( '' );
			}
		} catch ( err ) {
			setNotice( { status: 'error', message: err.message || __( 'خطا در ذخیره‌سازی کلید API.', 'bankai-core' ) } );
		} finally {
			setLoading( false );
		}
	};

	return (
		<div className="bankai-tab-ai" style={{ marginTop: '20px' }}>
			{ notice && (
				<Notice status={ notice.status } onRemove={ () => setNotice( null ) } style={{ marginBottom: '16px' }}>
					{ notice.message }
				</Notice>
			) }

			<Card style={{ borderRadius: '8px', marginBottom: '20px' }}>
				<CardBody>
					<h2 style={{ fontSize: '18px', margin: '0 0 16px 0' }}>{ __( 'تنظیمات ماژول هوش مصنوعی (AI)', 'bankai-core' ) }</h2>

					<PanelBody title={ __( 'مدیریت کلیدهای سرویس‌های AI (AES-256-GCM)', 'bankai-core' ) } initialOpen={ true }>
						<SelectControl
							label={ __( 'انتخاب ارائه دهنده سرویس AI', 'bankai-core' ) }
							value={ provider }
							options={[
								{ label: 'OpenAI (ChatGPT)', value: 'openai' },
								{ label: 'Google Gemini', value: 'gemini' },
								{ label: 'Anthropic Claude', value: 'claude' },
								{ label: 'DeepSeek', value: 'deepseek' },
								{ label: 'OpenRouter', value: 'openrouter' },
							]}
							onChange={ setProvider }
						/>

						<TextControl
							label={ __( 'کلید API (API Key)', 'bankai-core' ) }
							value={ apiKey }
							onChange={ setApiKey }
							type="password"
							help={ __( 'کلید وارد شده به‌صورت رمزنگاری‌شده AES-256-GCM ذخیره می‌گردد.', 'bankai-core' ) }
						/>
					</PanelBody>

					<div style={{ marginTop: '20px', display: 'flex', alignItems: 'center', gap: '12px' }}>
						<Button isPrimary onClick={ handleSaveKey } disabled={ loading }>
							{ __( 'ذخیره کلید API', 'bankai-core' ) }
						</Button>
						{ loading && <Spinner /> }
					</div>
				</CardBody>
			</Card>
		</div>
	);
};

export default AiTab;
