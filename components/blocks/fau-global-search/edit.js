import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { width, heading } = attributes;

	const blockProps = useBlockProps( {
		className: `fau-global-search-wrapper fau-global-search-wrapper--${ width }`,
	} );

	const onChangeWidth = ( newWidth ) => {
		setAttributes( { width: newWidth } );
	};

	const onChangeHeading = ( newHeading ) => {
		setAttributes( { heading: newHeading } );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content Settings', 'fau-elemental' ) }>
					<TextControl
						label={ __( 'Heading', 'fau-elemental' ) }
						value={ heading }
						onChange={ onChangeHeading }
						help={ __(
							'Optional heading text to display above the search form',
							'fau-elemental'
						) }
					/>
				</PanelBody>
				<PanelBody title={ __( 'Layout Settings', 'fau-elemental' ) }>
					<SelectControl
						label={ __( 'Width', 'fau-elemental' ) }
						value={ width }
						options={ [
							{
								label: __(
									'Content Size (with search scope & advanced features)',
									'fau-elemental'
								),
								value: 'content-size',
							},
							{
								label: __(
									'Full Grid (simple search only)',
									'fau-elemental'
								),
								value: 'full-grid',
							},
						] }
						onChange={ onChangeWidth }
						help={ __(
							'Content Size: for editorial content, includes search scope selection and advanced features. Full Grid: for wide teaser components, simple search only.',
							'fau-elemental'
						) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ heading && (
					<h3 className="fau-global-search__heading">{ heading }</h3>
				) }
				<form className="fau-global-search fau-global-search__form">
					<div className="fau-global-search__input-wrapper">
						<input
							type="search"
							className="fau-global-search__input"
							placeholder={ __( 'Search…', 'fau-elemental' ) }
							autoComplete="off"
							disabled
						/>
						<button
							type="submit"
							className="fau-global-search__button"
							disabled
						>
							<span className="fau-global-search__button-text">
								{ __( 'Search', 'fau-elemental' ) }
							</span>
							<span
								className="fau-global-search__button-icon"
								aria-hidden="true"
							></span>
						</button>
					</div>

					{ /* 
					//FAU-wide Search for Global and Inline Search is supposed to be some kind of google search which might get implemented in the future;
					//Keeping code commented out for future implementation rather than deletion
				
					width === 'content-size' && (
						<div className="fau-global-search__scope">
							<label
								className="fau-global-search__scope-option"
								htmlFor="scope-global-preview"
							>
								<input
									type="radio"
									name="scope"
									value="global"
									id="scope-global-preview"
									defaultChecked
									disabled
								/>
								<span>
									{ __( 'Global Search', 'fau-elemental' ) }
								</span>
							</label>
							<label
								className="fau-global-search__scope-option"
								htmlFor="scope-website-preview"
							>
								<input
									type="radio"
									name="scope"
									value="website"
									id="scope-website-preview"
									defaultChecked
									disabled
								/>
								<span>
									{ __( 'Website Search', 'fau-elemental' ) }
								</span>
							</label>
						</div>
					) 
					*/ }
				</form>
			</div>
		</>
	);
}
