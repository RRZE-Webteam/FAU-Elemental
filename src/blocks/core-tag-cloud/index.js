import { registerBlockType, unregisterBlockStyle } from '@wordpress/blocks';
import { useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

// Unregister the default style
domReady(() => {
    unregisterBlockStyle('core/tag-cloud', ['default', 'outline']);
});

registerBlockType('core/tag-cloud', {
    apiVersion: 3,
    title: __('Tag Cloud', 'fau-elemental'),
    description: __('Display a cloud of tags assigned to the current page.', 'fau-elemental'),
    category: 'widgets',
    icon: 'tag',
    supports: {
        html: false,
        align: true,
        color: {
            text: true,
            background: true,
            link: true,
        },
    },
    attributes: {
        showCount: {
            type: 'boolean',
            default: false,
        },
        numberOfTags: {
            type: 'number',
            default: 45,
        },
    },
    edit: ({ attributes, setAttributes }) => {
        const { showCount, numberOfTags } = attributes;
        const blockProps = useBlockProps();

        // Get the current post ID
        const postId = useSelect((select) => {
            return select('core/editor').getCurrentPostId();
        }, []);

        // Get the current post's tags
        const tags = useSelect((select) => {
            if (!postId) return null;
            return select('core').getEntityRecords('taxonomy', 'post_tag', {
                post: postId,
                per_page: numberOfTags,
            });
        }, [postId, numberOfTags]);

        if (!tags) {
            return <p>{__('Loading tags...', 'fau-elemental')}</p>;
        }

        if (tags.length === 0) {
            return <p>{__('No tags assigned to this post.', 'fau-elemental')}</p>;
        }

        return (
            <div {...blockProps}>
                <div className="tag-cloud">
                    {tags.map((tag) => (
                        <a
                            key={tag.id}
                            href={tag.link}
                            className="tag-cloud__tag"
                        >
                            {tag.name}
                            {showCount && ` (${tag.count})`}
                        </a>
                    ))}
                </div>
            </div>
        );
    },
    save: () => {
        const blockProps = useBlockProps.save();
        return (
            <div {...blockProps}>
                <div className="tag-cloud">
                    {/* Tags will be rendered dynamically on the server */}
                </div>
            </div>
        );
    },
});
