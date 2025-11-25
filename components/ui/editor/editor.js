import { createBlock } from '@wordpress/blocks';
import {
	Button,
	Notice,
	Card,
	CardBody,
	CardHeader,
	ExternalLink,
	PanelBody,
	__experimentalText as Text,
} from '@wordpress/components';
import { select, subscribe, dispatch } from '@wordpress/data';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
// import { addFilter } from '@wordpress/hooks';
// import { createHigherOrderComponent } from '@wordpress/compose';
import { __, sprintf } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { Fragment, RawHTML, useMemo, useState } from '@wordpress/element';
import { unregisterFormatType } from '@wordpress/rich-text';

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

// Legacy sidebar helpers ----------------------------------------------------
const legacySidebarContext = window.fauElementalLegacySidebar || null;

const escapeHtml = ( value = '' ) =>
	String( value )
		.replace( /&/g, '&amp;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );

const escapeAttribute = ( value = '' ) =>
	escapeHtml( value ).replace( /"/g, '&quot;' );

const buildLegacySidebarHtml = ( data, strings ) => {
	if ( ! data ) {
		return '';
	}

	const sections = [];

	if ( data.top && ( data.top.title || data.top.content ) ) {
		const heading = data.top.title
			? `<h3>${ escapeHtml( data.top.title ) }</h3>`
			: '';
		const body = data.top.content || '';
		sections.push(
			`<section class="legacy-sidebar-section legacy-sidebar-text-top">${ heading }${ body }</section>`
		);
	}

	if ( data.contacts && data.contacts.items && data.contacts.items.length ) {
		const list = data.contacts.items
			.map( ( item ) => {
				const label = item.title || strings.contactFallback || '';
				const linkLabel = escapeHtml( label );
				const url = item.url
					? `<span class="legacy-sidebar-link">${ escapeHtml( item.url ) }</span>`
					: '';
				return `<li><span class="legacy-sidebar-item-title">${ linkLabel }</span>${ url }</li>`;
			} )
			.join( '' );
		const heading = data.contacts.title
			? `<h3>${ escapeHtml( data.contacts.title ) }</h3>`
			: '';
		sections.push(
			`<section class="legacy-sidebar-section legacy-sidebar-contacts">${ heading }<ul>${ list }</ul></section>`
		);
	}

	if ( data.linkBlocks && data.linkBlocks.length ) {
		data.linkBlocks.forEach( ( block ) => {
			if ( ! block.links || ! block.links.length ) {
				return;
			}
			const heading = block.title
				? `<h3>${ escapeHtml( block.title ) }</h3>`
				: '';
			const list = block.links
				.map( ( entry ) => {
					const label = entry.title || strings.linkFallback || '';
					const link = entry.url
						? `<a href="${ escapeAttribute( entry.url ) }">${ escapeHtml( label ) }</a>`
						: `<span class="legacy-sidebar-item-title">${ escapeHtml( label ) }</span>`;
					return `<li>${ link }${ entry.url ? '' : '' }</li>`;
				} )
				.join( '' );
			sections.push(
				`<section class="legacy-sidebar-section legacy-sidebar-links">${ heading }<ul>${ list }</ul></section>`
			);
		} );
	}

	if ( data.bottom && ( data.bottom.title || data.bottom.content ) ) {
		const heading = data.bottom.title
			? `<h3>${ escapeHtml( data.bottom.title ) }</h3>`
			: '';
		const body = data.bottom.content || '';
		sections.push(
			`<section class="legacy-sidebar-section legacy-sidebar-text-bottom">${ heading }${ body }</section>`
		);
	}

	return sections.join( '' );
};

const cardSpacingStyle = { marginBottom: '16px' };
const actionsSpacingStyle = { marginTop: '16px', display: 'flex', flexDirection: 'column', gap: '8px' };

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
							{ strings.titleLabel || __( 'Title', 'fau-elemental' ) }:
						</strong>{' '}
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
 	withIdentifiers = false,
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
							{ strings.titleLabel || __( 'Title', 'fau-elemental' ) }:
						</strong>{' '}
						{ title }
					</Text>
				) }
				{ entries.map( ( entry, index ) => (
					<div key={ `${ sectionKey }-${ entry.id || index }` }>
					<Text as="p">
						<strong>{ entry.title || fallback }</strong>
						{ withIdentifiers && entry.id
							? ` (ID ${ entry.id })`
							: '' }
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

const getLegacySidebarSections = ( data, strings ) => {
	const sections = [];

	if ( data?.top && ( data.top.title || data.top.content ) ) {
		sections.push(
			<LegacyTextSection
				key="legacy-top"
				sectionKey="legacy-top"
				label={ strings.textTopLabel || __( 'Top text', 'fau-elemental' ) }
				data={ data.top }
				strings={ strings }
			/>
		);
	}

	if ( data?.contacts?.items?.length ) {
		sections.push(
			<LegacyListSection
				key="legacy-contacts"
				sectionKey="legacy-contacts"
				label={ strings.contactsLabel || __( 'Contacts', 'fau-elemental' ) }
				title={ data.contacts.title }
				entries={ data.contacts.items }
				strings={ strings }
				fallback={ strings.contactFallback || __( 'Contact', 'fau-elemental' ) }
				withIdentifiers
			/>
		);
	}

	if ( data?.linkBlocks?.length ) {
		data.linkBlocks.forEach( ( block ) => {
			if ( ! block.links?.length && ! block.title ) {
				return;
			}
			sections.push(
				<LegacyListSection
					key={ `legacy-link-block-${ block.block }` }
					sectionKey={ `legacy-link-block-${ block.block }` }
					label={
						strings.linkBlockLabel
							? sprintf( strings.linkBlockLabel, block.block )
							: sprintf( __( 'Link block %d', 'fau-elemental' ), block.block )
					}
					title={ block.title }
					entries={ block.links }
					strings={ strings }
					fallback={ strings.linkFallback || __( 'Link', 'fau-elemental' ) }
				/>
			);
		} );
	}

	if ( data?.bottom && ( data.bottom.title || data.bottom.content ) ) {
		sections.push(
			<LegacyTextSection
				key="legacy-bottom"
				sectionKey="legacy-bottom"
				label={ strings.textBottomLabel || __( 'Bottom text', 'fau-elemental' ) }
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

	const LegacySidebarControls = () => {
		const [ hasInserted, setHasInserted ] = useState( false );
		const sections = useMemo(
			() => getLegacySidebarSections( legacySidebarData, legacySidebarStrings ),
			[ legacySidebarData, legacySidebarStrings ]
		);
		const htmlContent = useMemo(
			() => buildLegacySidebarHtml( legacySidebarData, legacySidebarStrings ),
			[ legacySidebarData, legacySidebarStrings ]
		);
		const orderMessage =
			legacySidebarData.order === 1
				? legacySidebarStrings.orderLinksFirst
				: legacySidebarData.order === 0
				? legacySidebarStrings.orderContactsFirst
				: '';

		const handleInsert = () => {
			if ( ! htmlContent ) {
				return;
			}
			const classicBlock = createBlock( 'core/freeform', {
				content: htmlContent,
			} );
			dispatch( 'core/block-editor' ).insertBlocks( classicBlock );
			setHasInserted( true );
		};

		const title =
			legacySidebarStrings.panelTitle ||
			__( 'Legacy Sidebar Content', 'fau-elemental' );
		const menuLabel =
			legacySidebarStrings.menuLabel ||
			__( 'Legacy Sidebar', 'fau-elemental' );

		return (
			<Fragment>
				<PluginSidebarMoreMenuItem target="fau-legacy-sidebar">
					{ menuLabel }
				</PluginSidebarMoreMenuItem>
				<PluginSidebar
					name="fau-legacy-sidebar"
					title={ title }
					icon="index-card"
				>
					<PanelBody initialOpen title={ title }>
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
						{ ( htmlContent || hasInserted ) && (
							<div style={ actionsSpacingStyle }>
								{ htmlContent && (
									<Button
										variant="secondary"
										onClick={ handleInsert }
										disabled={ hasInserted }
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
												)
										}
									</Button>
								) }
								{ hasInserted && (
									<Notice status="success" isDismissible={ false }>
										{ legacySidebarStrings.insertedLabel ||
											__(
												'Legacy sidebar content was inserted at the bottom of the page.',
												'fau-elemental'
											)
										}
									</Notice>
								) }
							</div>
						) }
					</PanelBody>
				</PluginSidebar>
			</Fragment>
		);
	};

	registerPlugin( 'fau-legacy-sidebar-toolbar', {
		render: LegacySidebarControls,
		icon: 'index-card',
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
