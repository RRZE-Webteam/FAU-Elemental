import { useBlockProps } from '@wordpress/block-editor';

export default function Save( { attributes } ) {
	const {} = attributes; // TODO

	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			TODO
		</div>
	);
}
