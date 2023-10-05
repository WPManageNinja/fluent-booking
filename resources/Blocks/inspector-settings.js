/*eslint-disable*/
const { InspectorControls } = wp.blockEditor;
const {__} = wp.i18n;
const {
    PanelBody,
    PanelRow,
    SelectControl
} = wp.components;

const assets_url = window.fluent_booking_block.assets_url;

const InspectorSettings = props => {
    const {
        attributes: {
            slotId,
            calendarId,
            calendars
        }, setAttributes
    } = props;

    const calendarChangeHandler = (event) => {
        const ids = event.target.value.split(",");

        setAttributes( { slotId: ids[0] } );
        setAttributes( { calendarId: ids[1] } );
    }

    return (
        <InspectorControls>
            <PanelBody title="General Settings"
                       initialOpen={true}
            >
                <PanelRow>
                    <div className="fcal_block_settings">
                        <div className="fcal_block_inspector_widget">
                            <h3 className="label">Select An Slot</h3>
                            <select
                                value={[slotId, calendarId]}
                                onChange={calendarChangeHandler}
                            >
                                <option value="">---Select a Slot---</option>
                                {calendars.map((item, index) => {
                                    return <optgroup label={item.title} key={index}>
                                        {
                                            item.slots.map(slot => {
                                                return <option key={'slot-'+slot.id} value={[slot.id, item.id]}>
                                                    {slot.title}
                                                </option>
                                            })
                                        }
                                    </optgroup>
                                })}
                            </select>
                        </div>

                    </div>
                </PanelRow>
            </PanelBody>
        </InspectorControls>
    );
};
export default InspectorSettings;
