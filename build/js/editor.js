/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactJSXRuntime"];

/***/ }),

/***/ "@wordpress/block-editor":
/*!*************************************!*\
  !*** external ["wp","blockEditor"] ***!
  \*************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blockEditor"];

/***/ }),

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blocks"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["hooks"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!******************************!*\
  !*** ./src/editor/editor.js ***!
  \******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const {
  addFilter
} = wp.hooks;
const {
  createHigherOrderComponent
} = wp.compose;
const {
  useEffect
} = wp.element;

/**
 * Add selected block classes to body
 * This filter adds specific classes to the body tag when certain blocks are selected
 * to allow for contextual styling in the editor.
 */
addFilter('editor.BlockEdit', 'fau-elemental/with-block-selected-classes', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      isSelected,
      name,
      attributes
    } = props;
    useEffect(() => {
      if (isSelected) {
        // Define block types and their corresponding classes
        const blockClasses = {
          'core/button': 'faue-is-button-block-selected',
          'core/heading': 'faue-is-heading-block-selected',
          'core/paragraph': 'faue-is-paragraph-block-selected',
          'core/image': 'faue-is-image-block-selected'
        };

        // Add/remove the basic block type class
        Object.entries(blockClasses).forEach(([blockName, className]) => {
          document.body.classList.toggle(className, name === blockName);
        });

        // Handle special variations (like intro-text)
        const isParagraph = name === 'core/paragraph';
        const isIntroText = isParagraph && attributes.className?.includes('intro-text');
        document.body.classList.toggle('faue-is-intro-text-selected', isIntroText);
      }
    }, [isSelected, name, attributes?.className]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
      ...props
    });
  };
}, 'withBlockSelectedClasses'));
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*****************************************!*\
  !*** ./src/blocks/core-button/index.js ***!
  \*****************************************/
// Core button block customizations
wp.domReady(() => {
  wp.blocks.unregisterBlockStyle('core/button', ['fill', 'outline']);
  wp.blocks.registerBlockStyle('core/button', {
    name: 'primary',
    label: 'Primary',
    isDefault: true
  });
  wp.blocks.registerBlockStyle('core/button', {
    name: 'secondary',
    label: 'Secondary',
    isDefault: false
  });
  wp.blocks.registerBlockStyle('core/button', {
    name: 'tertiary',
    label: 'Tertiary',
    isDefault: false
  });
});
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************************!*\
  !*** ./src/blocks/core-heading/index.js ***!
  \******************************************/

})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!********************************************!*\
  !*** ./src/blocks/core-paragraph/index.js ***!
  \********************************************/
const {
  registerBlockVariation
} = wp.blocks;

// Register "Intro Text" variation for core/paragraph with an icon
registerBlockVariation('core/paragraph', {
  name: 'text',
  title: 'Text',
  description: 'A paragraph.',
  attributes: {
    className: 'text'
  },
  icon: 'editor-paragraph',
  isDefault: true,
  scope: ['block', 'inserter', 'transform']
});

// Register "Intro Text" variation for core/paragraph with an icon
registerBlockVariation('core/paragraph', {
  name: 'intro-text',
  title: 'Intro Text',
  description: 'A paragraph styled as an introduction.',
  attributes: {
    className: 'intro-text'
  },
  icon: 'editor-paragraph',
  isDefault: false,
  scope: ['block', 'inserter', 'transform']
});

// Register "Small Text" variation for core/paragraph with an icon
registerBlockVariation('core/paragraph', {
  name: 'small-text',
  title: 'Small Text',
  description: 'A smaller paragraph for fine print or secondary content.',
  attributes: {
    className: 'small-text'
  },
  icon: 'editor-paragraph',
  isDefault: false,
  scope: ['block', 'inserter', 'transform']
});
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!***************************************!*\
  !*** ./src/blocks/core-list/index.js ***!
  \***************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const {
  addFilter
} = wp.hooks;
const {
  createHigherOrderComponent
} = wp.compose;
const {
  InspectorControls
} = wp.blockEditor;
const {
  PanelBody,
  SelectControl
} = wp.components;

// Add list style controls
addFilter('editor.BlockEdit', 'fau-elemental/with-list-style-controls', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      attributes,
      setAttributes,
      name
    } = props;

    // Only show for list blocks
    if (name !== 'core/list') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
        ...props
      });
    }

    // Only show for unordered lists
    const isUnordered = !attributes.ordered;
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.Fragment, {
      children: [isUnordered && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(PanelBody, {
          title: "List Style Settings",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(SelectControl, {
            label: "List Style",
            value: attributes.className?.includes('list-icons') ? 'list-icons' : 'dots',
            options: [{
              label: 'Dots',
              value: 'dots'
            }, {
              label: 'Icons',
              value: 'list-icons'
            }],
            onChange: value => {
              // Get current classes as an array
              const currentClasses = attributes.className ? attributes.className.split(' ').filter(cls => cls !== 'list-icons') : [];

              // Add the new class if it's not 'dots'
              if (value !== 'dots') {
                currentClasses.push(value);
              }

              // Set the new className
              setAttributes({
                className: currentClasses.length > 0 ? currentClasses.join(' ') : undefined
              });
            }
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
        ...props
      })]
    });
  };
}, 'withListStyleControls'));
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!****************************************!*\
  !*** ./src/blocks/core-table/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__);

const {
  addFilter
} = wp.hooks;
const {
  createHigherOrderComponent
} = wp.compose;
const {
  useEffect,
  Fragment
} = wp.element;
const {
  InspectorControls
} = wp.blockEditor;
const {
  PanelBody,
  TextControl
} = wp.components;

// Disable rich text formatting for table cells
const disableRichTextFormatting = (settings, name) => {
  if (name === 'core/table') {
    return {
      ...settings,
      attributes: {
        ...settings.attributes,
        tableHeading: {
          type: 'string',
          default: ''
        }
      },
      supports: {
        ...settings.supports,
        typography: false,
        color: false,
        align: false,
        spacing: false,
        anchor: false
      }
    };
  }
  return settings;
};
wp.domReady(() => {
  // Unregister default block styles
  wp.blocks.unregisterBlockStyle('core/table', ['regular', 'stripes']);

  // Remove typography and color support
  wp.blocks.unregisterBlockVariation('core/table', 'typography');
  wp.blocks.unregisterBlockVariation('core/table', 'color');

  // Disable rich text formatting for table cells
  //wp.richText.unregisterFormatType( 'core/bold' );
  //wp.richText.unregisterFormatType( 'core/italic' );
  //wp.richText.unregisterFormatType( 'core/link' );
});

// Remove formatting options from block registration
addFilter('blocks.registerBlockType', 'fau-elemental/remove-table-supports', disableRichTextFormatting);

// Remove cell formatting options
addFilter('blocks.getSaveContent.extraProps', 'fau-elemental/remove-table-cell-formats', (props, blockType, attributes) => {
  if (blockType.name === 'core/table') {
    if (props.className) {
      props.className = props.className.replace(/has-[\w-]+-(color|background|font-size|text-align)/, '');
    }
  }
  return props;
});
addFilter('blocks.getSaveElement', 'fau-elemental/with-table-heading-save', (element, blockType, attributes) => {
  if (blockType.name !== 'core/table' || !attributes.tableHeading) {
    return element;
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
    className: "wp-block-table-wrapper",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h3", {
      className: "wp-block-table__heading",
      children: attributes.tableHeading
    }), element]
  });
});

// Add table heading control and display
addFilter('editor.BlockEdit', 'fau-elemental/with-table-heading', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      name,
      attributes,
      setAttributes
    } = props;
    if (name !== 'core/table') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
        ...props
      });
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)(Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(PanelBody, {
          title: "Table Settings",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(TextControl, {
            label: "Table Heading",
            value: attributes.tableHeading || '',
            onChange: value => setAttributes({
              tableHeading: value
            })
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsxs)("div", {
        className: "wp-block-table-wrapper",
        children: [attributes.tableHeading && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)("h3", {
          className: "wp-block-table__heading",
          children: attributes.tableHeading
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
          ...props
        })]
      })]
    });
  };
}, 'withTableHeading'));
// Additional cleanup for any remaining formatting buttons
addFilter('editor.BlockEdit', 'fau-elemental/with-table-formatting-removed', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      name
    } = props;
    useEffect(() => {
      if (name === 'core/table') {
        const removeFormattingButtons = () => {
          // Target all possible toolbar locations
          const selectors = ['.block-editor-table-block__fixed-toolbar', '.block-editor-block-toolbar', '.block-editor-table-cell-toolbar', '.block-editor-rich-text__inline-format-toolbar-group', '.components-toolbar-group', '.block-editor-rich-text__inline-format-toolbar'];
          const toolbars = document.querySelectorAll(selectors.join(', '));
          toolbars.forEach(toolbar => {
            if (toolbar) {
              // Target all formatting buttons and controls
              const formatButtons = toolbar.querySelectorAll('[aria-label*="Bold"], ' + '[aria-label*="Italic"], ' + '[aria-label*="Link"], ' + '[aria-label*="caption"], ' + 'button[aria-label*="More text settings"], ' + '.block-editor-format-toolbar, ' + '.format-library-text-color-button, ' + '.components-dropdown-menu__toggle');
              formatButtons.forEach(button => {
                button.style.display = 'none';
              });

              // Hide the entire toolbar if it's empty
              if (toolbar.children.length === 0 || Array.from(toolbar.children).every(child => child.style.display === 'none')) {
                toolbar.style.display = 'none';
              }
            }
          });
        };

        // Initial removal
        removeFormattingButtons();

        // Set up observer for dynamically added buttons
        const observer = new MutationObserver(mutations => {
          removeFormattingButtons();
        });

        // Observe the entire editor area
        const editor = document.querySelector('.block-editor-block-list__layout');
        if (editor) {
          observer.observe(editor, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
          });
        }
        return () => observer.disconnect();
      }
    }, [name]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
      ...props
    });
  };
}, 'withTableFormattingRemoved'));
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!****************************************!*\
  !*** ./src/blocks/core-image/index.js ***!
  \****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__);






// Unregister the rounded style and register new styles for image blocks

wp.domReady(() => {
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__.unregisterBlockStyle)('core/image', ['default', 'rounded']);
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__.registerBlockStyle)('core/image', {
    name: 'large',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Large', 'fau-elemental'),
    isDefault: true
  });
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__.registerBlockStyle)('core/image', {
    name: 'medium',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Medium', 'fau-elemental'),
    isDefault: false
  });
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__.registerBlockStyle)('core/image', {
    name: 'small',
    label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Small', 'fau-elemental'),
    isDefault: false
  });
});

// Register a default block variation with preconfigured attributes
(0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_4__.registerBlockVariation)('core/image', {
  name: 'fau-default-image',
  isDefault: true,
  attributes: {
    align: 'full'
  }
});

/**
 * Adds a custom 'copyrightInfo' attribute to all Image blocks and modifies block supports.
 *
 * @param {Object} settings The block settings for the registered block type.
 * @param {string} name     The block type name, including namespace.
 * @return {Object}         The modified block settings.
 */
function editImageBlockAttributesAndSupports(settings, name) {
  // Only modify Image blocks
  if (name !== 'core/image') {
    return settings;
  }

  // Modify block supports
  settings.supports = {
    ...settings.supports,
    // Disable specific features
    align: ['full', 'center'],
    // Keep alignment support
    filter: false,
    shadow: false
  };
  settings.attributes = {
    ...settings.attributes,
    // Set default alignment to full
    align: {
      type: 'string',
      default: 'full'
    },
    // Add copyright info attribute
    copyrightInfo: {
      type: 'string',
      default: ''
    }
  };
  return settings;
}
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__.addFilter)('blocks.registerBlockType', 'fau-elemental/add-copyright-info-attribute', editImageBlockAttributesAndSupports);

/**
 * Adds a custom 'copyrightInfo' attribute text input field to all Image blocks in the editor.
 * 
 * @param {*} BlockEdit 
 * @returns 
 */
function addCopyrightInfoInspectorControls(BlockEdit) {
  return props => {
    const {
      name,
      attributes,
      setAttributes
    } = props;

    // Early return if the block is not the Image block.
    if (name !== 'core/image') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(BlockEdit, {
        ...props
      });
    }

    // Retrieve selected attributes from the block.
    const {
      copyrightInfo
    } = attributes;
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(BlockEdit, {
        ...props
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_2__.InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.PanelBody, {
          title: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Additional Settings', 'fau-elemental'),
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_5__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_3__.TextControl, {
            label: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_0__.__)('Copyright Info', 'fau-elemental'),
            value: copyrightInfo,
            onChange: value => setAttributes({
              copyrightInfo: value
            })
          })
        })
      })]
    });
  };
}
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_1__.addFilter)('editor.BlockEdit', 'fau-elemental/add-copyright-info-inspector-controls', addCopyrightInfoInspectorControls);
})();

/******/ })()
;
//# sourceMappingURL=editor.js.map