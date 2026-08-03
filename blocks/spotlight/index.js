import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Spotlight is a dynamic block with inner blocks: the two-column shell, image,
 * eyebrow and heading are rendered by render.php (in git) from attributes (in
 * the database), while the body — paragraphs and buttons — is authored as inner
 * blocks. `save` persists only those inner blocks (wrapped in .spotlight__body);
 * render.php places that content inside the text column.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'spotlight__body',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
