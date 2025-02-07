/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';

/**
 * The save function defines the way in which the different attributes should
 * be combined into the final markup, which is then serialized by the block
 * editor into `post_content`.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#save
 *
 * @return {Element} Element to render.
 */
export default function save({ attributes }) {
	const { buttonText, url } = attributes;
	
	const blockProps = useBlockProps.save({
		className: `wp-block-fau-elemental-fau-button`
	});

	const content = (
		<>
			<span className="button-text">{buttonText || __('Label text', 'fau-button')}</span>
			<span className="button-arrow">→</span>
		</>
	);

	return url ? (
		<a {...blockProps} href={url}>
			{content}
		</a>
	) : (
		<div {...blockProps}>
			{content}
		</div>
	);
}
