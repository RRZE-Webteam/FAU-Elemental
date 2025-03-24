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

const FALLBACK_IMAGE = './../../assets/images/logo.svg';

function PostTeaser({ post }) {
    if (!post) return null;

    const dateObj = post.date ? new Date(post.date) : null;
    const day = dateObj ? dateObj.toLocaleDateString('de-DE', { day: '2-digit' }) : '';
    const monthYear = dateObj ? dateObj.toLocaleDateString('de-DE', {
        month: 'short',
        year: 'numeric'
    }).replace('.', '').toUpperCase() : '';
    const category = post._embedded?.['wp:term']?.[0]?.[0]?.name || '';
    const image = post._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = post.title?.rendered || '';
    const excerpt = (post.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = post.link || '#';

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image-wrapper">
                    <div className="teaser-image">
                        <img src={image} alt={title} />
                    </div>
                    <div className="teaser-meta">
                        <time>
                            <span className="date-day">{day}</span>
                            <span className="date-month-year">{monthYear}</span>
                        </time>
                    </div>
                </div>
            )}
            <div className="teaser-content-wrapper">
                <div className="teaser-content">
                    <div className="content-column">
                        {category && <span className="category">{category}</span>}
                        <h3 className="clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: title }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: title }} />
                        </h3>
                        <div className="excerpt clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: excerpt }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: excerpt }} />
                        </div>
                    </div>
                    <div className="button-column">
                        <div className="wp-block-button is-style-icon-only">
                            <a href={link} className="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

function PageTeaser({ page }) {
    if (!page) return null;

    const image = page._embedded?.['wp:featuredmedia']?.[0]?.source_url || FALLBACK_IMAGE;
    const title = page.title?.rendered || '';
    const excerpt = (page.excerpt?.rendered || '').replace('[&hellip;]', '..');
    const link = page.link || '#';

    return (
        <div className="teaser-item">
            {image && (
                <div className="teaser-image">
                    <img src={image} alt={title} />
                </div>
            )}
            <div className="teaser-content-wrapper">
                <div className="teaser-content">
                    <div className="content-column">
                        <h3 className="clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: title }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: title }} />
                        </h3>
                        <div className="excerpt clamp-3">
                            <span className="visually-hidden" dangerouslySetInnerHTML={{ __html: excerpt }} />
                            <span aria-hidden="true" dangerouslySetInnerHTML={{ __html: excerpt }} />
                        </div>
                    </div>
                    <div className="button-column">
                        <div className="wp-block-button is-style-icon-only">
                            <a href={link} className="wp-block-button__link"></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Edit({ attributes, setAttributes }) {
    const { 
        variant,
        postsPerPage,
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
                
                <div className="fau-teaser-grid">
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
