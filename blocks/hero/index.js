import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Hero is a dynamic block with inner blocks — a sibling of Spotlight. The
 * two-column shell, column image, background/overlay, eyebrow and heading are
 * rendered by render.php (in git) from attributes (in the database), while the
 * body — paragraphs and buttons — is authored as inner blocks. `save` persists
 * only those inner blocks (wrapped in .hero__body); render.php places that
 * content inside the text column, above the background overlay.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'hero__body',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
