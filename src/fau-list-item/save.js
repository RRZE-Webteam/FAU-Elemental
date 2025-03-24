import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const blockProps = useBlockProps.save();
    const { 
        variant,
        columns,
        postsPerPage,
        showFilters,
        selectedCategory,
        currentPage
    } = attributes;

    return (
        <div {...blockProps}>
            <div 
                className="fau-teaser-grid" 
                data-variant={variant}
                data-posts-per-page={postsPerPage}
                data-columns={columns}
                data-show-filters={showFilters}
                data-category={selectedCategory}
                data-current-page={currentPage}
                style={{ gridTemplateColumns: `repeat(${columns}, 1fr)` }}
            >
            </div>
        </div>
    );
}
