<?php
/**
 * Navigation Components
 *
 * @package FAU-Elemental
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include the unified menu modal system first
require_once __DIR__ . '/menu-modal-config.php';

// Navigation template parts are loaded via get_template_part() calls
// No initialization needed as they are pure template files 