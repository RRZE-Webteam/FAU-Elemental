/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import {
	useBlockProps,
	RichText,
	InspectorControls,
	URLInput,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
} from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const { buttonText, url } = attributes;
	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Button Settings', 'fau-button')}>
					<TextControl
						label={__('Button Text', 'fau-button')}
						value={buttonText}
						onChange={(value) => setAttributes({ buttonText: value })}
					/>
					<URLInput
						label={__('Button Link', 'fau-button')}
						value={url}
						onChange={(url) => setAttributes({ url })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<RichText
					className="button-text"
					value={buttonText}
					onChange={(value) => setAttributes({ buttonText: value })}
					placeholder={__('Label text', 'fau-button')}
					allowedFormats={[]}
					multiline={false}
				/>
				<span className="button-arrow">→</span>
			</div>
		</>
	);
}
