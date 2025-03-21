import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';

console.log('copyright-info');

registerBlockType('fau-elemental/fau-copyright-info', {
    edit: function Edit() {
        const blockProps = useBlockProps();

        return (
            <div {...blockProps}>
                <div className="copyright-info-placeholder">
                    <h3>{__('Copyright Information', 'fau-elemental')}</h3>
                    <p className="description">
                        {__('This block will display copyright information from images and other sources in the frontend.', 'fau-elemental')}
                    </p>
                </div>
            </div>
        );
    }
}); 