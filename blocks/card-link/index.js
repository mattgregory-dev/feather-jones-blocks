import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Card Link is a dynamic block. `save` persists only the decorative inner blocks
 * (wrapped in .sb-card-link__body); render.php (in git) wraps that content in the
 * card anchor from the url/padding/moreText attributes (in the database).
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'sb-card-link__body',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
