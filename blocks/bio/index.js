import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Bio is a dynamic block with inner blocks — a person-scoped sibling of
 * Spotlight. The two-column shell, portrait, role and name are rendered by
 * render.php (in git) from attributes (in the database); the biography — and
 * optional social links — is authored as inner blocks. `save` persists only
 * those inner blocks (wrapped in .bio__body); render.php places that content
 * inside the text column.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'bio__body',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
