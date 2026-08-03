import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically —
 * including the is-style-checklist ✓ markers, which come from _lists.scss.
 *
 * The body is exactly two core/list blocks (one per column). `templateLock:
 * "insert"` is the guardrail: the client can edit, add, remove and reorder list
 * items freely, but cannot add or delete the list blocks themselves — so an
 * Enter-split can't spawn a third block and no stray paragraph can land between
 * the columns (allowedBlocks is core/list only). Two separate lists let the
 * client balance the columns by hand.
 */

const ALLOWED_BLOCKS = [ 'core/list' ];
const LIST_ATTRS = {
	className: 'is-style-checklist',
	fontSize: 'large',
	style: { spacing: { blockGap: 'var:preset|spacing|30' } },
};
const TEMPLATE = [
	[ 'core/list', LIST_ATTRS, [ [ 'core/list-item' ] ] ],
	[ 'core/list', LIST_ATTRS, [ [ 'core/list-item' ] ] ],
];

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, title, level } = attributes;
	const TitleTag = 'h3' === level ? 'h3' : 'h2';

	// `sb-band` mirrors render.php so the flush-to-footer rule (a trailing band
	// cancels main's bottom padding) also applies in the Site Editor, where the
	// footer renders in the canvas.
	const blockProps = useBlockProps( { className: 'sb-band' } );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'checklist-section__lists' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: 'insert' }
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content', 'starter-blocks' ) }>
					<TextControl
						label={ __( 'Eyebrow (optional)', 'starter-blocks' ) }
						value={ eyebrow }
						onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Heading', 'starter-blocks' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Heading level', 'starter-blocks' ) }
						help={ __( 'A section heading is never the page’s top heading, so H1 is not offered.', 'starter-blocks' ) }
						value={ level }
						options={ [
							{ label: 'H2', value: 'h2' },
							{ label: 'H3', value: 'h3' },
						] }
						onChange={ ( value ) => setAttributes( { level: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="checklist-section__inner">
					{ eyebrow && <p className="checklist-section__eyebrow">{ eyebrow }</p> }
					{ title && <TitleTag className="checklist-section__title">{ title }</TitleTag> }
					<div { ...innerBlocksProps } />
				</div>
			</section>
		</>
	);
}
