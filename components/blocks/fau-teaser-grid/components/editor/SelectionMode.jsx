import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	Button,
	ComboboxControl,
	__experimentalToggleGroupControl as ToggleGroupControl,
	__experimentalToggleGroupControlOption as ToggleGroupControlOption,
	SelectControl,
} from '@wordpress/components';

export const SelectionMode = ( {
	variant,
	postTypeOptions,
	selectionMode,
	setAttributes,
	selectedPosts,
	availablePosts,
	setSearchTerm,
	handlePostSelection,
	removeSelectedPost,
} ) => {
	return (
		<PanelBody title={ __( 'Selection Mode', 'fau-elemental' ) }>
			<SelectControl
				label={ __( 'Content Type', 'fau-elemental' ) }
				value={ variant }
				options={ postTypeOptions }
				onChange={ ( value ) =>
					setAttributes( {
						variant: value,
						selectedCategory: 0,
						selectedTags: [],
						currentPage: 1,
						selectedPosts: [],
					} )
				}
				help={ __(
					'Select the type of content to display.',
					'fau-elemental'
				) }
				__nextHasNoMarginBottom={ true }
				__next40pxDefaultSize={ true }
			/>

			<ToggleGroupControl
				label={ __( 'Selection mode options', 'fau-elemental' ) }
				value={ selectionMode }
				onChange={ ( value ) => {
					setAttributes( {
						selectionMode: value,
						...( value === 'auto' ? { selectedPosts: [] } : {} ),
					} );
				} }
				isBlock
				__next40pxDefaultSize={ true }
				__nextHasNoMarginBottom={ true }
			>
				<ToggleGroupControlOption
					value="auto"
					label={ __( 'Automatic', 'fau-elemental' ) }
				/>
				<ToggleGroupControlOption
					value="manual"
					label={ __( 'Manual Selection', 'fau-elemental' ) }
				/>
			</ToggleGroupControl>

			{ selectionMode === 'manual' && (
				<>
					<ComboboxControl
						label={ __(
							'Search and select posts',
							'fau-elemental'
						) }
						value=""
						onChange={ handlePostSelection }
						options={
							availablePosts
								? availablePosts.map( ( post ) => ( {
										value: post.id,
										label: post.title.rendered,
								  } ) )
								: []
						}
						onFilterValueChange={ setSearchTerm }
						aria-label={ __(
							'Search and select posts',
							'fau-elemental'
						) }
						aria-describedby="post-search-description"
						__next40pxDefaultSize={ true }
						__nextHasNoMarginBottom={ true }
					/>
					<p
						id="post-search-description"
						className="screen-reader-text"
					>
						{ __(
							'Type to search for posts. Use arrow keys to navigate and enter to select.',
							'fau-elemental'
						) }
					</p>

					<div
						className="selected-posts-list"
						role="list"
						aria-label={ __( 'Selected posts', 'fau-elemental' ) }
					>
						{ selectedPosts.map( ( post ) => (
							<div
								key={ post.id }
								className="selected-post-item"
								role="listitem"
							>
								<span
									dangerouslySetInnerHTML={ {
										__html: post.title,
									} }
								/>
								<Button
									isSmall
									isDestructive
									onClick={ () =>
										removeSelectedPost( post.id )
									}
									aria-label={
										__( 'Remove post', 'fau-elemental' ) +
										': ' +
										post.title
									}
								>
									{ __( 'Remove', 'fau-elemental' ) }
								</Button>
							</div>
						) ) }
					</div>
				</>
			) }
		</PanelBody>
	);
};
