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
/*!*******************************!*\
  !*** ./src/js/core-button.js ***!
  \*******************************/
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
const {
  BlockControls
} = wp.blockEditor;
wp.domReady(() => {
  wp.blocks.unregisterBlockStyle('core/button', ['fill', 'outline']);
  wp.blocks.unregisterBlockVariation('core/button', 'width');
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
addFilter('editor.BlockEdit', 'fau-elemental/with-button-selected-class', createHigherOrderComponent(BlockEdit => {
  return props => {
    const {
      isSelected,
      name
    } = props;
    useEffect(() => {
      if (isSelected) {
        const isButtonBlock = name === 'core/button';
        document.body.classList.toggle('faue-is-button-block-selected', isButtonBlock);
      }
    }, [isSelected]);
    return /*#__PURE__*/(0,react_jsx_runtime__WEBPACK_IMPORTED_MODULE_0__.jsx)(BlockEdit, {
      ...props
    });
  };
}, 'withButtonSelectedClass'));
})();

// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*****************************!*\
  !*** ./src/js/core-text.js ***!
  \*****************************/
const {
  registerBlockVariation,
  unregisterBlockVariation
} = wp.blocks;
wp.domReady(() => {
  // Unregister Quote as a transform option for Paragraph
  unregisterBlockVariation('core/paragraph', 'core/quote');

  // Register "Intro Text" variation for core/paragraph with an icon
  registerBlockVariation('core/paragraph', {
    name: 'intro-text',
    title: 'Intro Text',
    description: 'A paragraph styled as an introduction.',
    attributes: {
      className: 'intro-text'
    },
    icon: 'editor-textcolor',
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

  // Register "List with Icons" variation for core/list with an icon
  registerBlockVariation('core/list', {
    name: 'list-with-icons',
    title: 'List with Icons',
    description: 'An unordered list with icons.',
    attributes: {
      className: 'list-icons'
    },
    icon: 'editor-ul',
    // Dashicon for list
    isDefault: false,
    scope: ['block', 'inserter', 'transform']
  });
});
})();

/******/ })()
;
//# sourceMappingURL=editor.js.map