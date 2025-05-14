import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {} = attributes;

	const blockProps = useBlockProps();

	return (
		<div
			{ ...blockProps }
			role="region"
			aria-label={ __( 'Teaser Grid Block', 'fau-elemental' ) }
		>
			<InspectorControls>
				<p>TODO</p>
			</InspectorControls>

			<h3>This is the optional headline</h3>
			<p>This is the required text.</p>

			TODO Optional Image
			TODO Optional CTA
		</div>
	);
}
