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
			<div className="fau-global-search__outer-wrapper">
				<div { ...blockProps }>
					{ width === 'full-grid' && heading && (
						<h3 className="fau-global-search__heading fau-global-search__heading--full-grid">
							{ heading }
						</h3>
					) }
					{ width !== 'full-grid' && heading && (
						<h3 className="fau-global-search__heading">{ heading }</h3>
					) }
					<form className="fau-global-search fau-global-search__form">
						<div
							className={ `fau-global-search__input-wrapper${
								width === 'full-grid'
									? ' fau-global-search__input-wrapper--full-grid'
									: ''
							}` }
						>
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
					</form>
				</div>
			</div>
		</>
	);
}
