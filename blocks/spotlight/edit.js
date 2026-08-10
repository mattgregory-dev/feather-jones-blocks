import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	useInnerBlocksProps,
	InspectorControls,
	BlockControls,
	MediaPlaceholder,
	MediaReplaceFlow,
} from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';

/**
 * Editor UI. Mirrors render.php's markup/classes so the theme stylesheet
 * (loaded into the canvas via add_editor_style) styles the preview identically.
 *
 * The title is always a typed attribute; `level` only chooses the heading tag
 * (H1 for a page hero, H2 for a mid-page feature). Unlike intro-section, there
 * is no page-title binding — spotlight headlines never match the page title.
 */

const ALLOWED_BLOCKS = [ 'core/paragraph', 'core/heading', 'core/list', 'core/quote', 'core/buttons', 'core/separator', 'core/group' ];
const TEMPLATE = [ [ 'core/paragraph', { placeholder: __( 'Add spotlight text…', 'fj-blocks' ) } ] ];

export default function Edit( { attributes, setAttributes } ) {
	const { imageId, imageAlt, imagePosition, verticalAlignment, eyebrow, level, title } = attributes;
	const isH1 = 'h1' === level;
	const TitleTag = isH1 ? 'h1' : 'h2';

	const blockProps = useBlockProps( {
		className: `is-position-${ imagePosition } is-valign-${ verticalAlignment }` + ( imageId ? '' : ' has-no-media' ),
	} );

	const innerBlocksProps = useInnerBlocksProps(
		{ className: 'spotlight__body' },
		{ allowedBlocks: ALLOWED_BLOCKS, template: TEMPLATE, templateLock: false }
	);

	// Media object for the selected image (source URL for the canvas preview).
	const image = useSelect(
		( select ) => ( imageId ? select( 'core' ).getMedia( imageId ) : null ),
		[ imageId ]
	);

	const onSelectImage = ( media ) =>
		setAttributes( { imageId: media.id, imageAlt: media.alt || '' } );

	const media = (
		<figure className="spotlight__media">
			{ imageId ? (
				<img src={ image?.source_url } alt={ imageAlt } />
			) : (
				<MediaPlaceholder
					icon="format-image"
					labels={ { title: __( 'Spotlight image', 'fj-blocks' ) } }
					accept="image/*"
					allowedTypes={ [ 'image' ] }
					onSelect={ onSelectImage }
				/>
			) }
		</figure>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'fj-blocks' ) }>
					{ /* Framed around the always-present content column, not the optional
					     image. Stored as `imagePosition` (the image side), so the control
					     works in the inverse: content-left ⇄ image-right. */ }
					<SelectControl
						label={ __( 'Content position', 'fj-blocks' ) }
						help={ __( 'Which side the text column sits on. An image, if set, takes the other side.', 'fj-blocks' ) }
						value={ 'left' === imagePosition ? 'right' : 'left' }
						options={ [
							{ label: __( 'Left', 'fj-blocks' ), value: 'left' },
							{ label: __( 'Right', 'fj-blocks' ), value: 'right' },
						] }
						onChange={ ( value ) =>
							setAttributes( { imagePosition: 'left' === value ? 'right' : 'left' } )
						}
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Vertical alignment', 'fj-blocks' ) }
						help={ __( 'Applies above 1160px; narrower screens stack and top-align.', 'fj-blocks' ) }
						value={ verticalAlignment }
						options={ [
							{ label: __( 'Center', 'fj-blocks' ), value: 'center' },
							{ label: __( 'Top', 'fj-blocks' ), value: 'top' },
						] }
						onChange={ ( value ) => setAttributes( { verticalAlignment: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={ __( 'Content', 'fj-blocks' ) }>
					<TextControl
						label={ __( 'Eyebrow (optional)', 'fj-blocks' ) }
						value={ eyebrow }
						onChange={ ( value ) => setAttributes( { eyebrow: value } ) }
						__nextHasNoMarginBottom
					/>
					<TextControl
						label={ __( 'Title', 'fj-blocks' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						__nextHasNoMarginBottom
					/>
					<SelectControl
						label={ __( 'Title heading level', 'fj-blocks' ) }
						help={ __( 'Sets the heading tag only: H1 for a page hero, H2 for a mid-page feature.', 'fj-blocks' ) }
						value={ level }
						options={ [
							{ label: __( 'H2 — in-page feature', 'fj-blocks' ), value: 'h2' },
							{ label: __( 'H1 — page hero', 'fj-blocks' ), value: 'h1' },
						] }
						onChange={ ( value ) => setAttributes( { level: value } ) }
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				{ imageId && (
					<PanelBody title={ __( 'Image', 'fj-blocks' ) }>
						<TextControl
							label={ __( 'Alt text', 'fj-blocks' ) }
							help={ __( 'Describe the image for screen readers.', 'fj-blocks' ) }
							value={ imageAlt }
							onChange={ ( value ) => setAttributes( { imageAlt: value } ) }
							__nextHasNoMarginBottom
						/>
						<Button
							variant="link"
							isDestructive
							onClick={ () => setAttributes( { imageId: undefined, imageAlt: '' } ) }
						>
							{ __( 'Remove image', 'fj-blocks' ) }
						</Button>
					</PanelBody>
				) }
			</InspectorControls>

			{ imageId && (
				<BlockControls>
					<MediaReplaceFlow
						mediaId={ imageId }
						mediaURL={ image?.source_url }
						allowedTypes={ [ 'image' ] }
						accept="image/*"
						onSelect={ onSelectImage }
					/>
				</BlockControls>
			) }

			<section { ...blockProps }>
				<div className="spotlight__inner">
					<div className="spotlight__text">
						{ eyebrow && <p className="spotlight__eyebrow">{ eyebrow }</p> }
						{ title && (
							<TitleTag className="spotlight__title">{ title }</TitleTag>
						) }
						<div { ...innerBlocksProps } />
					</div>
					{ media }
				</div>
			</section>
		</>
	);
}
