/*eslint-disable*/
const {Fragment, useState} = wp.element;
const { RichText } = wp.blockEditor;
const {__} = wp.i18n;

import './fcal-team-management-block.scss';

const assetsUrl = window.fluent_booking_block.assets_url;

const calendarsVar = window.fluent_booking_block.hosts;

export const LandingPage = props => {
    let {
        attributes: {
            title,
            description,
            headerImage,
            calendarHosts,
        }, setAttributes,
    } = props;

    const [isLoading, setIsLoading] = useState(false);
    // const [slot, setSlot] = useState('');
    const [error, setError] = useState(false);

    const apiFetch = wp.apiFetch;
    const {addQueryArgs} = wp.url;

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
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/text-color' ] }
                        onChange={ ( heading ) => setAttributes( { title: heading } ) }
                        placeholder={__('Enter title here...')}
                    />
                    <RichText
                        className={description?'':'empty-text'}
                        tagName="p"
                        value={ description }
                        allowedFormats={ [ 'core/bold', 'core/italic', 'core/link', 'core/text-color' ] }
                        onChange={ ( heading ) => setAttributes( { description: heading } ) }
                        placeholder={__('Enter description here...')}
                    />
                </div>

                { calendarHosts && calendarHosts.length ?
                        <div className="fcal_team_management_block_hosts">
                            {calendarHosts.map((calConfig, index) => {
                                return <div key={index} className="fcal_team_management_block_host">
                                    <img src={calendarsVar[calConfig.id]?.author?.avatar} alt=""/>
                                    <h3>{calendarsVar[calConfig.id]?.author?.name}</h3>
                                    {
                                        calendarsVar[calConfig.id]?.description != '' ?
                                            <div dangerouslySetInnerHTML={{ __html: calendarsVar[calConfig.id]?.description }} />
                                        :
                                        ''
                                    }
                                </div>
                            })}
                        </div>
                        :
                    <div className="fcal_team_management_block_hosts">
                        <p>{__('Please select team member from block settings')}</p>
                    </div>
                }

            </div>
        </Fragment>
    ]
}
