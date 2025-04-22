import { useBlockProps } from '@wordpress/block-editor';

export default function Save({ attributes }) {
    const { 
        displayStyle,
        variant,
        teaserLayout,
        postsPerPage,
        currentPage,
        showPagination,
        totalPosts,
        orderBy,
        order,
        selectionMode,
        selectedPosts
    } = attributes;

    const blockProps = useBlockProps.save();

    return (
        <div {...blockProps}>
            <div 
                className={`fau-teaser-grid ${displayStyle} layout-${teaserLayout}`}
                data-style={displayStyle}
                data-layout={teaserLayout}
                data-variant={variant}
                data-posts-per-page={postsPerPage}
                data-current-page={currentPage}
                data-show-pagination={showPagination}
                data-total-posts={totalPosts}
                data-order-by={orderBy}
                data-order={order}
                data-selection-mode={selectionMode}
                data-selected-posts={JSON.stringify(selectedPosts)}
            ></div>
        </div>
    );
}