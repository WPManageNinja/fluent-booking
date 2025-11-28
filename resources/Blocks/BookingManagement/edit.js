const {Fragment} = wp.element;
import InspectorSettings from './inspector-settings';

import { LandingPage } from './LandingPage.jsx';

const Edit = props => {
    return (
        <Fragment>
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
        </Fragment>
    );
};

export default Edit;
