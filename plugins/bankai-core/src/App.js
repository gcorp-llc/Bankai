import { useState, useEffect } from '@wordpress/element';
import { TabPanel, Notice, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Header, ModuleCard, Layout } from '../../../packages/ui/src';
import DashboardTab from './components/tabs/DashboardTab';
import SeoTab from './components/tabs/SeoTab';
import SpeedTab from './components/tabs/SpeedTab';
import MediaTab from './components/tabs/MediaTab';
import AiTab from './components/tabs/AiTab';

export const App = () => {
	const initialModules = ( window.bankaiData && window.bankaiData.activeModules ) || {
		seo: true,
		speed: true,
		media: true,
		ai: true,
	};

	const [ modules, setModules ] = useState( initialModules );
	const [ loading, setLoading ] = useState( false );
	const [ notice, setNotice ] = useState( null );

	const restUrl = ( window.bankaiData && window.bankaiData.restUrl ) || '/bankai/v1';
	const nonce = ( window.bankaiData && window.bankaiData.nonce ) || '';

	useEffect( () => {
		if ( nonce ) {
			apiFetch.use( apiFetch.createNonceMiddleware( nonce ) );
		}
	}, [ nonce ] );

	const handleToggleModule = ( moduleKey ) => {
		const updated = {
			...modules,
			[ moduleKey ]: ! modules[ moduleKey ],
		};
		setModules( updated );
		saveModules( updated );
	};

	const saveModules = async ( updatedModules ) => {
		setLoading( true );
		setNotice( null );
		try {
			const response = await apiFetch( {
				url: restUrl + '/modules',
				method: 'POST',
				data: { modules: updatedModules },
			} );

			if ( response && response.success ) {
				setNotice( {
					status: 'success',
					message: response.message || __( 'تنظیمات با موفقیت ذخیره شد.', 'bankai-core' ),
				} );
			} else {
				setNotice( {
					status: 'error',
					message: __( 'خطا در ذخیره‌سازی تنظیمات.', 'bankai-core' ),
				} );
			}
		} catch ( err ) {
			setNotice( {
				status: 'error',
				message: err.message || __( 'خطایی رخ داد.', 'bankai-core' ),
			} );
		} finally {
			setLoading( false );
		}
	};

	const tabs = [
		{
			name: 'dashboard',
			title: __( 'خلاصه وضعیت و داشبورد', 'bankai-core' ),
			className: 'tab-dashboard',
		},
		{
			name: 'modules',
			title: __( 'مدیریت ماژول‌ها', 'bankai-core' ),
			className: 'tab-modules',
		},
		{
			name: 'seo',
			title: __( 'سئو (SEO)', 'bankai-core' ),
			className: 'tab-seo',
		},
		{
			name: 'speed',
			title: __( 'سرعت (Speed)', 'bankai-core' ),
			className: 'tab-speed',
		},
		{
			name: 'media',
			title: __( 'رسانه (Media)', 'bankai-core' ),
			className: 'tab-media',
		},
		{
			name: 'ai',
			title: __( 'هوش مصنوعی (AI)', 'bankai-core' ),
			className: 'tab-ai',
		},
	];

	return (
		<Layout>
			<Header
				title={ __( 'پیشخوان مدیریت Bankai Core', 'bankai-core' ) }
				subtitle={ __( 'مدیریت یکپارچه و هوشمند سئو، افزایش سرعت، رسانه و اکوسیستم AI', 'bankai-core' ) }
			/>

			{ notice && (
				<Notice
					status={ notice.status }
					onRemove={ () => setNotice( null ) }
					style={{ marginBottom: '20px' }}
				>
					{ notice.message }
				</Notice>
			) }

			<TabPanel
				className="bankai-admin-tabs"
				activeClass="is-active"
				tabs={ tabs }
			>
				{ ( tab ) => {
					if ( tab.name === 'dashboard' ) {
						return <DashboardTab modules={ modules } onToggleModule={ handleToggleModule } loading={ loading } />;
					}

					if ( tab.name === 'modules' ) {
						return (
							<div style={{ marginTop: '20px' }}>
								<div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '16px' }}>
									<h2 style={{ fontSize: '18px', margin: 0 }}>{ __( 'تنظیمات ماژول‌های اصلی', 'bankai-core' ) }</h2>
									{ loading && <Spinner /> }
								</div>

								<div style={{
									display: 'grid',
									gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
									gap: '20px'
								}}>
									<ModuleCard
										title={ __( 'ماژول سئو (SEO)', 'bankai-core' ) }
										description={ __( 'مدیریت متاتگ‌های داینامیک، اسکیمای JSON-LD، ساخت سایت‌مپ XML و سیستم ریدایرکت.', 'bankai-core' ) }
										icon="🔍"
										checked={ modules.seo }
										onChange={ () => handleToggleModule( 'seo' ) }
										disabled={ loading }
									/>

									<ModuleCard
										title={ __( 'ماژول سرعت (Speed)', 'bankai-core' ) }
										description={ __( 'کش سطح صفحه، Object Cache Safe Drop-in، مینیفای دارایی‌ها و لیزی‌لود.', 'bankai-core' ) }
										icon="⚡"
										checked={ modules.speed }
										onChange={ () => handleToggleModule( 'speed' ) }
										disabled={ loading }
									/>

									<ModuleCard
										title={ __( 'ماژول رسانه (Media)', 'bankai-core' ) }
										description={ __( 'تبدیل خودکار تصاویر به WebP/AVIF و اعمال واترمارک اختصاصی در پس‌زمینه.', 'bankai-core' ) }
										icon="🖼️"
										checked={ modules.media }
										onChange={ () => handleToggleModule( 'media' ) }
										disabled={ loading }
									/>

									<ModuleCard
										title={ __( 'ماژول هوش مصنوعی (AI)', 'bankai-core' ) }
										description={ __( 'اتصال به Gemini, OpenAI, Claude, DeepSeek و OpenRouter با ذخیره‌سازی رمزنگاری‌شده.', 'bankai-core' ) }
										icon="🤖"
										checked={ modules.ai }
										onChange={ () => handleToggleModule( 'ai' ) }
										disabled={ loading }
									/>
								</div>
							</div>
						);
					}

					if ( tab.name === 'seo' ) {
						return <SeoTab />;
					}

					if ( tab.name === 'speed' ) {
						return <SpeedTab />;
					}

					if ( tab.name === 'media' ) {
						return <MediaTab />;
					}

					if ( tab.name === 'ai' ) {
						return <AiTab />;
					}

					return null;
				} }
			</TabPanel>
		</Layout>
	);
};

export default App;
