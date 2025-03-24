import { useBlockProps } from '@wordpress/block-editor';
import './editor.scss';

export default function Edit() {
    const blockProps = useBlockProps();

    return (
        <div { ...blockProps }>
            <p>FAU List – hello from the editor!</p>
        </div>
    );
}
