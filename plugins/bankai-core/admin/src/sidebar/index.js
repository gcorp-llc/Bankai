import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { PanelBody, Button, Spinner, TextControl, TextareaControl } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

const BankaiSidebar = () => {
	const postTitle = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'title' ) );
	const postContent = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( 'content' ) );
	const { editPost } = useDispatch( 'core/editor' );

	const [ seoTitle, setSeoTitle ] = useState( postTitle || '' );
	const [ seoDesc, setSeoDesc ] = useState( '' );
	const [ loading, setLoading ] = useState( false );

	const restUrl = ( window.bankaiData && window.bankaiData.restUrl ) || '/bankai/v1';

	const handleGenerateSeoWithAi = async () => {
		if ( ! postContent ) {
			return;
		}

		setLoading( true );
		try {
			const res = await apiFetch( {
				url: `${restUrl}/ai/generate`,
				method: 'POST',
				data: { provider: 'openai', prompt: `Generate SEO title and meta description for content: ${postContent.substring(0, 500)}` },
			} );

			if ( res && res.text ) {
				setSeoDesc( res.text );
			}
		} catch ( err ) {
			// Handle error silently or set notice
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
				title={ __( 'Bankai AI & SEO Assistant', 'bankai-core' ) }
				icon="shield"
			>
				<PanelBody title={ __( 'پیش‌نمایش گوگل (Google Snippet Preview)', 'bankai-core' ) } initialOpen={ true }>
					<div style={{ padding: '12px', border: '1px solid #dfe1e5', borderRadius: '8px', backgroundColor: '#fff' }}>
						<div style={{ color: '#202124', fontSize: '14px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
							https://example.com/sample-post
						</div>
						<div style={{ color: '#1a0dab', fontSize: '18px', fontWeight: '400', margin: '4px 0' }}>
							{ seoTitle || postTitle || __( 'عنوان نوشته در گوگل', 'bankai-core' ) }
						</div>
						<div style={{ color: '#4d5156', fontSize: '13px', lineHeight: '1.4' }}>
							{ seoDesc || __( 'توضیحات متای نوشته پس از تولید در این بخش پیش‌نمایش داده می‌شود.', 'bankai-core' ) }
						</div>
					</div>
				</PanelBody>

				<PanelBody title={ __( 'تولید محتوا با هوش مصنوعی', 'bankai-core' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'عنوان سئو (SEO Title)', 'bankai-core' ) }
						value={ seoTitle }
						onChange={ setSeoTitle }
					/>
					<TextareaControl
						label={ __( 'توضیحات متا (Meta Description)', 'bankai-core' ) }
						value={ seoDesc }
						onChange={ setSeoDesc }
						rows={ 4 }
					/>
					<Button
						isPrimary
						onClick={ handleGenerateSeoWithAi }
						disabled={ loading }
						style={{ width: '100%', justifyContent: 'center' }}
					>
						{ loading ? <Spinner /> : __( 'تولید هوشمند متا با AI', 'bankai-core' ) }
					</Button>
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
