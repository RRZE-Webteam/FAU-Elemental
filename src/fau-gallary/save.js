import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save({ attributes }) {
	const { galleryItems } = attributes;

	return (
		<div {...useBlockProps.save()} className="fau-gallery">
			{galleryItems?.map((item, index) => (
				<div key={index} className="gallery-item">
					{item.imageUrl && (
						<div className="gallery-item-wrapper" style={{ position: 'relative' }}>
							<img
								src={item.imageUrl}
								alt={item.caption || `Gallery Image ${index + 1}`}
								className="gallery-image"
							/>
							<button
								className="fullscreen-button"
								onClick={(e) => {
									e.preventDefault();
									const img = new Image();
									img.src = item.imageUrl;
									const fullscreenWindow = window.open('', '_blank');
									fullscreenWindow.document.write(
										`<img src="${img.src}" style="width: 100%; height: auto;" />`
									);
									fullscreenWindow.document.title = 'Fullscreen Image';
								}}
								aria-label="Open Fullscreen"
								style={{
									position: 'absolute',
									top: '8px',
									right: '8px',
									background: 'rgba(0, 0, 0, 0.7)',
									color: '#fff',
									border: 'none',
									borderRadius: '50%',
									width: '32px',
									height: '32px',
									display: 'flex',
									alignItems: 'center',
									justifyContent: 'center',
									cursor: 'pointer',
								}}
							>
								🔍
							</button>
						</div>
					)}
					{item.caption && <RichText.Content tagName="p" className="gallery-caption" value={item.caption} />}
					{item.copyright && <p className="gallery-copyright">{item.copyright}</p>}
				</div>
			))}
		</div>
	);
}
