import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl } from '@wordpress/components';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically.
 *
 * A CTA is never the page's top heading, so the level select offers H2/H3 only
 * (no H1). Title is always a typed attribute. No color or style controls — the
 * one band treatment is owned by the stylesheet.
 */

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/buttons' ];
const TEMPLATE = [
	[ 'core/paragraph', { align: 'center', placeholder: __( 'Call-to-action text…', 'fj-blocks' ) } ],
	[ 'core/buttons', { layout: { type: 'flex', justifyContent: 'center' } }, [ [ 'core/button', {} ] ] ],
];

export default function Edit( { attributes, setAttributes } ) {
	const { eyebrow, title, level } = attributes;
	const TitleTag = 'h3' === level ? 'h3' : 'h2';

	// `sb-band` mirrors render.php so the flush-to-footer rule (a trailing band
	// cancels main's bottom padding) also applies in the Site Editor, where the
	// footer renders in the canvas.
	const blockProps = useBlockProps( { className: 'sb-band' } );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'cta-band__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: false }
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content', 'fj-blocks' ) }>
					<TextControl
						label={ __( 'Eyebrow (optional)', 'fj-blocks' ) }
						value={ eyebrow }
						onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Heading', 'fj-blocks' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Heading level', 'fj-blocks' ) }
						help={ __( 'A CTA is never the page’s top heading, so H1 is not offered.', 'fj-blocks' ) }
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
				<div className="cta-band__inner">
					{ eyebrow && <p className="cta-band__eyebrow">{ eyebrow }</p> }
					{ title && <TitleTag className="cta-band__title">{ title }</TitleTag> }
					<div { ...innerBlocksProps } />
				</div>
			</section>
		</>
	);
}
