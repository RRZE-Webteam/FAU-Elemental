/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "react/jsx-runtime":
/*!**********************************!*\
  !*** external "ReactJSXRuntime" ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["ReactJSXRuntime"];

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

addFilter('editor.BlockEdit', 'fau-elemental/with-selected-class', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      isSelected,
      name,
      attributes
    } = props;
    useEffect(() => {
      if (isSelected) {
        const blockTypes = {
          'core/heading': 'faue-is-heading-block-selected',
          'core/paragraph': 'faue-is-paragraph-block-selected',
          'core/button': 'faue-is-button-block-selected'
        };
        const isIntroText = name === 'core/paragraph' && attributes.className?.includes('intro-text');
        Object.keys(blockTypes).forEach(blockName => {
          document.body.classList.toggle(blockTypes[blockName], name === blockName);
        });
        document.body.classList.toggle('faue-is-intro-text-selected', isIntroText);
      }
    }, [isSelected, attributes.className]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
      ...props
    });
  };
}, 'withSelectedClass'));
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************!*\
  !*** ./src/js/core-button.js ***!
  \*******************************/
const {
  unregisterBlockStyle,
  registerBlockStyle
} = wp.blocks;
wp.domReady(() => {
  unregisterBlockStyle('core/button', ['fill', 'outline']);
  registerBlockStyle('core/button', {
    name: 'primary',
    label: 'Primary',
    isDefault: true
  });
  registerBlockStyle('core/button', {
    name: 'secondary',
    label: 'Secondary',
    isDefault: false
  });
  registerBlockStyle('core/button', {
    name: 'tertiary',
    label: 'Tertiary',
    isDefault: false
  });
});
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!**********************************!*\
  !*** ./src/js/core-paragraph.js ***!
  \**********************************/
const {
  registerBlockVariation
} = wp.blocks;
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
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!*****************************!*\
  !*** ./src/js/core-list.js ***!
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
  InspectorControls
} = wp.blockEditor;
const {
  PanelBody,
  SelectControl
} = wp.components;
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
  wp.richText.unregisterFormatType('core/bold');
  wp.richText.unregisterFormatType('core/italic');
  wp.richText.unregisterFormatType('core/link');
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

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!******************************!*\
  !*** ./src/js/core-image.js ***!
  \******************************/
const {
  unregisterBlockStyle,
  registerBlockStyle
} = wp.blocks;
wp.domReady(() => {
  unregisterBlockStyle('core/image', ['default', 'rounded']);
  registerBlockStyle('core/image', {
    name: 'large',
    label: 'Large',
    isDefault: true
  });
  registerBlockStyle('core/image', {
    name: 'medium',
    label: 'Medium',
    isDefault: false
  });
  registerBlockStyle('core/image', {
    name: 'small',
    label: 'Small',
    isDefault: false
  });
});
})();

/******/ })()
;
//# sourceMappingURL=editor.js.map