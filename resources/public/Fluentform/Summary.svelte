<script>
    import { util, i18 } from '../util.js';

    export let timezone, slot, dateFormatter, selectedDateTime;
</script>


<div class="fcal_form_booking_details">
    <table>
        <tbody>
            <tr>
                <th>{i18('Date')}</th>
                <td>{util.dateTimeI18(selectedDateTime.start, dateFormatter)}</td>
            </tr>
            <tr>
                <th>{i18('Time')}</th>
                <td>
                    {#if slot.time_format == '24' }
                        {util.dateTimeI18(selectedDateTime.start, 'HH:mm')}
                        - {util.dateTimeI18(selectedDateTime.end, 'HH:mm')}
                    {:else}
                        {util.dateTimeI18(selectedDateTime.start, 'hh:mma')}
                        - {util.dateTimeI18(selectedDateTime.end, 'hh:mma')}
                    {/if}
                </td>
            </tr>
            <tr>
                <th>{i18('Timezone')}</th>
                <td>{timezone}</td>
            </tr>
        </tbody>
    </table>
</div>