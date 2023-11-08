/*eslint-disable*/
const {Fragment, useEffect, useState} = wp.element;
const { RichText } = wp.blockEditor;
const {__} = wp.i18n;
const {
    Spinner,
    DropdownMenu
} = wp.components;

import './fcal-team-management-block.scss';

const assetsUrl = window.fluent_booking_block.assets_url;

export const LandingPage = props => {
    let {
        attributes: {
            title,
            description,
            headerImage,
            calendars,
            hosts
        }, setAttributes,
    } = props;

    const [isLoading, setIsLoading] = useState(false);
    // const [slot, setSlot] = useState('');
    const [error, setError] = useState(false);


    const apiFetch = wp.apiFetch;
    const {addQueryArgs} = wp.url;

    useEffect(() => {
        getCalendars();
    }, [ ] );
    const getCalendars = (queryArgs) => {
        setIsLoading(true);
        apiFetch({
            path: addQueryArgs('fluent-booking/v2/calendars', {
                ...queryArgs
            })
        })
            .then((response) => {
                setAttributes( { calendars: response.calendars.data } );
            })
            .catch(error => {
                setError(error);
            })
            .finally(() => {
                setIsLoading(false);
            });
    };

    if (hosts) {
        let hostId = [];

        for (let key in hosts) {
            hostId.push(parseInt(key));
        }
        calendars = calendars.filter(calendar => hostId.includes(calendar.id));
    }

    return [
        <Fragment>
            <div className="fcal_team_management_block_wrap">
                <div className="fcal_team_management_block_header">
                    {
                        headerImage.url != '' ?
                        <img src={headerImage.url} alt={headerImage.title} />
                        :
                        <img src={assetsUrl+'/images/logo.svg'} alt="Logo" />
                    }
                    <RichText
                        className={title?'':'empty-text'}
                        tagName="h1"
                        value={ title }
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
                        onChange={ ( heading ) => setAttributes( { title: heading } ) }
                        placeholder="Enter title here..."
                    />
                    <RichText
                        className={description?'':'empty-text'}
                        tagName="p"
                        value={ description }
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link' ] }
                        onChange={ ( heading ) => setAttributes( { description: heading } ) }
                        placeholder="Enter description here..."
                    />
                </div>

                {
                    calendars && calendars.length ?
                        <div className="fcal_team_management_block_hosts">
                            {calendars.map(calendar => {
                                return <div className="fcal_team_management_block_host">
                                    <img src={calendar?.author_profile?.avatar} alt=""/>
                                    <h3>{calendar?.author_profile?.name}</h3>
                                    {
                                        calendar.description != '' ?
                                            <p>{calendar.description}</p>
                                        :
                                        ''
                                    }
                                </div>
                            })}
                        </div>
                        :
                        <p className='empty-text'>{__('No Calendar Found!')}</p>
                    }

            </div>
        </Fragment>
    ]
}
