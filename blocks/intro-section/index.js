import { registerBlockType } from '@wordpress/blocks';
import { InnerBlocks } from '@wordpress/block-editor';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Intro Section is a dynamic block: the eyebrow, heading and subtitle live in
 * block attributes (rendered by render.php), while an optional body of
 * paragraphs is authored as inner blocks. `save` persists only those inner
 * blocks; render.php places them below the subtitle and omits the wrapper
 * entirely when there are none — so attribute-only instances are unchanged.
 *
 * The deprecation (save returning null) lets self-closing, body-less instances
 * keep validating cleanly against the inner-blocks save.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => <InnerBlocks.Content />,
	deprecated: [ { save: () => null } ],
} );
