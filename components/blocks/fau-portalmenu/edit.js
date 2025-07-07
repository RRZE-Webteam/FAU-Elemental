/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Panel,
	PanelBody,
	SelectControl,
	ToggleControl,
	RadioControl,
	Placeholder,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import EditorPreview from './EditorPreview';

/**
 * Edit component for the FAU Portal Menu block
 * WCAG 2.2 Level II compliant with full feature support
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'wp-block-fau-elemental-portalmenu',
	} );

	// Get available menus
	const menus = useSelect( ( select ) => {
		const menuItems = select( 'core' ).getMenus();
		return menuItems
			? menuItems.map( ( menu ) => ( {
					label: menu.name,
					value: menu.id.toString(),
			  } ) )
			: [];
	}, [] );

	// Add an empty option
	const menuOptions = [
		{ label: __( 'Select a menu', 'fau-elemental' ), value: '' },
		...menus,
	];

	// Add type options that match the backend configuration
	const typeOptions = [
		{ label: __( 'Type 1 (2:1 Ratio)', 'fau-elemental' ), value: '1' },
		{ label: __( 'Type 2 (3:2 Ratio)', 'fau-elemental' ), value: '2' },
		{ label: __( 'Type 3 (3:4 Ratio)', 'fau-elemental' ), value: '3' },
	];

	// Get the currently selected menu name for display
	const selectedMenu = menus.find(
		( menu ) => menu.value === attributes.menuId
	);
	const selectedMenuName = selectedMenu
		? selectedMenu.label
		: attributes.menuId;

	// Effect to store menu name when menuId changes
	useEffect( () => {
		if ( attributes.menuId && selectedMenu ) {
			setAttributes( { menuName: selectedMenu.label } );
		}
	}, [ attributes.menuId, selectedMenu ] );

	// Inspector controls for the settings sidebar
	const inspectorControls = (
		<InspectorControls>
			<Panel>
				<PanelBody
					title={ __( 'Menu Settings', 'fau-elemental' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Menu', 'fau-elemental' ) }
						value={ attributes.menuId }
						options={ menuOptions }
						onChange={ ( menuId ) => setAttributes( { menuId } ) }
						help={ __(
							'Select the navigation menu to display as portal menu.',
							'fau-elemental'
						) }
					/>

					<RadioControl
						label={ __( 'Display Type', 'fau-elemental' ) }
						selected={ attributes.type?.toString() || '1' }
						options={ typeOptions }
						onChange={ ( type ) =>
							setAttributes( { type: parseInt( type ) } )
						}
						help={ __(
							'Choose the aspect ratio for thumbnail images.',
							'fau-elemental'
						) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Display Options', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show Submenus', 'fau-elemental' ) }
						checked={ attributes.showSubs !== false }
						onChange={ ( showSubs ) =>
							setAttributes( { showSubs } )
						}
						help={ __(
							'Display submenu items below each main menu item.',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'List View', 'fau-elemental' ) }
						checked={ !! attributes.listView }
						onChange={ ( listView ) =>
							setAttributes( { listView } )
						}
						help={ __(
							'Display menu items in a vertical list instead of grid.',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'Hide Thumbnails', 'fau-elemental' ) }
						checked={ !! attributes.noThumbs }
						onChange={ ( noThumbs ) =>
							setAttributes( { noThumbs } )
						}
						help={ __(
							'Hide thumbnail images for all menu items.',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'No Fallback Images', 'fau-elemental' ) }
						checked={ !! attributes.noFallback }
						onChange={ ( noFallback ) =>
							setAttributes( { noFallback } )
						}
						help={ __(
							'Do not show fallback images when thumbnails are missing.',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'Dark Style', 'fau-elemental' ) }
						checked={ !! attributes.isDark }
						onChange={ ( isDark ) => setAttributes( { isDark } ) }
						help={ __(
							'Use dark background styling for the portal menu.',
							'fau-elemental'
						) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Advanced Options', 'fau-elemental' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Mega Navigation', 'fau-elemental' ) }
						checked={ !! attributes.isMegaNav }
						onChange={ ( isMegaNav ) =>
							setAttributes( { isMegaNav } )
						}
						help={ __(
							'Enable mega navigation features (advanced).',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'Hover Zoom Effect', 'fau-elemental' ) }
						checked={ !! attributes.hoverZoom }
						onChange={ ( hoverZoom ) =>
							setAttributes( { hoverZoom } )
						}
						help={ __(
							'Add zoom effect when hovering over thumbnails.',
							'fau-elemental'
						) }
					/>

					<ToggleControl
						label={ __( 'Hover Blur Effect', 'fau-elemental' ) }
						checked={ !! attributes.hoverBlur }
						onChange={ ( hoverBlur ) =>
							setAttributes( { hoverBlur } )
						}
						help={ __(
							'Add blur effect when hovering over thumbnails.',
							'fau-elemental'
						) }
					/>
				</PanelBody>
			</Panel>
		</InspectorControls>
	);

	// Return the block content
	return (
		<>
			{ inspectorControls }

			<div { ...blockProps }>
				{ attributes.menuId ? (
					<EditorPreview
						attributes={ attributes }
						selectedMenuName={ selectedMenuName }
					/>
				) : (
					<Placeholder
						label={ __( 'FAU Portal Menu', 'fau-elemental' ) }
						instructions={ __(
							'Please select a menu in the block settings panel to display as a portal menu.',
							'fau-elemental'
						) }
						icon="menu"
					/>
				) }
			</div>
		</>
	);
}
