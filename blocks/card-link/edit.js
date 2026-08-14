import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	BlockControls,
	AlignmentControl,
} from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically.
 *
 * The whole card is a link, so on the FRONT END render.php wraps the content in
 * an <a>. In the editor the wrapper is a <div> instead — inner blocks must stay
 * editable, and a live anchor would hijack clicks. Inner content is fully
 * author-composed and decorative; core/buttons is excluded so nothing nests a
 * second anchor inside the card link.
 */

const ALLOWED_BLOCKS = [
	'core/group',
	'core/columns',
	'core/paragraph',
	'core/heading',
	'core/image',
	'core/list',
	'core/separator',
	'core/spacer',
];

const PAD_OPTIONS = [
	{ label: __( 'Roomy (80)', 'fj-blocks' ), value: '80' },
	{ label: __( 'Medium (70)', 'fj-blocks' ), value: '70' },
	{ label: __( 'Snug (60)', 'fj-blocks' ), value: '60' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { url, opensInNewTab, padding, contentAlign } = attributes;

	// Alignment flexes the whole card (align-items) so the image centers with the
	// text — not just inline text-align. Left is the default (no modifier).
	const alignClass =
		contentAlign && 'left' !== contentAlign ? `sb-card-link--align-${ contentAlign }` : '';

	const blockProps = useBlockProps( {
		className: `sb-card-link sb-card ${ alignClass }`.trim(),
		style: { '--sb-card-link-pad': `var(--wp--preset--spacing--${ padding })` },
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'sb-card-link__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, templateLock: false }
	);

	return (
		<>
			<BlockControls>
				<AlignmentControl
					value={ contentAlign }
					onChange={ ( value ) => setAttributes( { contentAlign: value ?? '' } ) }
				/>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Link', 'fj-blocks' ) }>
					<TextControl
						label={ __( 'URL', 'fj-blocks' ) }
						help={ __( 'The whole card links here. The card is hidden on the front end until a URL is set.', 'fj-blocks' ) }
						value={ url }
						onChange={ ( value ) => setAttributes( { url: value } ) }
						__nextHasNoMarginBottom
					/>
					<ToggleControl
						label={ __( 'Open in new tab', 'fj-blocks' ) }
						checked={ opensInNewTab }
						onChange={ ( value ) => setAttributes( { opensInNewTab: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={ __( 'Card', 'fj-blocks' ) }>
					<SelectControl
						label={ __( 'Padding', 'fj-blocks' ) }
						value={ padding }
						options={ PAD_OPTIONS }
						onChange={ ( value ) => setAttributes( { padding: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<div { ...innerBlocksProps } />
			</div>
		</>
	);
}
