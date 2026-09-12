import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { PanelBody, Button, Spinner, TextControl, TextareaControl, SelectControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const BankaiSidebar = () => {
	const postTitle = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'title' ) );
	const postContent = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'content' ) );
	const meta = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'meta' ) ) || {};
	const { editPost } = useDispatch( 'core/editor' );

	const [ seoTitle, setSeoTitle ] = useState( meta._bankai_seo_title || '' );
	const [ seoDesc, setSeoDesc ] = useState( meta._bankai_seo_description || '' );
	const [ focusKeyword, setFocusKeyword ] = useState( meta._bankai_seo_focus_keyword || '' );
	const [ schemaType, setSchemaType ] = useState( meta._bankai_schema_type || 'Article' );
	const [ loading, setLoading ] = useState( false );

	useEffect( () => {
		editPost( {
			meta: {
				...meta,
				_bankai_seo_title: seoTitle,
				_bankai_seo_description: seoDesc,
				_bankai_seo_focus_keyword: focusKeyword,
				_bankai_schema_type: schemaType,
			}
		} );
	}, [ seoTitle, seoDesc, focusKeyword, schemaType ] );

	const calculateSeoScore = () => {
		let score = 30;
		if ( focusKeyword ) {
			score += 20;
			if ( postTitle && postTitle.toLowerCase().includes( focusKeyword.toLowerCase() ) ) {
				score += 25;
			}
			if ( postContent && postContent.toLowerCase().includes( focusKeyword.toLowerCase() ) ) {
				score += 25;
			}
		}
		return Math.min( score, 100 );
	};

	const score = calculateSeoScore();
	const restUrl = ( window.bankaiData && window.bankaiData.restUrl ) || '/bankai/v1';

	const handleGenerateSeoWithAi = async () => {
		if ( ! postContent ) {
			return;
		}

		setLoading( true );
		try {
			const res = await apiFetch( {
				url: ,
				method: 'POST',
				data: { provider: 'openai', prompt: 'Generate SEO title and description for content' },
			} );

			if ( res && res.text ) {
				setSeoDesc( res.text );
			}
		} catch ( err ) {
			// Handle error
		} finally {
			setLoading( false );
		}
	};

	return (
		<>
			<PluginSidebarMoreMenuItem target="bankai-sidebar">
				{ __( 'Bankai AI & SEO Assistant', 'bankai-core' ) }
			</PluginSidebarMoreMenuItem>
			<PluginSidebar
				name="bankai-sidebar"
				title={ __( 'Bankai AI & RankMath SEO', 'bankai-core' ) }
				icon="shield"
			>
				<PanelBody title={ __( 'امتیاز سئو (SEO Score)', 'bankai-core' ) } initialOpen={ true }>
					<div style={{ textAlign: 'center', padding: '10px 0' }}>
						<div style={{ fontSize: '32px', fontWeight: 'bold', color: score > 70 ? '#10b981' : score > 40 ? '#f59e0b' : '#ef4444' }}>
							{ score } / 100
						</div>
						<div style={{ fontSize: '12px', color: '#6b7280', marginTop: '4px' }}>
							{ score > 70 ? __( 'عالی (Good)', 'bankai-core' ) : __( 'نیازمند بهینه‌سازی', 'bankai-core' ) }
						</div>
					</div>
					<TextControl
						label={ __( 'کلمه کلیدی اصلی (Focus Keyword)', 'bankai-core' ) }
						value={ focusKeyword }
						onChange={ setFocusKeyword }
					/>
				</PanelBody>

				<PanelBody title={ __( 'پیش‌نمایش گوگل (Google Snippet Preview)', 'bankai-core' ) } initialOpen={ true }>
					<div style={{ padding: '12px', border: '1px solid #dfe1e5', borderRadius: '8px', backgroundColor: '#fff', marginBottom: '16px' }}>
						<div style={{ color: '#202124', fontSize: '12px' }}>https://your-site.com/...</div>
						<div style={{ color: '#1a0dab', fontSize: '16px', fontWeight: '500', margin: '4px 0' }}>
							{ seoTitle || postTitle || __( 'عنوان نوشته در گوگل', 'bankai-core' ) }
						</div>
						<div style={{ color: '#4d5156', fontSize: '13px', lineHeight: '1.4' }}>
							{ seoDesc || __( 'توضیحات متای نوشته پس از تنظیم یا تولید با هوش مصنوعی در این قسمت نمایش داده می‌شود.', 'bankai-core' ) }
						</div>
					</div>
					<TextControl
						label={ __( 'عنوان سئو (SEO Title)', 'bankai-core' ) }
						value={ seoTitle }
						onChange={ setSeoTitle }
					/>
					<TextareaControl
						label={ __( 'توضیحات متا (Meta Description)', 'bankai-core' ) }
						value={ seoDesc }
						onChange={ setSeoDesc }
						rows={ 3 }
					/>
					<Button
						isPrimary
						onClick={ handleGenerateSeoWithAi }
						disabled={ loading }
						style={{ width: '100%', justifyContent: 'center' }}
					>
						{ loading ? <Spinner /> : __( 'تولید خودکار متا با AI 🤖', 'bankai-core' ) }
					</Button>
				</PanelBody>

				<PanelBody title={ __( 'تنظیمات اسکیما (Schema Type)', 'bankai-core' ) } initialOpen={ false }>
					<SelectControl
						label={ __( 'نوع اسکیما (Schema Type)', 'bankai-core' ) }
						value={ schemaType }
						options={[
							{ label: 'Article', value: 'Article' },
							{ label: 'Product', value: 'Product' },
							{ label: 'FAQ Page', value: 'FAQPage' },
							{ label: 'HowTo', value: 'HowTo' },
							{ label: 'LocalBusiness', value: 'LocalBusiness' },
							{ label: 'Recipe', value: 'Recipe' },
							{ label: 'Course', value: 'Course' },
							{ label: 'Event', value: 'Event' },
							{ label: 'Video', value: 'VideoObject' },
						]}
						onChange={ setSchemaType }
					/>
				</PanelBody>
			</PluginSidebar>
		</>
	);
};

if ( window.wp && window.wp.plugins ) {
	registerPlugin( 'bankai-editor-sidebar', {
		render: BankaiSidebar,
	} );
}
