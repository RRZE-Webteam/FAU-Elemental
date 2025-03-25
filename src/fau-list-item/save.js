import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const { 
        displayStyle,
        variant,
        postsPerPage,
        showFilters,
        selectedCategory,
        currentPage,
        showPagination,
        totalPosts,
        orderBy,
        order
    } = attributes;

    // Remove style- prefix from blockProps
    const blockProps = useBlockProps.save();

    return (
        <div {...blockProps}>
            <div 
                className={`fau-teaser-grid ${displayStyle}`}
                data-style={displayStyle}
                data-variant={variant}
                data-posts-per-page={postsPerPage}
                data-show-filters={showFilters}
                data-category={selectedCategory}
                data-current-page={currentPage}
                data-show-pagination={showPagination}
                data-total-posts={totalPosts}
                data-order-by={orderBy}
                data-order={order}
            ></div>
        </div>
    );
}