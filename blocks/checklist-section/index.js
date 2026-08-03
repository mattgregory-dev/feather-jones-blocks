import { registerBlockType } from '@wordpress/blocks';
import { useInnerBlocksProps } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Checklist Section is a dynamic block with inner blocks. The band, eyebrow and
 * heading are rendered by render.php from attributes; the two checklists are
 * authored as inner blocks (locked to exactly two core/list blocks). `save`
 * persists those lists inside the .checklist-section__lists grid wrapper;
 * render.php places that grid below the header.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => {
		const innerBlocksProps = useInnerBlocksProps.save( {
			className: 'checklist-section__lists',
		} );
		return <div { ...innerBlocksProps } />;
	},
} );
