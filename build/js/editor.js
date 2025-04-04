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

/***/ "@wordpress/compose":
/*!*********************************!*\
  !*** external ["wp","compose"] ***!
  \*********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["compose"];

/***/ }),

/***/ "@wordpress/dom-ready":
/*!**********************************!*\
  !*** external ["wp","domReady"] ***!
  \**********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["domReady"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/hooks":
/*!*******************************!*\
  !*** external ["wp","hooks"] ***!
  \*******************************/
/***/ ((module) => {

"use strict";
module.exports = window["wp"]["hooks"];

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

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!******************************!*\
  !*** ./src/js/core-quote.js ***!
  \******************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/blocks */ "@wordpress/blocks");
/* harmony import */ var _wordpress_blocks__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/dom-ready */ "@wordpress/dom-ready");
/* harmony import */ var _wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/hooks */ "@wordpress/hooks");
/* harmony import */ var _wordpress_hooks__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/compose */ "@wordpress/compose");
/* harmony import */ var _wordpress_compose__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/block-editor */ "@wordpress/block-editor");
/* harmony import */ var _wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_5___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_6___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_6__);
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! react/jsx-runtime */ "react/jsx-runtime");
/* harmony import */ var react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7___default = /*#__PURE__*/__webpack_require__.n(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__);








// Add custom attribute to quote block

(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)('blocks.registerBlockType', 'my-plugin/quote-image-attribute', (settings, name) => {
  if (name !== 'core/quote') {
    return settings;
  }
  return {
    ...settings,
    attributes: {
      ...settings.attributes,
      quotes: {
        type: 'array',
        default: [{
          id: Date.now(),
          content: '',
          citation: '',
          image: null
        }]
      }
    }
  };
});

// Simple carousel initialization
const initCarousel = (container, initialSlide = 0, onSlideChange = null) => {
  if (!container) return;
  const slides = container.querySelectorAll('.quote-slide');
  const prevButton = container.querySelector('.carousel-prev');
  const dots = container.querySelector('.carousel-dots');
  const nextButton = container.querySelector('.carousel-next');
  if (!slides.length || slides.length <= 1) {
    if (prevButton) prevButton.style.display = 'none';
    if (nextButton) nextButton.style.display = 'none';
    if (dots) dots.style.display = 'none';
    return;
  }
  let currentSlide = Math.min(initialSlide, slides.length - 1);
  const updateSlides = () => {
    slides.forEach((slide, index) => {
      if (slide) {
        slide.style.display = index === currentSlide ? 'block' : 'none';
      }
    });
    if (dots) {
      const dotButtons = dots.querySelectorAll('button');
      dotButtons.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
    }
    if (onSlideChange) {
      onSlideChange(currentSlide);
    }
  };

  // Clear and create new dots
  if (dots) {
    dots.innerHTML = '';
    slides.forEach((_, index) => {
      const dot = document.createElement('button');
      dot.setAttribute('aria-label', `Go to slide ${index + 1}`);
      dot.addEventListener('click', () => {
        currentSlide = index;
        updateSlides();
      });
      dots.appendChild(dot);
    });
    dots.style.display = 'flex';
  }

  // Add click handlers directly without cloning
  if (prevButton) {
    prevButton.style.display = 'block';
    prevButton.onclick = () => {
      currentSlide = (currentSlide - 1 + slides.length) % slides.length;
      updateSlides();
    };
  }
  if (nextButton) {
    nextButton.style.display = 'block';
    nextButton.onclick = () => {
      currentSlide = (currentSlide + 1) % slides.length;
      updateSlides();
    };
  }
  updateSlides();
};
const withImageControl = (0,_wordpress_compose__WEBPACK_IMPORTED_MODULE_3__.createHigherOrderComponent)(BlockEdit => {
  return props => {
    if (props.name !== 'core/quote') {
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(BlockEdit, {
        ...props
      });
    }
    const {
      attributes,
      setAttributes
    } = props;
    const carouselRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useRef)(null);
    const currentSlideRef = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useRef)(0);
    const [selectedQuoteIndex, setSelectedQuoteIndex] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useState)(0);
    const handleSlideChange = newIndex => {
      setSelectedQuoteIndex(newIndex);
      currentSlideRef.current = newIndex;
    };
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useEffect)(() => {
      if (carouselRef.current) {
        initCarousel(carouselRef.current, currentSlideRef.current, handleSlideChange);
      }
    }, [attributes.quotes]);
    (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_6__.useEffect)(() => {
      if (carouselRef.current) {
        currentSlideRef.current = selectedQuoteIndex;
        initCarousel(carouselRef.current, selectedQuoteIndex, handleSlideChange);
      }
    }, [selectedQuoteIndex]);
    const addNewQuote = () => {
      const quotes = [...(attributes.quotes || [])];
      quotes.push({
        id: Date.now(),
        content: '',
        citation: '',
        image: null
      });
      // Set the current slide to the new quote
      currentSlideRef.current = quotes.length - 1;
      setSelectedQuoteIndex(quotes.length - 1);
      setAttributes({
        quotes
      });
    };
    const updateQuote = (index, field, value) => {
      const quotes = [...attributes.quotes];
      quotes[index] = {
        ...quotes[index],
        [field]: value
      };
      setAttributes({
        quotes
      });
    };
    const removeQuote = index => {
      const quotes = [...attributes.quotes];
      quotes.splice(index, 1);
      currentSlideRef.current = Math.min(currentSlideRef.current, Math.max(0, quotes.length - 1));
      setSelectedQuoteIndex(Math.min(selectedQuoteIndex, Math.max(0, quotes.length - 1)));
      setAttributes({
        quotes
      });
    };
    const moveQuote = (index, direction) => {
      const quotes = [...attributes.quotes];
      const newIndex = index + direction;
      if (newIndex >= 0 && newIndex < quotes.length) {
        [quotes[index], quotes[newIndex]] = [quotes[newIndex], quotes[index]];
        setSelectedQuoteIndex(newIndex);
        setAttributes({
          quotes
        });
      }
    };
    const renderQuotes = () => {
      if (!attributes.quotes?.length) return null;
      if (attributes.quotes.length === 1) {
        return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          className: "wp-block-quote-item",
          children: renderQuoteContent(attributes.quotes[0], 0)
        });
      }
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
        className: "quote-carousel",
        ref: carouselRef,
        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          className: "carousel-container",
          children: attributes.quotes.map((quote, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
            className: "quote-slide",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
              className: "wp-block-quote-item",
              children: renderQuoteContent(quote, index)
            })
          }, quote.id))
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          className: "carousel-controls",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("button", {
            className: "carousel-prev",
            "aria-label": "Previous slide",
            children: "\u276E"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
            className: "carousel-dots"
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("button", {
            className: "carousel-next",
            "aria-label": "Next slide",
            children: "\u276F"
          })]
        })]
      });
    };
    const renderQuoteContent = (quote, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
      className: "quote-wrapper",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
        className: "quote-content",
        children: [quote.image && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("figure", {
          className: "quote-image",
          children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("img", {
            src: quote.image.url,
            alt: quote.image.alt || ''
          })
        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          className: "quote-text",
          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
            tagName: "blockquote",
            value: quote.content,
            onChange: content => updateQuote(index, 'content', content),
            placeholder: "Enter quote text..."
          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText, {
            tagName: "cite",
            value: quote.citation,
            onChange: citation => updateQuote(index, 'citation', citation),
            placeholder: "Enter citation..."
          })]
        })]
      })
    });
    const renderQuoteControls = () => {
      if (!attributes.quotes?.length) return null;
      const quote = attributes.quotes[selectedQuoteIndex];
      return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
          className: "quote-list",
          children: [attributes.quotes.map((quote, index) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
            className: `quote-list-item ${index === selectedQuoteIndex ? 'is-selected' : ''}`,
            onClick: () => {
              setSelectedQuoteIndex(index);
              if (carouselRef.current) {
                currentSlideRef.current = index;
                initCarousel(carouselRef.current, index, handleSlideChange);
              }
            },
            children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
              className: "quote-list-item__content",
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
                className: "quote-list-item__text",
                children: quote.content ? quote.content.replace(/<[^>]*>/g, '').substring(0, 50) + '...' : 'Empty quote'
              })
            }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
              className: "quote-list-item__actions",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                icon: "arrow-up-alt2",
                onClick: e => {
                  e.stopPropagation();
                  moveQuote(index, -1);
                },
                isSmall: true,
                disabled: index === 0,
                className: "quote-list-item__move",
                title: "Move quote up"
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                icon: "arrow-down-alt2",
                onClick: e => {
                  e.stopPropagation();
                  moveQuote(index, 1);
                },
                isSmall: true,
                disabled: index === attributes.quotes.length - 1,
                className: "quote-list-item__move",
                title: "Move quote down"
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                icon: "trash",
                onClick: e => {
                  e.stopPropagation();
                  removeQuote(index);
                },
                isSmall: true,
                isDestructive: true,
                disabled: attributes.quotes.length <= 1,
                className: "quote-list-item__remove",
                title: attributes.quotes.length <= 1 ? "Cannot remove the last quote" : "Remove this quote"
              })]
            })]
          }, quote.id)), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("button", {
            type: "button",
            className: "quote-list-item quote-list-item-add",
            onClick: addNewQuote,
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
              className: "quote-list-item__content",
              children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
                className: "quote-list-item__add-icon",
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("svg", {
                  width: "24",
                  height: "24",
                  xmlns: "http://www.w3.org/2000/svg",
                  viewBox: "0 0 24 24",
                  "aria-hidden": "true",
                  focusable: "false",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("path", {
                    d: "M18 11.2h-5.2V6h-1.6v5.2H6v1.6h5.2V18h1.6v-5.2H18z"
                  })
                })
              }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("span", {
                className: "quote-list-item__add-label",
                children: "Add New Quote"
              })]
            })
          })]
        })
      });
    };
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.InspectorControls, {
        children: attributes.quotes?.length > 0 && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
          children: [renderQuoteControls(), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.BaseControl, {
            label: `Quote Image`,
            help: "Add an image to accompany this quote",
            children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
              className: "quote-image-controls",
              children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.MediaUploadCheck, {
                children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
                  className: "editor-post-featured-image",
                  children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.MediaUpload, {
                    onSelect: media => updateQuote(selectedQuoteIndex, 'image', media),
                    allowedTypes: ['image'],
                    value: attributes.quotes[selectedQuoteIndex].image?.id,
                    render: ({
                      open
                    }) => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
                      children: [!attributes.quotes[selectedQuoteIndex].image && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                        onClick: open,
                        variant: "secondary",
                        isSecondary: true,
                        className: "editor-post-featured-image__toggle",
                        style: {
                          width: '100%'
                        },
                        children: "Add Image"
                      }), attributes.quotes[selectedQuoteIndex].image && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)(react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.Fragment, {
                        children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("img", {
                          src: attributes.quotes[selectedQuoteIndex].image.url,
                          alt: attributes.quotes[selectedQuoteIndex].image.alt || '',
                          className: "editor-post-featured-image__preview"
                        }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
                          className: "editor-post-featured-image__actions",
                          children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                            onClick: open,
                            variant: "secondary",
                            isSecondary: true,
                            className: "editor-post-featured-image__action",
                            children: "Replace"
                          }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_components__WEBPACK_IMPORTED_MODULE_5__.Button, {
                            onClick: () => updateQuote(selectedQuoteIndex, 'image', null),
                            isDestructive: true,
                            className: "editor-post-featured-image__action",
                            children: "Remove"
                          })]
                        })]
                      })]
                    })
                  })
                })
              })
            })
          })]
        })
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
        className: "wp-block-quotes-container",
        children: renderQuotes()
      })]
    });
  };
}, 'withImageControl');

// Modify the frontend save element
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)('blocks.getSaveElement', 'my-plugin/quote-with-image', (element, block, attributes) => {
  if (block.name !== 'core/quote' || !attributes.quotes?.length) {
    return element;
  }

  // Filter out quotes with empty content
  const validQuotes = attributes.quotes.filter(quote => quote.content && quote.content.trim() !== '');

  // If no valid quotes remain, return default element
  if (validQuotes.length === 0) {
    return element;
  }
  const renderQuote = quote => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
    className: "quote-content",
    children: [quote.image && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("figure", {
      className: "quote-image",
      children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("img", {
        src: quote.image.url,
        alt: quote.image.alt || ''
      })
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
      className: "quote-text",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("blockquote", {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText.Content, {
          value: quote.content
        })
      }), quote.citation && /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("cite", {
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)(_wordpress_block_editor__WEBPACK_IMPORTED_MODULE_4__.RichText.Content, {
          value: quote.citation
        })
      })]
    })]
  });
  if (validQuotes.length === 1) {
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
      className: "wp-block-quote-item",
      children: renderQuote(validQuotes[0])
    });
  }
  return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
    className: "quote-carousel",
    children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
      className: "carousel-container",
      children: validQuotes.map(quote => /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
        className: "quote-slide",
        children: /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
          className: "wp-block-quote-item",
          children: renderQuote(quote)
        })
      }, quote.id))
    }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsxs)("div", {
      className: "carousel-controls",
      children: [/*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("button", {
        className: "carousel-prev",
        "aria-label": "Previous slide",
        children: "\u276E"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("div", {
        className: "carousel-dots"
      }), /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_7__.jsx)("button", {
        className: "carousel-next",
        "aria-label": "Next slide",
        children: "\u276F"
      })]
    })]
  });
});
(0,_wordpress_hooks__WEBPACK_IMPORTED_MODULE_2__.addFilter)('editor.BlockEdit', 'my-plugin/quote-with-image', withImageControl);
_wordpress_dom_ready__WEBPACK_IMPORTED_MODULE_1___default()(() => {
  // Unregister default styles
  (0,_wordpress_blocks__WEBPACK_IMPORTED_MODULE_0__.unregisterBlockStyle)('core/quote', ['default', 'plain']);
});
})();

/******/ })()
;
//# sourceMappingURL=editor.js.map