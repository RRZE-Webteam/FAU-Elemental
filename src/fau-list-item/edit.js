import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { 
    PanelBody, 
    RangeControl, 
    ToggleControl,
    SelectControl,
    Placeholder,
    Spinner 
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import './editor.scss';

const PostTeaser = ({ post }) => {
    if (!post) return null;

    const date = post.date ? new Date(post.date).toLocaleDateString() : '';
    const category = post._embedded?.['wp:term']?.[0]?.[0]?.name || '';
    const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
    const title = post.title?.rendered || '';
    const excerpt = post.excerpt?.rendered || '';
    const link = post.link || '#';

    return (
        <div className="teaser-item">
            <div className="teaser-meta">
                <time>{date}</time>
                {category && <span className="category">{category}</span>}
            </div>
            {image && (
                <div className="teaser-image">
                    <img src={image} alt={title} />
                </div>
            )}
            <h3 dangerouslySetInnerHTML={{ __html: title }} />
            <div className="excerpt" dangerouslySetInnerHTML={{ __html: excerpt }} />
            <a href={link} className="teaser-link">
                {__('Read more', 'fau-elemental')}
            </a>
        </div>
    );
};

const PageTeaser = ({ page }) => {
    if (!page) return null;

    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || '';
    const title = page.title?.rendered || '';
    const excerpt = page.excerpt?.rendered || '';
    const link = page.link || '#';

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image">
                    <img src={image} alt={title} />
                </div>
            )}
            <h3 dangerouslySetInnerHTML={{ __html: title }} />
            <div className="excerpt" dangerouslySetInnerHTML={{ __html: excerpt }} />
            <a href={link} className="teaser-link">
                {__('View page', 'fau-elemental')}
            </a>
        </div>
    );
};

export default function Edit({ attributes, setAttributes }) {
    const { 
        variant,
        postsPerPage,
        columns,
        showFilters,
        selectedCategory,
        currentPage
    } = attributes;

    const { items, categories, totalPages } = useSelect((select) => {
        const coreSelect = select('core');
        if (!coreSelect) {
            return { items: [], categories: [], totalPages: 0 };
        }

        const query = {
            per_page: postsPerPage,
            page: currentPage,
            _embed: true,
        };

        if (variant === 'post' && selectedCategory) {
            query.categories = selectedCategory;
        }

        const records = coreSelect.getEntityRecords('postType', variant, query);
        const cats = variant === 'post' ? 
            coreSelect.getEntityRecords('taxonomy', 'category', { per_page: -1 }) : 
            [];

        return {
            items: records || [],
            categories: cats || [],
            totalPages: records?.length ? Math.ceil(records.length / postsPerPage) : 0
        };
    }, [variant, postsPerPage, selectedCategory, currentPage]);

    if (!items || items.length === 0) {
        return (
            <div {...useBlockProps()}>
                <Placeholder>
                    <Spinner />
                    <p>{__('Loading content...', 'fau-elemental')}</p>
                </Placeholder>
            </div>
        );
    }

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Teaser Grid Settings', 'fau-elemental')}>
                    <SelectControl
                        label={__('Content Type', 'fau-elemental')}
                        value={variant}
                        options={[
                            { label: 'Posts', value: 'post' },
                            { label: 'Pages', value: 'page' }
                        ]}
                        onChange={(value) => setAttributes({ variant: value })}
                    />
                    <RangeControl
                        label={__('Columns', 'fau-elemental')}
                        value={columns}
                        onChange={(value) => setAttributes({ columns: value })}
                        min={1}
                        max={4}
                    />
                    {variant === 'post' && (
                        <ToggleControl
                            label={__('Show Category Filters', 'fau-elemental')}
                            checked={showFilters}
                            onChange={() => setAttributes({ showFilters: !showFilters })}
                        />
                    )}
                </PanelBody>
            </InspectorControls>
            
            <div {...useBlockProps()}>
                {showFilters && categories && categories.length > 0 && variant === 'post' && (
                    <div className="list-filter">
                        <SelectControl
                            label={__('Filter by Category', 'fau-elemental')}
                            value={selectedCategory}
                            options={[
                                { label: __('All Categories', 'fau-elemental'), value: 0 },
                                ...categories.map(cat => ({
                                    label: cat.name,
                                    value: cat.id
                                }))
                            ]}
                            onChange={(value) => setAttributes({ selectedCategory: Number(value) })}
                        />
                    </div>
                )}
                
                <div className="fau-teaser-grid" style={{ 
                    gridTemplateColumns: `repeat(${columns}, 1fr)`
                }}>
                    {items.map((item) => (
                        variant === 'post' 
                            ? <PostTeaser key={item.id} post={item} />
                            : <PageTeaser key={item.id} page={item} />
                    ))}
                </div>

                {totalPages > 1 && (
                    <div className="pagination">
                        {Array.from({ length: totalPages }, (_, i) => (
                            <button
                                key={i + 1}
                                className={`page-number ${currentPage === i + 1 ? 'active' : ''}`}
                                onClick={() => setAttributes({ currentPage: i + 1 })}
                            >
                                {i + 1}
                            </button>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
