import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	RichText,
	InspectorControls,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically.
 *
 * The title is always a typed attribute, edited inline; `level` only chooses the
 * heading tag (H1 for a page hero, H2 for a mid-page intro). No page-title
 * binding, consistent with the other blocks.
 *
 * Below the subtitle is an optional InnerBlocks body (paragraphs) for centered-
 * header / left-body prose sections; it stays empty (and renders nothing) unless
 * paragraphs are added.
 *
 * The per-element balance toggle (text-wrap: balance) and width preset (measure
 * cap) are applied to the title / subtitle classes live in the canvas, so the
 * preview shows the true final rag. Both default to off / "default", emitting no
 * class.
 */

const ALLOWED_BLOCKS = [ 'core/paragraph' ];

const WIDTH_OPTIONS = [
	{ label: __( 'Default', 'fj-blocks' ), value: 'default' },
	{ label: __( 'Narrow', 'fj-blocks' ), value: 'narrow' },
	{ label: __( 'Narrower', 'fj-blocks' ), value: 'narrower' },
	{ label: __( 'Narrowest', 'fj-blocks' ), value: 'narrowest' },
];

// Build an element's class list from its balance + width settings. `prefix` is
// 'title' or 'subtitle'.
function elementClass( base, balance, width, prefix ) {
	let cls = base;
	if ( balance ) {
		cls += ' has-balanced-text';
	}
	if ( 'default' !== width ) {
		cls += ` has-${ prefix }-width-${ width }`;
	}
	return cls;
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		eyebrow,
		title,
		subtitle,
		level,
		titleBalance,
		subtitleBalance,
		titleWidth,
		subtitleWidth,
	} = attributes;
	// Mirror render.php: `alignfull` (the intro band is always full-bleed by
	// design, with no align support/toolbar, so break it out in the canvas too)
	// and `sb-band` (so the flush-to-footer rule applies in the Site Editor,
	// where the footer renders in the canvas).
	const blockProps = useBlockProps( { className: 'alignfull sb-band' } );
	const TitleTag = 'h1' === level ? 'h1' : 'h2';

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'intro-section__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: [], templateLock: false }
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Intro settings', 'fj-blocks' ) }>
					<SelectControl
						label={ __( 'Title heading level', 'fj-blocks' ) }
						help={ __(
							'Use H1 only when this is the page hero; H2 for a mid-page intro.',
							'fj-blocks'
						) }
						value={ level }
						options={ [
							{ label: __( 'H2 — in-page intro', 'fj-blocks' ), value: 'h2' },
							{ label: __( 'H1 — page hero', 'fj-blocks' ), value: 'h1' },
						] }
						onChange={ ( value ) => setAttributes( { level: value } ) }
						__nextHasNoMarginBottom
					/>

					<ToggleControl
						label={ __( 'Balance title lines', 'fj-blocks' ) }
						checked={ !! titleBalance }
						onChange={ ( value ) => setAttributes( { titleBalance: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Title width', 'fj-blocks' ) }
						value={ titleWidth }
						options={ WIDTH_OPTIONS }
						onChange={ ( value ) => setAttributes( { titleWidth: value } ) }
						__nextHasNoMarginBottom
					/>

					<ToggleControl
						label={ __( 'Balance subtitle lines', 'fj-blocks' ) }
						checked={ !! subtitleBalance }
						onChange={ ( value ) => setAttributes( { subtitleBalance: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Subtitle width', 'fj-blocks' ) }
						value={ subtitleWidth }
						options={ WIDTH_OPTIONS }
						onChange={ ( value ) => setAttributes( { subtitleWidth: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
			</InspectorControls>

			<section { ...blockProps }>
				<div className="intro-section__inner">
					<RichText
						tagName="p"
						className="intro-section__eyebrow"
						value={ eyebrow }
						onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
						placeholder={ __( 'Eyebrow (optional)', 'fj-blocks' ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName={ TitleTag }
						className={ elementClass( 'intro-section__title', titleBalance, titleWidth, 'title' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						placeholder={ __( 'Title', 'fj-blocks' ) }
						allowedFormats={ [] }
					/>
					<RichText
						tagName="p"
						className={ elementClass( 'intro-section__subtitle', subtitleBalance, subtitleWidth, 'subtitle' ) }
						value={ subtitle }
						onChange={ ( value ) => setAttributes( { subtitle: value } ) }
						placeholder={ __( 'Subtitle (optional)', 'fj-blocks' ) }
						allowedFormats={ [ 'core/bold', 'core/italic' ] }
					/>
					<div { ...innerBlocksProps } />
				</div>
			</section>
		</>
	);
}
