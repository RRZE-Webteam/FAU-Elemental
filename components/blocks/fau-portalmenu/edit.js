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
	Placeholder,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useEffect } from '@wordpress/element';
import EditorPreview from './EditorPreview';

/**
 * Edit component for the FAU Portal Menu block
 */
export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps( {
		className: 'fau-portal-menu',
		'aria-label': __( 'Portal Menu', 'fau-elemental' ),
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

	// Get the currently selected menu name for display
	const selectedMenu = menus.find(
		( menu ) => menu.value === attributes.menuId
	);

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
					<EditorPreview attributes={ attributes } />
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
