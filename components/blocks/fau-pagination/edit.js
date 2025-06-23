import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

export default function Edit({ attributes, setAttributes }) {
    const { variant, currentPage, totalPages } = attributes;
    const blockProps = useBlockProps();

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Pagination Settings', 'fau-elemental')}>
                    <SelectControl
                        label={__('Pagination Variant', 'fau-elemental')}
                        value={variant}
                        options={[
                            { label: __('Basic Pagination', 'fau-elemental'), value: 'basic' },
                            { label: __('Load More Button', 'fau-elemental'), value: 'load-more' }
                        ]}
                        onChange={(value) => setAttributes({ variant: value })}
                        help={__('Choose the type of pagination to display.', 'fau-elemental')}
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                <nav className={`pagination ${variant}`} role="navigation" aria-label={__('Pagination', 'fau-elemental')}>
                    {variant === 'load-more' ? (
                        <button className="load-more-button" data-current-page={currentPage} data-total-pages={totalPages}>
                            {__('Load More', 'fau-elemental')}
                        </button>
                    ) : (
                        <>
                            <a href="#" className={`page-numbers prev${currentPage === 1 ? ' disabled' : ''}`}>
                                {__('Prev', 'fau-elemental')}
                            </a>
                            {Array.from({ length: Math.min(totalPages, 5) }, (_, i) => i + 1).map((page) => (
                                <a
                                    key={page}
                                    href="#"
                                    className={`page-numbers${page === currentPage ? ' current' : ''}`}
                                >
                                    {page}
                                </a>
                            ))}
                            {totalPages > 5 && <span className="page-numbers dots">...</span>}
                            <a href="#" className={`page-numbers next${currentPage === totalPages ? ' disabled' : ''}`}>
                                {__('Next', 'fau-elemental')}
                            </a>
                        </>
                    )}
                </nav>
            </div>
        </>
    );
} 