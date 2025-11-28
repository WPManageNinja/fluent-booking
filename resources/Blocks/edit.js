import InspectorSettings from './inspector-settings';

import { LandingPage } from './LandingPage.jsx';

const Edit = props => {
    return (
        <>
            <div className="fluent-booking-block">
                <LandingPage
                    attributes={props.attributes}
                    setAttributes={props.setAttributes}
                />
            </div>

            <InspectorSettings
                attributes={props.attributes}
                setAttributes={props.setAttributes}
            />
        </>
    );
};

export default Edit;
