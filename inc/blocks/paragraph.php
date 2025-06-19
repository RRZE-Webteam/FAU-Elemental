<?php

add_filter( 'render_block_core/paragraph', 'fau_elemental_render_block_core_paragraph', 10, 2 );

function fau_elemental_render_block_core_paragraph( $block_content, $block ) {
    // Check if isSpan attribute exists and is true
    if ( isset( $block['attrs']['isSpan'] ) && $block['attrs']['isSpan'] === true ) {
        // Replace the p tags with span tags
        $block_content = str_replace( '<p', '<span', $block_content );
        $block_content = str_replace( '</p>', '</span>', $block_content );
    }
    
    return $block_content;
}