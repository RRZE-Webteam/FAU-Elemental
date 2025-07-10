import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

export default function Edit( { attributes, setAttributes } ) {
	const { width } = attributes;

	const blockProps = useBlockProps( {
		className: `fau-global-search-wrapper fau-global-search-wrapper--${ width }`,
	} );

	const onChangeWidth = ( newWidth ) => {
		setAttributes( { width: newWidth } );
	};

	return (
		<>
			<InspectorControls>
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
				<form className="fau-global-search fau-global-search__form">
					<div className="fau-global-search__input-wrapper">
						<input
							type="search"
							className="fau-global-search__input"
							placeholder={ __( 'Search…', 'fau-elemental' ) }
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

					{ width === 'content-size' && (
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
									disabled
								/>
								<span>
									{ __( 'Website Search', 'fau-elemental' ) }
								</span>
							</label>
						</div>
					) }
				</form>
			</div>
		</>
	);
}
