<?php
/**
 * FAU Global Search Block Template
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block default content.
 * @param WP_Block $block      Block instance.
 */

// Get block attributes
$width = isset($attributes['width']) ? esc_attr($attributes['width']) : 'content-size';
$heading = isset($attributes['heading']) ? esc_html($attributes['heading']) : '';
// Automatically enable search scope and advanced features for content-size
$show_search_scope = ($width === 'content-size');
$enable_advanced_features = ($width === 'content-size');

// Get block wrapper attributes with additional classes
$wrapper_attributes = get_block_wrapper_attributes([
    'class' => 'fau-global-search-wrapper fau-global-search-wrapper--' . $width
]);

// Generate unique ID for form elements
$form_id = 'fau-global-search-' . wp_unique_id();
?>

<div <?php echo $wrapper_attributes; ?>>
	<?php if (!empty($heading) && $width !== 'full-grid'): ?>
		<h3 class="fau-global-search__heading"><?php echo $heading; ?></h3>
	<?php endif; ?>
	<form 
		class="fau-global-search fau-global-search__form" 
		method="get" 
		action="<?php echo home_url('/'); ?>"
		id="<?php echo $form_id; ?>"
		<?php if ($width === 'content-size'): ?>data-advanced-features="true" data-enable-autocomplete="true"<?php endif; ?>
	>
		<div class="fau-global-search__input-wrapper<?php if ($width === 'full-grid') echo ' fau-global-search__input-wrapper--inline'; ?>">
<?php if (!empty($heading) && $width === 'full-grid'): ?>
			<h3 class="fau-global-search__heading fau-global-search__heading--inline"><?php echo $heading; ?></h3>
<?php endif; ?>
			<input
				type="search"
				class="fau-global-search__input"
				name="s"
				placeholder="<?php echo __('Search…', 'fau-elemental'); ?>"
				value="<?php echo esc_attr(get_search_query()); ?>"
				autocomplete="off"
				id="<?php echo $form_id; ?>-input"
			/>
			<button
				type="submit"
				class="fau-global-search__button"
			>
				<span class="fau-global-search__button-text">
					<?php echo __('Search', 'fau-elemental'); ?>
				</span>
				<span class="fau-global-search__button-icon" aria-hidden="true"></span>
			</button>
		</div>
				
				<?php 
				//FAU-wide Search for Global and Inline Search is supposed to be some kind of google search which might get implemented in the future;
				// Keeping code commented out for future implementation rather than deletion
				/*
				if ($width === 'content-size'): ?>
					<div class="fau-global-search__scope">
						<label class="fau-global-search__scope-option">
							<input 
								type="radio" 
								name="fau_search_scope" 
								value="fau-wide" 
								checked="checked"
							/>
							<span><?php echo __('FAU-wide Search', 'fau-elemental'); ?></span>
						</label>
						<label class="fau-global-search__scope-option">
							<input 
								type="radio" 
								name="fau_search_scope" 
								value="current-site"
								checked="checked"
							/>
							<span><?php echo __('Current Site Only', 'fau-elemental'); ?></span>
						</label>
					</div>
				<?php endif; 
				*/
				?>
	</form>
</div> 
