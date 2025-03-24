import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const blockProps = useBlockProps.save();
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
