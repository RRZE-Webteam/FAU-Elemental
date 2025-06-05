document.addEventListener('DOMContentLoaded', function() {
    const shareButton = document.querySelector('.share-toggle');
    const shareDropdown = document.querySelector('.share-dropdown');
    const shareLinks = document.querySelectorAll('.share-link');

    if (shareButton && shareDropdown) {
        shareButton.addEventListener('click', function(e) {
            e.preventDefault();
            shareDropdown.classList.toggle('is-active');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!shareButton.contains(e.target) && !shareDropdown.contains(e.target)) {
                shareDropdown.classList.remove('is-active');
            }
        });

        // Handle share links
        shareLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const shareType = this.dataset.share;
                const url = window.location.href;
                const title = document.title;
                let shareUrl;

                switch(shareType) {
                    case 'bluesky':
                        shareUrl = `https://bsky.app/intent/post?text=${encodeURIComponent(title + ' - ' + url)}`;
                        break;
                    case 'signal':
                        shareUrl = `https://signal.me/#p/${encodeURIComponent(url)}`;
                        break;
                    case 'linkedin':
                        shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
                        break;
                    case 'facebook':
                        shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
                        break;
                    case 'whatsapp':
                        shareUrl = `https://wa.me/?text=${encodeURIComponent(title + ' - ' + url)}`;
                        break;
                    case 'email':
                        shareUrl = `mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`;
                        break;
                }

                if (shareUrl) {
                    window.open(shareUrl, '_blank');
                }
                
                // Close dropdown after sharing
                shareDropdown.classList.remove('is-active');
            });
        });
    }
}); 