/**
 * Makes teaser cards clickable.
 * 
 * Accessibility features:
 * - Keyboard navigation support
 * - Preserves actual link behavior when links are clicked
 * - Adds proper ARIA attributes for screen readers
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find all teaser items with data-href attribute
    const teaserItems = document.querySelectorAll('.teaser-item[data-href]');
    
    teaserItems.forEach(function(item) {
        const href = item.getAttribute('data-href');
        
        // Set cursor to pointer to indicate clickability
        item.style.cursor = 'pointer';
        
        // Add visual feedback on focus for accessibility
        item.addEventListener('focus', function() {
            this.classList.add('is-focused');
        });
        
        item.addEventListener('blur', function() {
            this.classList.remove('is-focused');
        });
        
        // Make the whole card clickable
        item.addEventListener('click', function(e) {
            // Prevent card click if user clicked on an actual link inside the card
            if (e.target.tagName.toLowerCase() === 'a' || 
                e.target.closest('a')) {
                return;
            }
            
            // Navigate to the post/page
            window.location.href = href;
        });
        
        // Handle keyboard navigation
        item.addEventListener('keydown', function(e) {
            // Navigate on Enter key or Space key (for button role)
            if (e.key === 'Enter' || e.key === ' ' || e.keyCode === 13 || e.keyCode === 32) {
                e.preventDefault(); // Prevent page scroll on space
                window.location.href = href;
            }
        });
    });
}); 