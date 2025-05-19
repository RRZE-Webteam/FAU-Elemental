import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	InspectorControls,
	BlockControls,
} from '@wordpress/block-editor';
import {
	PanelBody,
	RangeControl,
	ToggleControl,
	SelectControl,
	Placeholder,
	Spinner,
	Button,
	ComboboxControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	ToolbarButton,
	DropdownMenu,
} from '@wordpress/components';
import { useSelect, createSelector } from '@wordpress/data';
import './editor.scss';
import { useState, useEffect, useRef, useMemo } from 'react';
import './editor.scss';

import PostTeaser from './components/PostTeaser';
import PageTeaser from './components/PageTeaser';
import { createPagination, updateGridClasses } from './utils/helpers';
import { DisplaySettings } from './components/editor/DisplaySettings';
import { SelectionMode } from './components/editor/SelectionMode';
import { ContentSettings } from './components/editor/ContentSettings';
import {
	usePostTypes,
	useCategories,
	usePosts,
	useAvailablePosts,
	useTotalItems,
} from './components/editor/useTeaserData';

export default function Edit({ attributes, setAttributes }) {
	const {
		displayStyle,
		variant,
		teaserLayout,
		postsPerPage,
		selectedCategory,
		currentPage,
		showPagination,
		totalPosts,
		orderBy,
		order,
		selectedPosts,
		selectionMode,
		headingLevel,
	} = attributes;

	const gridRef = useRef(null);
	const [searchTerm, setSearchTerm] = useState('');

	// Effect to update grid classes when display style or layout changes
	useEffect(() => {
		if (gridRef.current) {
			const grid = gridRef.current;
			grid.className = '';
			updateGridClasses(grid, displayStyle, teaserLayout);
		}
	}, [displayStyle, teaserLayout]);

	// Data hooks
	const postTypes = usePostTypes();
	const categories = useCategories();

	// Memoize query parameters
	const queryParams = useMemo(
		() => ({
			_embed: true,
			per_page: postsPerPage,
			page: currentPage,
			orderby: orderBy,
			order: order.toLowerCase(),
			...(selectedCategory ? { categories: selectedCategory } : {}),
		}),
		[postsPerPage, currentPage, orderBy, order, selectedCategory]
	);

	const { items, isLoading } = usePosts(variant, queryParams);
	const availablePosts = useAvailablePosts(searchTerm, variant);
	const { totalItems } = useTotalItems(variant, selectedCategory);

	// Memoize options
	const postTypeOptions = useMemo(
		() =>
			postTypes.map((type) => ({
				label: type.labels?.singular_name || type.name,
				value: type.slug,
			})),
		[postTypes]
	);

	const categoryOptions = useMemo(
		() => [
			{ label: __('All Categories', 'fau-elemental'), value: 0 },
			...categories.map((category) => ({
				label: category.name,
				value: category.id,
			})),
		],
		[categories]
	);

	// Calculate total pages
	const calculatedTotalPosts =
		totalPosts > 0 ? Math.min(totalPosts, totalItems) : totalItems;
	const calculatedTotalPages = Math.ceil(calculatedTotalPosts / postsPerPage);

	// Post selection handlers
	const handlePostSelection = (postId) => {
		if (!postId) return;

		const post = availablePosts.find((p) => p.id === postId);
		if (!post) return;

		const newSelectedPosts = [...selectedPosts];
		if (!newSelectedPosts.some((p) => p.id === post.id)) {
			newSelectedPosts.push({
				id: post.id,
				title: post.title.rendered,
			});
			setAttributes({ selectedPosts: newSelectedPosts });
		}
	};

	const removeSelectedPost = (postId) => {
		const newSelectedPosts = selectedPosts.filter((p) => p.id !== postId);
		setAttributes({ selectedPosts: newSelectedPosts });
	};

	const blockProps = useBlockProps({
		className: `style-${displayStyle}`,
	});

	return (
		<div
			{...blockProps}
			role="region"
			aria-label={__('Teaser Grid Block', 'fau-elemental')}
		>
			<InspectorControls>
				<DisplaySettings
					displayStyle={displayStyle}
					teaserLayout={teaserLayout}
					headingLevel={headingLevel}
					onDisplayStyleChange={(newStyle) =>
						setAttributes({ displayStyle: newStyle })
					}
					onTeaserLayoutChange={(newLayout) =>
						setAttributes({ teaserLayout: newLayout })
					}
					setAttributes={setAttributes}
				/>

				<SelectionMode
					selectionMode={selectionMode}
					setAttributes={setAttributes}
					selectedPosts={selectedPosts}
					availablePosts={availablePosts}
					searchTerm={searchTerm}
					setSearchTerm={setSearchTerm}
					handlePostSelection={handlePostSelection}
					removeSelectedPost={removeSelectedPost}
				/>

				{selectionMode === 'auto' && (
					<ContentSettings
						variant={variant}
						selectedCategory={selectedCategory}
						postsPerPage={postsPerPage}
						showPagination={showPagination}
						orderBy={orderBy}
						order={order}
						setAttributes={setAttributes}
						postTypeOptions={postTypeOptions}
						categoryOptions={categoryOptions}
						categories={categories}
					/>
				)}

				<PanelBody title={__('Accessibility', 'fau-elemental')}>
					<p>
						{__(
							'To ensure good accessibility and SEO, please make sure that there is only one H1 heading on the page. If you already have an H1 heading, use a different heading level for the teasers in this block.',
							'fau-elemental'
						)}
					</p>
				</PanelBody>
			</InspectorControls>

			<BlockControls group="block">
				<DropdownMenu
					icon="heading"
					label={ __( 'Change heading level', 'fau-elemental' ) }
					controls={ [
						{
							title: __( 'Heading 2', 'fau-elemental' ),
							onClick: () => setAttributes({ headingLevel: 'h2' }),
							isActive: headingLevel === 'h2',
						},
						{
							title: __( 'Heading 3', 'fau-elemental' ),
							onClick: () => setAttributes({ headingLevel: 'h3' }),
							isActive: headingLevel === 'h3',
						},
						{
							title: __( 'Heading 4', 'fau-elemental' ),
							onClick: () => setAttributes({ headingLevel: 'h4' }),
							isActive: headingLevel === 'h4',
						},
						{
							title: __( 'Heading 5', 'fau-elemental' ),
							onClick: () => setAttributes({ headingLevel: 'h5' }),
							isActive: headingLevel === 'h5',
						},
						{
							title: __( 'Heading 6', 'fau-elemental' ),
							onClick: () => setAttributes({ headingLevel: 'h6' }),
							isActive: headingLevel === 'h6',
						},
					] }
				/>
			</BlockControls>

			<div
				ref={ gridRef }
				className={ `fau-teaser-grid ${ displayStyle } ${
					displayStyle === 'teaser-grid'
						? `layout-${teaserLayout}`
						: displayStyle === 'mini-list'
						? 'style-mini-list'
						: ''
				}`}
				role="list"
				aria-label={__('Content grid', 'fau-elemental')}
			>
				{!isLoading ? (
					selectionMode === 'manual' ? (
						selectedPosts.length > 0 ? (
							selectedPosts.map((selectedPost) => {
								const fullPost = items.find(
									(item) => item.id === selectedPost.id
								);

								const postData = fullPost || {
									id: selectedPost.id,
									title: {
										rendered: selectedPost.title,
									},
									excerpt: {
										rendered: '',
									},
									_embedded: {
										'wp:featuredmedia': [],
										'wp:term': [],
									},
								};

								return variant === 'post' ? (
									<PostTeaser
										key={postData.id}
										post={postData}
										headingLevel={headingLevel}
									/>
								) : (
									<PageTeaser
										key={postData.id}
										page={postData}
										headingLevel={headingLevel}
									/>
								);
							})
						) : (
							<p role="status">
								{__('No posts selected', 'fau-elemental')}
							</p>
						)
					) : items && items.length > 0 ? (
						items.map((item) =>
							variant === 'post' ? (
								<PostTeaser
									key={item.id}
									post={item}
									headingLevel={headingLevel}
								/>
							) : (
								<PageTeaser
									key={item.id}
									page={item}
									headingLevel={headingLevel}
								/>
							)
						)
					) : (
						<p role="status">
							{__('No items found', 'fau-elemental')}
						</p>
					)
				) : (
					<Placeholder>
						<Spinner />
						<p role="status">{__('Loading...', 'fau-elemental')}</p>
					</Placeholder>
				)}
			</div>

			{showPagination &&
				calculatedTotalPages > 1 &&
				selectionMode === 'auto' && (
					<nav
						role="navigation"
						aria-label={__('Pagination', 'fau-elemental')}
					>
						{createPagination(
							currentPage,
							calculatedTotalPages,
							(newPage) => setAttributes({ currentPage: newPage })
						)}
					</nav>
				)}
		</div>
	);
}
