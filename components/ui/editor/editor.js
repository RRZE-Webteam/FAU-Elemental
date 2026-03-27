import { createBlock, rawHandler } from '@wordpress/blocks';
import {
	Button,
	Notice,
	Card,
	CardBody,
	CardHeader,
	ExternalLink,
	ResponsiveWrapper,
	Spinner,
	__experimentalText as Text,
} from '@wordpress/components';
import { select, subscribe, dispatch, useSelect, useDispatch } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
// import { addFilter } from '@wordpress/hooks';
// import { createHigherOrderComponent } from '@wordpress/compose';
import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { Fragment, RawHTML, useMemo, useState } from '@wordpress/element';
import { unregisterFormatType } from '@wordpress/rich-text';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

// Editor iframe body class injection (persistent MutationObserver pattern).
import './iframe-body-class-injection';

// Import all core-blocks
import '../../core-blocks/button/index.js';
import '../../core-blocks/cover/index.js';
import '../../core-blocks/details/index.js';
import '../../core-blocks/file/index.js';
import '../../core-blocks/gallery/index.js';
import '../../core-blocks/group/index.js';
import '../../core-blocks/image/index.js';
import '../../core-blocks/list/index.js';
import '../../core-blocks/media-text/index.js';
import '../../core-blocks/paragraph/index.js';
import '../../core-blocks/separator/index.js';
import '../../core-blocks/spacer/index.js';
import '../../core-blocks/table/index.js';
import '../../core-blocks/tag-cloud/index.js';

/**
 * Block Selection Class Manager
 *
 * Adds class to the body element based on currently selected block
 * Class format: 'faue-is-{blocktype}-block-selected'
 */

// Subscribe to block selection changes
subscribe( () => {
	// Clear all block selection classes first
	document.body.classList.forEach( ( className ) => {
		if (
			className.startsWith( 'faue-is-' ) &&
			className.endsWith( '-block-selected' )
		) {
			document.body.classList.remove( className );
		}
		// Also clear pattern-related classes
		if (
			className.startsWith( 'faue-is-' ) &&
			className.endsWith( '-pattern-selected' )
		) {
			document.body.classList.remove( className );
		}
	} );

	// Get the currently selected block (first in selection array)
	const selectedBlockId =
		select( 'core/block-editor' ).getSelectedBlockClientId();

	// Add class for the currently selected block
	if ( selectedBlockId ) {
		const block = select( 'core/block-editor' ).getBlock( selectedBlockId );
		const parentBlockIds =
			select( 'core/block-editor' ).getBlockParents( selectedBlockId );
		const parentBlockId = parentBlockIds[ parentBlockIds.length - 1 ]; // Get the immediate parent
		const parentBlock = parentBlockId
			? select( 'core/block-editor' ).getBlock( parentBlockId )
			: null;

		if ( block && block.name.startsWith( 'core/' ) ) {
			const blockType = block.name.replace( 'core/', '' );
			document.body.classList.add(
				`faue-is-${ blockType }-block-selected`
			);
		}

		if ( parentBlock && parentBlock.name.startsWith( 'core/' ) ) {
			const blockType = parentBlock.name.replace( 'core/', '' );
			document.body.classList.add(
				`faue-is-${ blockType }-parent-block-selected`
			);
		}

		// Check if the selected block is inside a pattern
		const patternClass = detectPatternClass( selectedBlockId );
		if ( patternClass ) {
			document.body.classList.add(
				`faue-is-${ patternClass }-pattern-selected`
			);
		}
	}
} );

/**
 * Detect if a block is inside a pattern and return the pattern class name
 * @param {string} blockId - The client ID of the block to check
 * @return {string|null} - The pattern class name or null if not in a pattern
 */
function detectPatternClass( blockId ) {
	const block = select( 'core/block-editor' ).getBlock( blockId );
	if ( ! block ) {
		return null;
	}

	// Check if this block itself is a pattern root
	const patternClass = getPatternClassFromBlock( block );
	if ( patternClass ) {
		return patternClass;
	}

	// Check parent blocks for pattern classes
	const parentBlockIds =
		select( 'core/block-editor' ).getBlockParents( blockId );

	for ( let i = parentBlockIds.length - 1; i >= 0; i-- ) {
		const parentBlock = select( 'core/block-editor' ).getBlock(
			parentBlockIds[ i ]
		);
		if ( parentBlock ) {
			const parentPatternClass = getPatternClassFromBlock( parentBlock );
			if ( parentPatternClass ) {
				return parentPatternClass;
			}
		}
	}

	return null;
}

/**
 * Extract pattern class name from a block's className attribute
 * @param {Object} block - The block object
 * @return {string|null} - The pattern class name or null if not a pattern
 */
function getPatternClassFromBlock( block ) {
	if ( ! block.attributes?.className ) {
		return null;
	}

	const className = block.attributes.className;

	// Check for hero pattern classes
	const heroPatterns = [
		'hero-fau',
		'hero-portal',
		'hero-faculty-other',
		'hero-chair-cooperation',
		'hero-cooperation',
		'hero-other',
	];

	// Check for big-buttons pattern classes
	const bigButtonsPatterns = [ 'big-buttons', 'big-buttons-faculties' ];

	// Check for other pattern classes
	const otherPatterns = [
		'featured-event-teaser',
		'big-teaser',
		'logo-grid',
		'mini-list-file',
		'facts-grid-with-header',
	];

	// Check hero patterns first
	const foundHeroPattern = heroPatterns.find( ( pattern ) =>
		className.includes( pattern )
	);
	if ( foundHeroPattern ) {
		return foundHeroPattern;
	}

	// Check big-buttons patterns
	const foundBigButtonsPattern = bigButtonsPatterns.find( ( pattern ) =>
		className.includes( pattern )
	);
	if ( foundBigButtonsPattern ) {
		return foundBigButtonsPattern;
	}

	// Check other patterns (both with and without pattern- prefix)
	const foundOtherPattern = otherPatterns.find(
		( pattern ) =>
			className.includes( pattern ) ||
			className.includes( `pattern-${ pattern }` )
	);
	if ( foundOtherPattern ) {
		return foundOtherPattern;
	}

	return null;
}

// Remove the text-color format type
unregisterFormatType( 'core/text-color' );

/**
 * Filter Rich Text Format Types
 *
 * This filter allows disabling specific format types (like bold, italic)
 * for specific blocks
 */
// addFilter(
// 	'editor.BlockEdit',
// 	'fau-elemental/filter-format-types',
// 	createHigherOrderComponent((BlockEdit) => {
// 		return (props) => {
// 			const { name, isSelected } = props;

// 			// When the block is selected, filter available format types
// 			if (isSelected) {
// 				// Check for specific block types to customize
// 				if (name === 'core/paragraph') {
// 					// Example: Disable bold formatting for paragraphs
// 					const formatTypes = select('core/rich-text').getFormatTypes();
// 					const disabledFormats = ['core/bold']; // Add format names to disable

// 					formatTypes.forEach(format => {
// 						if (disabledFormats.includes(format.name)) {
// 							// Unregister or modify the format type
// 							dispatch('core/rich-text').removeFormatTypes(format.name);
// 						}
// 					});
// 				}
// 			}

// 			return <BlockEdit {...props} />;
// 		};
// 	}, 'withFilteredFormatTypes')
// );

const { createNotice } = dispatch( 'core/notices' );

let isProcessing = false;

// Function to remove the most recently added FAU Hero pattern
function removeLastFAUHeroPattern() {
	const blocks = select( 'core/block-editor' ).getBlocks();

	// Find all top-level blocks that contain the FAU Hero pattern
	const heroBlocks = [];

	blocks.forEach( ( block, index ) => {
		if ( containsFAUHeroPattern( block ) ) {
			heroBlocks.push( {
				clientId: block.clientId,
				index,
				block,
			} );
		}
	} );

	// If we have more than one hero block, remove the last one
	if ( heroBlocks.length > 1 ) {
		const lastHeroBlock = heroBlocks[ heroBlocks.length - 1 ];
		dispatch( 'core/block-editor' ).removeBlock( lastHeroBlock.clientId );
	}
}

// Helper function to check if a block (or its children) contains the FAU Hero pattern
function containsFAUHeroPattern( block ) {
	// Check if this is the main hero group block
	if (
		block.name === 'core/group' &&
		block.attributes?.className?.includes( 'hero-fau' )
	) {
		return true;
	}

	// Check if any inner blocks contain the hero pattern
	if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
		return block.innerBlocks.some( ( innerBlock ) =>
			containsFAUHeroPatternRecursive( innerBlock )
		);
	}

	return false;
}

// Recursive helper to check inner blocks
function containsFAUHeroPatternRecursive( block ) {
	// Check for hero-specific classes
	if (
		block.attributes?.className?.includes( 'hero-front-page-title' ) ||
		block.attributes?.className?.includes( 'hero-content' ) ||
		block.attributes?.className?.includes( 'hero-fau' )
	) {
		return true;
	}

	// Check inner blocks recursively
	if ( block.innerBlocks && block.innerBlocks.length > 0 ) {
		return block.innerBlocks.some( ( innerBlock ) =>
			containsFAUHeroPatternRecursive( innerBlock )
		);
	}

	return false;
}

// Subscribe to block editor changes
let previousBlockCount = 0;

subscribe( () => {
	if ( isProcessing ) {
		return;
	}

	const blocks = select( 'core/block-editor' ).getBlocks();
	const currentBlockCount = select( 'core/block-editor' ).getBlockCount();

	// Only check when blocks are added (not removed or modified)
	if ( currentBlockCount > previousBlockCount ) {
		const patternCount = countFAUHeroOccurrences( blocks );

		if ( patternCount > 1 ) {
			isProcessing = true;

			// Remove the duplicate
			removeLastFAUHeroPattern();

			// Show notice to user
			createNotice(
				'warning',
				'Only one FAU Hero pattern is allowed per page. The duplicate has been removed.',
				{
					isDismissible: true,
					type: 'snackbar',
				}
			);

			setTimeout( () => {
				isProcessing = false;
			}, 100 );
		}
	}

	previousBlockCount = currentBlockCount;
} );

// Count how many times the FAU Hero pattern appears (count top-level occurrences only)
function countFAUHeroOccurrences( blocks ) {
	let count = 0;

	// Only count top-level blocks that contain the hero pattern
	blocks.forEach( ( block ) => {
		if ( containsFAUHeroPattern( block ) ) {
			count++;
		}
	} );

	return count;
}

// Teaser Image panel --------------------------------------------------------

const TEASER_IMAGE_META_KEY = '_faue_teaser_image_id';

const TeaserImagePanel = () => {
	const { teaserImageId, postType } = useSelect( ( sel ) => {
		const meta = sel( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
		return {
			teaserImageId: meta[ TEASER_IMAGE_META_KEY ] || 0,
			postType: sel( 'core/editor' ).getCurrentPostType(),
		};
	}, [] );

	const { editPost } = useDispatch( 'core/editor' );

	// Only show for posts and pages
	if ( postType !== 'post' && postType !== 'page' ) {
		return null;
	}

	const media = useSelect(
		( sel ) => {
			if ( ! teaserImageId ) {
				return null;
			}
			return sel( 'core' ).getMedia( teaserImageId );
		},
		[ teaserImageId ]
	);

	const setTeaserImage = ( image ) => {
		editPost( {
			meta: { [ TEASER_IMAGE_META_KEY ]: image ? image.id : 0 },
		} );
	};

	const removeTeaserImage = () => {
		editPost( {
			meta: { [ TEASER_IMAGE_META_KEY ]: 0 },
		} );
	};

	return (
		<PluginDocumentSettingPanel
			name="fau-teaser-image"
			title={ __( 'Teaser Image', 'fau-elemental' ) }
		>
			<p style={ { fontSize: '12px', color: '#757575', marginTop: 0 } }>
				{ __(
					'Override the featured image in teaser grids and portal menus.',
					'fau-elemental'
				) }
			</p>
			<MediaUploadCheck>
				<MediaUpload
					onSelect={ setTeaserImage }
					allowedTypes={ [ 'image' ] }
					value={ teaserImageId }
					render={ ( { open } ) => (
						<div>
							{ teaserImageId && media ? (
								<Fragment>
									<div
										style={ { marginBottom: '8px', cursor: 'pointer' } }
										onClick={ open }
										onKeyDown={ ( e ) => {
											if ( e.key === 'Enter' || e.key === ' ' ) {
												open();
											}
										} }
										role="button"
										tabIndex={ 0 }
									>
										<ResponsiveWrapper
											naturalWidth={
												media?.media_details?.width || 300
											}
											naturalHeight={
												media?.media_details?.height || 200
											}
										>
											<img
												src={
													media?.source_url || ''
												}
												alt={
													media?.alt_text || ''
												}
											/>
										</ResponsiveWrapper>
									</div>
									<div style={ { display: 'flex', gap: '8px' } }>
										<Button variant="secondary" onClick={ open }>
											{ __( 'Replace', 'fau-elemental' ) }
										</Button>
										<Button
											variant="link"
											isDestructive
											onClick={ removeTeaserImage }
										>
											{ __( 'Remove', 'fau-elemental' ) }
										</Button>
									</div>
								</Fragment>
							) : teaserImageId && ! media ? (
								<Spinner />
							) : (
								<Button variant="secondary" onClick={ open }>
									{ __( 'Set teaser image', 'fau-elemental' ) }
								</Button>
							) }
						</div>
					) }
				/>
			</MediaUploadCheck>
		</PluginDocumentSettingPanel>
	);
};

registerPlugin( 'fau-teaser-image-panel', {
	render: TeaserImagePanel,
} );

// Legacy sidebar helpers ----------------------------------------------------
const legacySidebarContext = window.fauElementalLegacySidebar || null;

const escapeHtml = ( value = '' ) =>
	String( value )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );

const escapeAttribute = ( value = '' ) =>
	escapeHtml( value ).replace( /"/g, '&quot;' );

const parseHtmlToBlocks = ( html ) => {
	const trimmed = ( html || '' ).trim();
	if ( ! trimmed ) {
		return [];
	}

	try {
		const parsed = rawHandler( { HTML: trimmed } ) || [];
		if ( parsed.length ) {
			return parsed;
		}
	} catch ( error ) {
		// Fallback handled below
	}

	return [
		createBlock( 'core/paragraph', {
			content: trimmed,
		} ),
	];
};

const buildListBlockFromLinks = ( links ) => {
	if ( ! Array.isArray( links ) || ! links.length ) {
		return null;
	}

	const itemsHtml = links
		.map( ( entry ) => {
			const label = escapeHtml( entry.title || '' );
			if ( entry.url ) {
				return `<li><a href="${ escapeAttribute(
					entry.url
				) }">${ label }</a></li>`;
			}
			return `<li>${ label }</li>`;
		} )
		.join( '' );

	if ( ! itemsHtml ) {
		return null;
	}

	return createBlock( 'core/list', {
		values: itemsHtml,
	} );
};

const buildLegacyBlocks = ( data, strings = {} ) => {
	const blocks = [];
	if ( ! data ) {
		return blocks;
	}

	const headingLabel = ( text, fallback ) => text || fallback || '';

	const addHeading = ( text ) => {
		if ( ! text ) {
			return;
		}
		blocks.push(
			createBlock( 'core/heading', {
				level: 3,
				content: text,
			} )
		);
	};

	const addContentBlocks = ( html ) => {
		parseHtmlToBlocks( html ).forEach( ( block ) => blocks.push( block ) );
	};

	if ( data.top ) {
		addHeading( data.top.title );
		addContentBlocks( data.top.content );
	}

	if ( data.contacts ) {
		const contactHeading = headingLabel(
			data.contacts.title,
			strings.contactsLabel || __( 'Contacts', 'fau-elemental' )
		);
		addHeading( contactHeading );
		if ( data.contacts.shortcode ) {
			blocks.push(
				createBlock( 'core/shortcode', {
					text: data.contacts.shortcode,
				} )
			);
		}
	}

	if ( data.linkBlocks && data.linkBlocks.length ) {
		data.linkBlocks.forEach( ( block ) => {
			if ( block.title ) {
				addHeading( block.title );
			}
			const listBlock = buildListBlockFromLinks( block.links );
			if ( listBlock ) {
				blocks.push( listBlock );
			}
		} );
	}

	if ( data.bottom ) {
		addHeading( data.bottom.title );
		addContentBlocks( data.bottom.content );
	}

	return blocks;
};

const cardSpacingStyle = { marginBottom: '16px' };
const shortcodeBoxStyle = {
	backgroundColor: '#f6f7f7',
	borderRadius: '4px',
	fontFamily: 'monospace',
	padding: '8px 12px',
	display: 'inline-block',
	marginBottom: '12px',
};
const actionsSpacingStyle = {
	marginTop: '16px',
	display: 'flex',
	flexDirection: 'column',
	gap: '8px',
};

const LegacyTextSection = ( { label, data, strings, sectionKey } ) => {
	if ( ! data || ( ! data.title && ! data.content ) ) {
		return null;
	}

	return (
		<Card key={ sectionKey } style={ cardSpacingStyle }>
			<CardHeader>
				<Text variant="title.small">{ label }</Text>
			</CardHeader>
			<CardBody>
				{ data.title && (
					<Text as="p">
						<strong>
							{ strings.titleLabel ||
								__( 'Title', 'fau-elemental' ) }
							:
						</strong>{ ' ' }
						{ data.title }
					</Text>
				) }
				{ data.content && <RawHTML>{ data.content }</RawHTML> }
			</CardBody>
		</Card>
	);
};

const LegacyListSection = ( {
	label,
	title,
	entries,
	strings,
	fallback,
	sectionKey,
} ) => {
	if ( ! entries || ! entries.length ) {
		return null;
	}

	return (
		<Card key={ sectionKey } style={ cardSpacingStyle }>
			<CardHeader>
				<Text variant="title.small">{ label }</Text>
			</CardHeader>
			<CardBody>
				{ title && (
					<Text as="p">
						<strong>
							{ strings.titleLabel ||
								__( 'Title', 'fau-elemental' ) }
							:
						</strong>{ ' ' }
						{ title }
					</Text>
				) }
				{ entries.map( ( entry, index ) => (
					<div key={ `${ sectionKey }-${ entry.id || index }` }>
						<Text as="p">
							<strong>{ entry.title || fallback }</strong>
						</Text>
						{ entry.url && (
							<ExternalLink href={ entry.url }>
								{ entry.url }
							</ExternalLink>
						) }
					</div>
				) ) }
			</CardBody>
		</Card>
	);
};

const LegacyContactsSection = ( { data, strings } ) => {
	if ( ! data ) {
		return null;
	}

	const label = strings.contactsLabel || __( 'Contacts', 'fau-elemental' );
	const shortcode = data.shortcode;
	const entries = data.items || [];
	const shortcodeLabel =
		strings.shortcodeLabel || __( 'Legacy shortcode', 'fau-elemental' );
	const shortcodeDescription =
		strings.shortcodeDescription ||
		__(
			'Add this shortcode to display the selected contacts.',
			'fau-elemental'
		);

	return (
		<Card key="legacy-contacts" style={ cardSpacingStyle }>
			<CardHeader>
				<Text variant="title.small">{ label }</Text>
			</CardHeader>
			<CardBody>
				{ data.title && (
					<Text as="p">
						<strong>
							{ strings.titleLabel ||
								__( 'Title', 'fau-elemental' ) }
							:
						</strong>{ ' ' }
						{ data.title }
					</Text>
				) }
				{ shortcode && (
					<Fragment>
						<Text as="p">
							<strong>{ shortcodeLabel }</strong>
						</Text>
						<code style={ shortcodeBoxStyle }>{ shortcode }</code>
						<Text as="p">{ shortcodeDescription }</Text>
					</Fragment>
				) }
				{ entries.length > 0 && (
					<div>
						<Text as="p">
							<strong>
								{ strings.linksLabel ||
									__( 'Links', 'fau-elemental' ) }
							</strong>
						</Text>
						<ul className="fau-legacy-sidebar__list">
							{ entries.map( ( entry, index ) => (
								<li
									key={ `${
										entry.id || 'contact'
									}-${ index }` }
								>
									<Text as="span">
										{ entry.title ||
											strings.contactFallback ||
											__( 'Contact', 'fau-elemental' ) }
									</Text>
									{ entry.id && (
										<Text as="span">{ ` (ID ${ entry.id })` }</Text>
									) }
								</li>
							) ) }
						</ul>
					</div>
				) }
			</CardBody>
		</Card>
	);
};

const getLegacySidebarSections = ( data, strings ) => {
	const sections = [];

	if ( data?.top && ( data.top.title || data.top.content ) ) {
		sections.push(
			<LegacyTextSection
				key="legacy-top"
				sectionKey="legacy-top"
				label={
					strings.textTopLabel || __( 'Top text', 'fau-elemental' )
				}
				data={ data.top }
				strings={ strings }
			/>
		);
	}

	if ( data?.contacts?.items?.length ) {
		sections.push(
			<LegacyContactsSection
				key="legacy-contacts"
				data={ data.contacts }
				strings={ strings }
			/>
		);
	}

	if ( data?.linkBlocks?.length ) {
		data.linkBlocks.forEach( ( block ) => {
			if ( ! block.links?.length && ! block.title ) {
				return;
			}
			const blockLabel = strings.linkBlockLabel
				? strings.linkBlockLabel.replace( '%d', block.block )
				: sprintf(
						/* translators: %d: link block number */
						__( 'Link block %d', 'fau-elemental' ),
						block.block
				  );
			sections.push(
				<LegacyListSection
					key={ `legacy-link-block-${ block.block }` }
					sectionKey={ `legacy-link-block-${ block.block }` }
					label={ blockLabel }
					title={ block.title }
					entries={ block.links }
					strings={ strings }
					fallback={
						strings.linkFallback || __( 'Link', 'fau-elemental' )
					}
				/>
			);
		} );
	}

	if ( data?.bottom && ( data.bottom.title || data.bottom.content ) ) {
		sections.push(
			<LegacyTextSection
				key="legacy-bottom"
				sectionKey="legacy-bottom"
				label={
					strings.textBottomLabel ||
					__( 'Bottom text', 'fau-elemental' )
				}
				data={ data.bottom }
				strings={ strings }
			/>
		);
	}

	return sections.length ? sections : null;
};

if ( legacySidebarContext?.data?.hasLegacyData ) {
	const legacySidebarData = legacySidebarContext.data;
	const legacySidebarStrings = legacySidebarContext.strings || {};

	const LegacySidebarIcon = () => (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			height="24"
			viewBox="0 -960 960 960"
			width="24"
			fill="currentColor"
			role="img"
			aria-hidden="true"
		>
			<path d="M280-160q-50 0-85-35t-35-85H60l18-80h113q17-19 40-29.5t49-10.5q26 0 49 10.5t40 29.5h167l84-360H182l4-17q6-28 27.5-45.5T264-800h456l-37 160h117l120 160-40 200h-80q0 50-35 85t-85 35q-50 0-85-35t-35-85H400q0 50-35 85t-85 35Zm357-280h193l4-21-74-99h-95l-28 120Zm-19-273 2-7-84 360 2-7 34-146 46-200ZM20-427l20-80h220l-20 80H20Zm80-146 20-80h260l-20 80H100Zm180 333q17 0 28.5-11.5T320-280q0-17-11.5-28.5T280-320q-17 0-28.5 11.5T240-280q0 17 11.5 28.5T280-240Zm400 0q17 0 28.5-11.5T720-280q0-17-11.5-28.5T680-320q-17 0-28.5 11.5T640-280q0 17 11.5 28.5T680-240Z" />
		</svg>
	);

	const LegacySidebarControls = () => {
		const [ hasInserted, setHasInserted ] = useState( false );
		const sections = useMemo(
			() =>
				getLegacySidebarSections(
					legacySidebarData,
					legacySidebarStrings
				),
			[ legacySidebarData, legacySidebarStrings ]
		);
		const legacyBlocksPreview = useMemo(
			() => buildLegacyBlocks( legacySidebarData, legacySidebarStrings ),
			[ legacySidebarData, legacySidebarStrings ]
		);
		const canInsertLegacyBlocks = legacyBlocksPreview.length > 0;
		const orderMessage =
			legacySidebarData.order === 1
				? legacySidebarStrings.orderLinksFirst
				: legacySidebarData.order === 0
				? legacySidebarStrings.orderContactsFirst
				: '';

		const handleInsert = () => {
			const blocksToInsert = buildLegacyBlocks(
				legacySidebarData,
				legacySidebarStrings
			);
			if ( ! blocksToInsert.length ) {
				return;
			}
			dispatch( 'core/block-editor' ).insertBlocks( blocksToInsert );
			setHasInserted( true );
		};

		const title =
			legacySidebarStrings.panelTitle ||
			__( 'Migration Assistant', 'fau-elemental' );
		const menuLabel =
			legacySidebarStrings.menuLabel ||
			__( 'Migration Assistant', 'fau-elemental' );

		return (
			<Fragment>
				<PluginSidebarMoreMenuItem target="fau-legacy-sidebar">
					{ menuLabel }
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="fau-legacy-sidebar"
					title={ title }
					icon={ <LegacySidebarIcon /> }
				>
					<div style={ { padding: '16px 20px' } }>
						<Text as="p">
							{ legacySidebarStrings.panelDescription ||
								__(
									'This page still holds sidebar entries from the previous theme. Review the content below or append it to the page as a Classic block.',
									'fau-elemental'
								) }
						</Text>
						{ orderMessage && (
							<Notice status="info" isDismissible={ false }>
								{ orderMessage }
							</Notice>
						) }
						{ sections && sections.length > 0 && (
							<div
								style={ {
									display: 'flex',
									flexDirection: 'column',
									gap: '16px',
									marginTop: '12px',
								} }
							>
								{ sections }
							</div>
						) }
						{ ( canInsertLegacyBlocks || hasInserted ) && (
							<div style={ actionsSpacingStyle }>
								{ canInsertLegacyBlocks && (
									<Button
										variant="secondary"
										onClick={ handleInsert }
										disabled={
											hasInserted ||
											! canInsertLegacyBlocks
										}
									>
										{ hasInserted
											? legacySidebarStrings.insertedLabel ||
											  __(
													'Legacy sidebar content was inserted.',
													'fau-elemental'
											  )
											: legacySidebarStrings.insertButton ||
											  __(
													'Insert legacy sidebar content',
													'fau-elemental'
											  ) }
									</Button>
								) }
								{ hasInserted && (
									<Notice
										status="success"
										isDismissible={ false }
									>
										{ legacySidebarStrings.insertedLabel ||
											__(
												'Legacy sidebar content was inserted at the bottom of the page.',
												'fau-elemental'
											) }
									</Notice>
								) }
							</div>
						) }
					</div>
				</PluginSidebar>
			</Fragment>
		);
	};

	registerPlugin( 'fau-legacy-sidebar-toolbar', {
		render: LegacySidebarControls,
		icon: <LegacySidebarIcon />,
	} );
}

const portalMenuSettings = document.getElementById(
	'fau_elemental_portal_menu_settings'
);
if ( portalMenuSettings ) {
	const portalMenuIdSelect =
		portalMenuSettings.querySelector( '#portal_menu_id' );
	let currentTemplate = null;
	subscribe( () => {
		const template =
			select( 'core/editor' ).getEditedPostAttribute( 'template' );
		if ( template !== currentTemplate && template !== undefined ) {
			currentTemplate = template;
			if (
				currentTemplate &&
				currentTemplate.includes( 'portal-page/portal-page.php' )
			) {
				portalMenuSettings.classList.remove(
					'fau-portal-menu-template-not-active'
				);
			} else {
				portalMenuSettings.classList.add(
					'fau-portal-menu-template-not-active'
				);
				if ( portalMenuIdSelect ) {
					portalMenuIdSelect.value = '';
				}
			}
		}
	} );
}
