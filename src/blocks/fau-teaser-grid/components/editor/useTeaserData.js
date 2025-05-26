import { useSelect, createSelector } from '@wordpress/data';
import { useMemo } from 'react';

export const usePostTypes = () => {
	return useSelect( ( select ) => {
		const coreSelect = select( 'core' );
		const allPostTypes = coreSelect.getPostTypes();
		return (
			allPostTypes?.filter(
				( type ) =>
					[ 'post', 'page' ].includes( type.slug )
			) || []
		);
	}, [] );
};

export const useCategories = () => {
	return useSelect( ( select ) => {
		return (
			select( 'core' ).getEntityRecords( 'taxonomy', 'category', {
				per_page: -1,
			} ) || []
		);
	}, [] );
};

export const usePosts = ( variant, queryParams ) => {
	const getPosts = createSelector(
		( select, variant, query ) => {
			const rawPosts = select( 'core' ).getEntityRecords(
				'postType',
				variant,
				query
			);
			if ( ! Array.isArray( rawPosts ) ) return [];

			return rawPosts.map( ( post ) => ( {
				id: post.id,
				title: post.title,
				excerpt: post.excerpt,
				date: post.date,
				_embedded: post._embedded || {
					'wp:featuredmedia': [],
					'wp:term': [],
				},
			} ) );
		},
		( select, variant, query ) => {
			const rawPosts = select( 'core' ).getEntityRecords(
				'postType',
				variant,
				query
			);
			const postIdsString = Array.isArray( rawPosts )
				? rawPosts.map( ( p ) => p.id ).join( ',' )
				: '';
			const isResolving = select( 'core' ).isResolving(
				'getEntityRecords',
				[ 'postType', variant, query ]
			);
			return [ postIdsString, isResolving, variant, query ];
		}
	);

	return useSelect(
		( select ) => {
			const posts = getPosts( select, variant, queryParams );
			const isResolving = select( 'core' ).isResolving(
				'getEntityRecords',
				[ 'postType', variant, queryParams ]
			);

			return {
				items: posts,
				isLoading: isResolving,
			};
		},
		[ variant, queryParams, getPosts ]
	);
};

export const useAvailablePosts = ( searchTerm, variant ) => {
	return useSelect(
		( select ) => {
			if ( ! searchTerm || ! variant ) return [];
			const posts =
				select( 'core' ).getEntityRecords( 'postType', variant, {
					search: searchTerm,
					per_page: 20,
					_fields: [ 'id', 'title' ],
					_embed: true,
				} ) || [];

			return posts.map( ( post ) => ( {
				id: post.id,
				title: {
					rendered: post.title.rendered,
				},
			} ) );
		},
		[ searchTerm, variant ]
	);
};

export const useTotalItems = ( variant, selectedCategory ) => {
	return useSelect(
		( select ) => {
			if ( ! variant ) return { totalItems: 0 };

			const countQuery = {
				per_page: 1,
				_fields: [ 'id' ],
				...( selectedCategory ? { categories: selectedCategory } : {} ),
			};

			const posts = select( 'core' ).getEntityRecords(
				'postType',
				variant,
				{
					...countQuery,
					per_page: -1,
				}
			);

			return {
				totalItems: Array.isArray( posts ) ? posts.length : 0,
			};
		},
		[ variant, selectedCategory ]
	);
};
