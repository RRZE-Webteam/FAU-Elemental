/**
 * Portal Menu Block
 * 
 * DISABLED: This old block is being replaced by the new fau-elemental/portalmenu block
 */

/*
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { InspectorControls, useBlockProps } = wp.blockEditor;
const { PanelBody, SelectControl, ToggleControl, Placeholder, Icon } =
	wp.components;
const { Fragment } = wp.element;

// Register Portal Menu Block
registerBlockType('fau/portal-menu', {
    title: __('FAU Portal Menu', 'fau-elemental'),
    description: __('Display a portal menu with optional thumbnails and layout options.', 'fau-elemental'),
    category: 'fau-blocks',
    icon: 'grid-view',
    supports: {
        html: false,
        multiple: true,
        reusable: true,
    },
    attributes: {
        menu: {
            type: 'string',
            default: '',
        },
        type: {
            type: 'string',
            default: 'grid',
        },
        ratio: {
            type: 'string',
            default: '33-33-33',
        },
        columns: {
            type: 'number',
            default: 3,
        },
        thumbnails: {
            type: 'boolean',
            default: true,
        },
        displaySubmenus: {
            type: 'boolean',
            default: true,
        },
        listview: {
            type: 'boolean',
            default: false,
        },
        hovertitle: {
            type: 'boolean',
            default: false,
        },
        megamenu: {
            type: 'boolean',
            default: false,
        }
    },

	edit: ( props ) => {
		const { attributes, setAttributes } = props;
		const {
			menu,
			type,
			ratio,
			columns,
			thumbnails,
			displaySubmenus,
			listview,
			hovertitle,
			megamenu,
		} = attributes;

		const blockProps = useBlockProps( {
			className: 'wp-block-fau-portal-menu',
		} );

		// Prepare menu options from the localized variable
		const menuOptions = window.fauPortalMenuOptions
			? window.fauPortalMenuOptions.menus
			: [ { value: '', label: __( 'No menus found', 'fau-elemental' ) } ];

		// Prepare layout type options
		const typeOptions = [
			{ value: 'grid', label: __( 'Grid', 'fau-elemental' ) },
			{ value: 'list', label: __( 'List', 'fau-elemental' ) },
		];

		// Prepare ratio options
		const ratioOptions = [
			{
				value: '33-33-33',
				label: __( 'Three columns (33-33-33)', 'fau-elemental' ),
			},
			{
				value: '50-50',
				label: __( 'Two columns (50-50)', 'fau-elemental' ),
			},
			{
				value: '25-25-25-25',
				label: __( 'Four columns (25-25-25-25)', 'fau-elemental' ),
			},
			{
				value: '25-50-25',
				label: __( 'Three columns (25-50-25)', 'fau-elemental' ),
			},
			{
				value: '60-40',
				label: __( 'Two columns (60-40)', 'fau-elemental' ),
			},
			{
				value: '40-60',
				label: __( 'Two columns (40-60)', 'fau-elemental' ),
			},
		];

    save: () => {
        // Rendering is done on the server side
        return null;
    },
});
*/ 