import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button } from '@wordpress/components';

/**
 * Editor UI. The canvas mirrors render.php's card markup so the theme stylesheet
 * (loaded via add_editor_style) previews it identically — except the editor has
 * no real radio inputs, so the selected card is flagged with `is-selected` (the
 * stylesheet targets both that and :has(input:checked)). Clicking a card sets it
 * as the default selection. Plan content is edited as a repeater in the sidebar.
 */

const FIELDS = [
	{ key: 'kicker', label: __( 'Kicker', 'fj-blocks' ) },
	{ key: 'price', label: __( 'Price', 'fj-blocks' ) },
	{ key: 'per', label: __( 'Cadence line', 'fj-blocks' ) },
	{ key: 'total', label: __( 'Total line', 'fj-blocks' ) },
	{ key: 'pill', label: __( 'Pill', 'fj-blocks' ) },
	{ key: 'ctaLabel', label: __( 'Button label when selected', 'fj-blocks' ) },
	{ key: 'ctaUrl', label: __( 'Button URL when selected', 'fj-blocks' ) },
];

const EMPTY_CARD = { kicker: '', price: '', per: '', total: '', pill: '', ctaLabel: '', ctaUrl: '' };

export default function Edit( { attributes, setAttributes } ) {
	const { cards, selected } = attributes;

	const updateCard = ( index, key, value ) => {
		setAttributes( {
			cards: cards.map( ( card, i ) => ( i === index ? { ...card, [ key ]: value } : card ) ),
		} );
	};

	const addCard = () => setAttributes( { cards: [ ...cards, { ...EMPTY_CARD } ] } );

	const removeCard = ( index ) => {
		const next = cards.filter( ( _, i ) => i !== index );
		setAttributes( {
			cards: next,
			selected: Math.max( 0, Math.min( selected, next.length - 1 ) ),
		} );
	};

	const blockProps = useBlockProps( { className: 'plan-selector', role: 'radiogroup' } );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Plans', 'fj-blocks' ) }>
					{ cards.map( ( card, i ) => (
						<div
							key={ i }
							style={ {
								marginBottom: '1.25rem',
								paddingBottom: '1rem',
								borderBottom: '1px solid #e0e0e0',
							} }
						>
							<p style={ { fontWeight: 700, margin: '0 0 0.5rem' } }>
								{ __( 'Plan', 'fj-blocks' ) } { i + 1 }
								{ i === selected ? ` — ${ __( 'default', 'fj-blocks' ) }` : '' }
							</p>
							{ FIELDS.map( ( field ) => (
								<TextControl
									key={ field.key }
									label={ field.label }
									value={ card[ field.key ] || '' }
									onChange={ ( value ) => updateCard( i, field.key, value ) }
									__nextHasNoMarginBottom
								/>
							) ) }
							<div style={ { display: 'flex', gap: '0.5rem', marginTop: '0.5rem' } }>
								<Button
									variant="secondary"
									size="small"
									disabled={ i === selected }
									onClick={ () => setAttributes( { selected: i } ) }
								>
									{ __( 'Make default', 'fj-blocks' ) }
								</Button>
								<Button
									variant="link"
									isDestructive
									disabled={ cards.length <= 1 }
									onClick={ () => removeCard( i ) }
								>
									{ __( 'Remove', 'fj-blocks' ) }
								</Button>
							</div>
						</div>
					) ) }
					<Button variant="primary" onClick={ addCard }>
						{ __( 'Add plan', 'fj-blocks' ) }
					</Button>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ cards.map( ( card, i ) => (
					// eslint-disable-next-line jsx-a11y/no-noninteractive-element-interactions
					<label
						key={ i }
						className={ `plan-card${ i === selected ? ' is-selected' : '' }` }
						onClick={ () => setAttributes( { selected: i } ) }
					>
						<span className="plan-card__dot" aria-hidden="true"></span>
						{ card.kicker && <span className="plan-card__kicker">{ card.kicker }</span> }
						{ card.price && <span className="plan-card__price">{ card.price }</span> }
						{ card.per && <span className="plan-card__per">{ card.per }</span> }
						{ card.total && <span className="plan-card__total">{ card.total }</span> }
						{ card.pill && <span className="plan-card__pill">{ card.pill }</span> }
					</label>
				) ) }
			</div>
		</>
	);
}
