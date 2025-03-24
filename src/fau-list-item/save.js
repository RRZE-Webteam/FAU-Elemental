import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const { displayStyle } = attributes;
    const blockProps = useBlockProps.save({
        className: `style-${displayStyle}`
    });
    const { 
        variant,
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
                data-show-filters={showFilters}
                data-category={selectedCategory}
                data-current-page={currentPage}
            ></div>
        </div>
    );
}
