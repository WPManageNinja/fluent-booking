<script>
    import { util, i18 } from '../util.js';
    import { createEventDispatcher } from 'svelte';

    export let appData, timezone, selectedDate;

    const id = appData.id;
    let start_time;

    $: if (selectedDate) {
        start_time = selectedDate.start;
    }

    let dispatch = createEventDispatcher();

    function resetSelection() {
        selectedDate = '';
        start_time = undefined;

        const el = jQuery('#' + id);
        el.removeClass('ff-el-is-error')
          .find(':first-child')
          .removeClass('border-danger');
        el.nextAll('.text-danger').remove();
        
        dispatch('resetSelection');
    }
</script>

{#if selectedDate}
    <div class="fcal_form_booking_details">
        <div class="fcal_author">
            <div aria-label="Back to Date Selection" on:click={(e) => { resetSelection() }} on:keypress={(e) => { resetSelection() }} class="fcal_back">
                <button class="fcal_svg ff_svg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                        <path fill="none" d="M0 0h24v24H0V0z"/>
                        <path d="M19 11H7.83l4.88-4.88c.39-.39.39-1.03 0-1.42-.39-.39-1.02-.39-1.41 0l-6.59 6.59c-.39.39-.39 1.02 0 1.41l6.59 6.59c.39.39 1.02.39 1.41 0 .39-.39.39-1.02 0-1.41L7.83 13H19c.55 0 1-.45 1-1s-.45-1-1-1z"/>
                    </svg>
                </button>
            </div>
            <div class="fcal_author_name">
                {appData.author_profile.name}
            </div>
        </div>
        <div class="fcal_slot_info">
            <h2 class="fcal_slot_heading">{appData.slot.title}</h2>
            <div class="slot_timing fcal_icon_item">
                <svg fill="#000000" width="1rem" height="1rem" viewBox="0 0 24 24"
                     xmlns="http://www.w3.org/2000/svg" stroke="#000000">
                    <g stroke-width="0"/>
                    <g stroke-linecap="round" stroke-linejoin="round"/>
                    <g>
                        <path d="M12,2A10,10,0,1,0,22,12,10,10,0,0,0,12,2Zm5,11H12a1,1,0,0,1-1-1V6a1,1,0,0,1,2,0v5h4a1,1,0,0,1,0,2Z"/>
                    </g>
                </svg>
                <span>{appData.slot.duration} {i18('Minutes')}</span>
            </div>
        </div>
        {#if appData.slot.location_settings[0]?.type == 'phone_organizer'}
            <div class="slot_location fcal_icon_item">
                <svg fill="none" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="1rem" height="1rem" data-testid="phone-call-icon" data-id="details-item-icon"><title>Phone call</title><path d="M15.415 22.655c2.356 1.51 5.218 1.174 7.238-.84l.842-.838c.673-.672.673-2.014 0-2.685l-3.012-3.006c-.673-.671-1.541-.2-2.215.472-.673.671-2.679 1.334-3.352.663l-7.35-7.144c-.674-.671-.016-2.677.658-3.348.673-.671.673-2.014 0-2.685L5.65.67C4.977 0 3.63 0 2.957.671l-.841.671C.264 3.356-.073 6.21 1.274 8.558a56.353 56.353 0 0014.14 14.097z" fill="currentColor"></path></svg>
                <span>{i18('Phone Call')}</span>
            </div>
        {:else if appData.slot.location_settings[0]?.type == 'in_person_organizer'}
            <div class="slot_location fcal_icon_item">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path></svg>
                <span>{appData.slot.location_settings[0]?.title}</span>
            </div>
            <div class="fcal_slot_description">
                <p>{appData.slot.location_settings[0]?.description}</p>
            </div>
        {:else}
            <div class="slot_location fcal_icon_item">
                <svg fill="none" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" width="1rem" height="1rem" data-testid="location-marker-icon" data-id="details-item-icon"><title>Physical location</title><path d="M12 0C7.453 0 3.623 3.853 3.623 8.429c0 6.502 7.18 14.931 7.42 15.172.479.482 1.197.482 1.675.24l.24-.24c.239-.24 7.419-8.67 7.419-15.172C20.377 3.853 16.547 0 12 0zm0 11.56c-1.675 0-2.872-1.445-2.872-2.89S10.566 5.78 12 5.78c1.436 0 2.872 1.445 2.872 2.89S13.675 11.56 12 11.56z" fill="currentColor"></path></svg>
                <span>{appData.slot.location_settings[0]?.title}</span>
            </div>
            <div class="fcal_slot_description">
                <p>{appData.slot.location_settings[0]?.description}</p>
            </div>
        {/if}
        <div class="slot_time_range fcal_icon_item">
            <svg height="1rem" width="1rem" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg"
                 xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 29.237 29.237" xml:space="preserve"
                 fill="#000000">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                    <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="#CCCCCC" stroke-width="0.05847399999999999"></g>
                    <g id="SVGRepo_iconCarrier"> <g> <g>
                    <path style="fill:#010002;"
                        d="M7.685,24.819H8.28v-2.131h3.688v2.131h0.596v-2.131h3.862v2.131h0.597v-2.131h4.109v2.131h0.595 v-2.131h3.417v-0.594h-3.417v-3.861h3.417v-0.596h-3.417v-3.519h3.417v-0.594h-3.417v-2.377h-0.595v2.377h-4.109v-2.377h-0.597 v2.377h-3.862v-2.377h-0.596v2.377H8.279v-2.377H7.685v2.377H3.747v0.594h3.938v3.519H3.747v0.596h3.938v3.861H3.747v0.594h3.938 V24.819z M12.563,22.094v-3.861h3.862v3.861H12.563z M21.132,22.094h-4.109v-3.861h4.109V22.094z M21.132,14.118v3.519h-4.109 v-3.519C17.023,14.119,21.132,14.119,21.132,14.118z M16.426,14.118v3.519h-3.862v-3.519 C12.564,14.119,16.426,14.119,16.426,14.118z M8.279,14.118h3.688v3.519H8.279V14.118z M8.279,18.233h3.688v3.861H8.279V18.233z">
                    </path>
                    <path style="fill:#010002;"
                        d="M29.207,2.504l-4.129,0.004L24.475,2.51v2.448c0,0.653-0.534,1.187-1.188,1.187h-1.388 c-0.656,0-1.188-0.533-1.188-1.187V2.514l-1.583,0.002v2.442c0,0.653-0.535,1.187-1.191,1.187h-1.388 c-0.655,0-1.188-0.533-1.188-1.187V2.517l-1.682,0.004v2.438c0,0.653-0.534,1.187-1.189,1.187h-1.389 c-0.653,0-1.188-0.533-1.188-1.187V2.525H8.181v2.434c0,0.653-0.533,1.187-1.188,1.187H5.605c-0.656,0-1.189-0.533-1.189-1.187 V2.53L0,2.534v26.153h2.09h25.06l2.087-0.006L29.207,2.504z M27.15,26.606H2.09V9.897h25.06V26.606z">
                    </path>
                    <path style="fill:#010002;"
                        d="M5.605,5.303h1.388c0.163,0,0.296-0.133,0.296-0.297v-4.16c0-0.165-0.133-0.297-0.296-0.297H5.605 c-0.165,0-0.298,0.132-0.298,0.297v4.16C5.307,5.17,5.44,5.303,5.605,5.303z">
                    </path>
                    <path style="fill:#010002;"
                        d="M11.101,5.303h1.389c0.164,0,0.297-0.133,0.297-0.297v-4.16c-0.001-0.165-0.134-0.297-0.298-0.297 H11.1c-0.163,0-0.296,0.132-0.296,0.297v4.16C10.805,5.17,10.938,5.303,11.101,5.303z">
                    </path>
                    <path style="fill:#010002;"
                        d="M16.549,5.303h1.388c0.166,0,0.299-0.133,0.299-0.297v-4.16c-0.001-0.165-0.133-0.297-0.299-0.297 h-1.388c-0.164,0-0.297,0.132-0.297,0.297v4.16C16.252,5.17,16.385,5.303,16.549,5.303z">
                    </path>
                    <path style="fill:#010002;"
                        d="M21.899,5.303h1.388c0.164,0,0.296-0.133,0.296-0.297v-4.16c0-0.165-0.132-0.297-0.296-0.297 h-1.388c-0.164,0-0.297,0.132-0.297,0.297v4.16C21.603,5.17,21.735,5.303,21.899,5.303z">
                    </path>
                </g> </g> </g>
            </svg>
            <span>
                {util.toDate(selectedDate.start, 'hh:mma')} - {util.toDate(selectedDate.end, 'hh:mma')},
                {util.toDate(selectedDate.start, 'dddd, MMMM DD, YYYY')}
            </span>
        </div>
        <div class="slot_time_range fcal_icon_item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16">
                <path fill="#444"
                      d="M8 0C3.6 0 0 3.6 0 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm5.2 5.3c.4 0 .7.3 1.1.3-.3.4-1.6.4-2-.1.3-.1.5-.2.9-.2zM1 8c0-.4 0-.8.1-1.3.1 0 .2.1.3.1 0 0 .1.1.1.2 0 .3.3.5.5.5.8.1 1.1.8 1.8 1 .2.1.1.3 0 .5-.6.8-.1 1.4.4 1.9.5.4.5.8.6 1.4 0 .7.1 1.5.4 2.2C2.7 13.3 1 10.9 1 8zm7 7c-.7 0-1.5-.1-2.1-.3-.1-.2-.1-.4 0-.6.4-.8.8-1.5 1.3-2.2.2-.2.4-.4.4-.7 0-.2.1-.5.2-.7.3-.5.2-.8-.2-.9-.8-.2-1.2-.9-1.8-1.2s-1.2-.5-1.7-.2c-.2.1-.5.2-.5-.1 0-.4-.5-.7-.4-1.1-.1 0-.2 0-.3.1s-.2.2-.4.1c-.2-.2-.1-.4-.1-.6.1-.2.2-.3.4-.4.4-.1.8-.1 1 .4.3-.9.9-1.4 1.5-1.8 0 0 .8-.7.9-.7s.2.2.4.3c.2 0 .3 0 .3-.2.1-.5-.2-1.1-.6-1.2 0-.1.1-.1.1-.1.3-.1.7-.3.6-.6 0-.4-.4-.6-.8-.6-.2 0-.4 0-.6.1-.4.2-.9.4-1.5.4C5.2 1.4 6.6 1 8 1h.8c-.6.1-1.2.3-1.6.5.6.1.7.4.5.9-.1.2 0 .4.2.5s.4.1.5-.1c.2-.3.6-.4.9-.5.4-.1.7-.3 1-.7 0-.1.1-.1.2-.2.6.2 1.2.6 1.8 1-.1 0-.1.1-.2.1-.2.2-.5.3-.2.7.1.2 0 .3-.1.4-.2.1-.3 0-.4-.1s-.1-.3-.4-.3c-.1.2-.4.3-.4.6.5 0 .4.4.5.7-.6.1-.8.4-.5.9.1.2-.1.3-.2.4-.4.6-.8 1-.8 1.7s.5 1.4 1.3 1.3c.9-.1.9-.1 1.2.7 0 .1.1.2.1.3.1.2.2.4.1.6-.3.8.1 1.4.4 2 .1.2.2.3.3.4-1.3 1.4-3 2.2-5 2.2z">
                </path>
            </svg>
            <span>
                {timezone}
            </span>
        </div>
    </div>
{/if}
<div>
    <input type="hidden" name={appData.name} value={JSON.stringify({ id, timezone, start_time })} />
</div>
