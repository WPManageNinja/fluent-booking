<script>
    import { util, i18 } from '../util.js';

    export let appData, timezone, slot, selectedDateTime;

</script>


<div class="fcal_form_booking_details">
    <div class="fcal_slot_info">
        <div class="slot_time_range fcal_icon_item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M6 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M12 1.5V3.75" stroke="#445164" stroke-width="1.25" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.625 6.8175H15.375" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M15.75 6.375V12.75C15.75 15 14.625 16.5 12 16.5H6C3.375 16.5 2.25 15 2.25 12.75V6.375C2.25 4.125 3.375 2.625 6 2.625H12C14.625 2.625 15.75 4.125 15.75 6.375Z" stroke="#445164" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.771 10.275H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.771 12.525H11.7778" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.99661 10.275H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.99661 12.525H9.00335" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.22073 10.275H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.22073 12.525H6.22747" stroke="#445164" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>


            <span>
                {#if slot.time_format == '24' }
                    {util.dateTimeI18(selectedDateTime.start, 'HH:mm')}
                    - {util.dateTimeI18(selectedDateTime.end, 'HH:mm')},
               {:else}
                    {util.dateTimeI18(selectedDateTime.start, 'hh:mma')}
                    - {util.dateTimeI18(selectedDateTime.end, 'hh:mma')},
               {/if}
                {util.dateTimeI18(selectedDateTime.start, 'dddd, MMM DD, YYYY')}
            </span>
        </div>
        <div class="slot_time_range slot_timezone fcal_icon_item">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5.99995 2.25H6.74995C5.28745 6.63 5.28745 11.37 6.74995 15.75H5.99995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path><path d="M11.25 2.25C12.7125 6.63 12.7125 11.37 11.25 15.75" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.25 12V11.25C6.63 12.7125 11.37 12.7125 15.75 11.25V12" stroke="#445164" stroke-linecap="round" stroke-linejoin="round"></path><path d="M2.25 6.74995C6.63 5.28745 11.37 5.28745 15.75 6.74995" stroke="#445164" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
            <span>{timezone}</span>
        </div>
    </div>
</div>