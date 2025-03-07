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

/***/ "@wordpress/blocks":
/*!********************************!*\
  !*** external ["wp","blocks"] ***!
  \********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["blocks"];

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
/*!**************************!*\
  !*** ./src/js/editor.js ***!
  \**************************/
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
          'core/image': 'faue-is-image-block-selected',
          'core/table': 'faue-is-table-block-selected'
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
/*!*******************************!*\
  !*** ./src/js/core-button.js ***!
  \*******************************/
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

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!*****************************!*\
  !*** ./src/js/core-text.js ***!
  \*****************************/
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
  getBlockType,
  registerBlockType,
  registerBlockVariation,
  unregisterBlockVariation
} = wp.blocks;
const {
  BlockControls,
  InspectorControls
} = wp.blockEditor;
const {
  ToolbarGroup,
  ToolbarButton,
  PanelBody,
  SelectControl
} = wp.components;

// Get the original Heading block
const headingBlock = getBlockType('core/heading');
wp.domReady(() => {
  // Register "Intro Text" variation for core/paragraph with an icon
  registerBlockVariation('core/paragraph', {
    name: 'text',
    title: 'Text',
    description: 'A paragraph.',
    attributes: {
      className: 'text'
    },
    icon: 'editor-paragraph',
    // Dashicon for text
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
    // Dashicon for text
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
    // Dashicon for paragraph text
    isDefault: false,
    scope: ['block', 'inserter', 'transform']
  });
});

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
/*!******************************!*\
  !*** ./src/js/core-table.js ***!
  \******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__);


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
  TextControl
} = wp.components;

// Unregister default styles
wp.domReady(() => {
  wp.blocks.unregisterBlockStyle('core/table', ['regular', 'stripes']);
});

// Add heading attribute
addFilter('blocks.registerBlockType', 'fau-elemental/table-heading', (settings, name) => {
  if (name !== 'core/table') {
    return settings;
  }
  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      tableHeading: {
        type: 'string',
        default: ''
      }
    },
    // Add save component to handle frontend rendering
    save: props => {
      const {
        attributes
      } = props;
      const blockProps = wp.blockEditor.useBlockProps.save({
        className: 'wp-block-table-wrapper'
      });

      // Get the original saved content
      const originalSaveElement = settings.save(props);
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
        ...blockProps,
        children: [attributes.tableHeading && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("div", {
          className: "wp-block-table__heading",
          children: attributes.tableHeading
        }), originalSaveElement]
      });
    }
  };
});

// Add inspector controls
const withInspectorControls = createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      attributes,
      setAttributes,
      name
    } = props;
    if (name !== 'core/table') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(BlockEdit, {
        ...props
      });
    }
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(InspectorControls, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(PanelBody, {
          title: "Table Settings",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(TextControl, {
            label: "Table Heading",
            value: attributes.tableHeading || '',
            onChange: value => setAttributes({
              tableHeading: value
            }),
            help: "Add a heading that will appear above the table"
          })
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsxs)("div", {
        className: "wp-block-table-wrapper",
        children: [attributes.tableHeading && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)("div", {
          className: "wp-block-table__heading",
          children: attributes.tableHeading
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_1__.jsx)(BlockEdit, {
          ...props
        })]
      })]
    });
  };
}, 'withInspectorControls');
addFilter('editor.BlockEdit', 'fau-elemental/with-inspector-controls', withInspectorControls);
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************!*\
  !*** ./src/js/core-image.js ***!
  \******************************/
// Core image block customizations
wp.domReady(() => {
  wp.blocks.unregisterBlockStyle('core/image', ['default', 'rounded']);
  wp.blocks.registerBlockStyle('core/image', {
    name: 'large',
    label: 'Large',
    isDefault: true
  });
  wp.blocks.registerBlockStyle('core/image', {
    name: 'medium',
    label: 'Medium',
    isDefault: false
  });
  wp.blocks.registerBlockStyle('core/image', {
    name: 'small',
    label: 'Small',
    isDefault: false
  });
});
})();

/******/ })()
;
//# sourceMappingURL=editor.js.map