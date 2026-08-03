import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * CTA Band is a dynamic block with inner blocks. The band, eyebrow and heading
 * are rendered by render.php (in git) from attributes (in the database); the
 * body — paragraph(s) and buttons — is authored as inner blocks. `save` persists
 * only those inner blocks (wrapped in .cta-band__body); render.php places that
 * content inside the constrained centered column.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'cta-band__body',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
