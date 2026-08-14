import { registerBlockType } from '@wordpress/blocks';

import metadata from './block.json';
import Edit from './edit.js';

/**
 * Plan Selector is a fully dynamic block — the cards are rendered by render.php
 * from the `cards`/`selected` attributes (in the database), and the front-end
 * behaviour lives in view.js. There are no inner blocks, so `save` returns null.
 */
registerBlockType( metadata.name, {
	edit: Edit,
	save: () => null,
} );
