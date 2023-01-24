<div class="fcal_booking_form_wrap">
    <div class="fcal_booking_form">
        <div class="fcal_form_item">
            <label class="fcal_input_content">
                <div class="fcal_input_label">Your Name <span>*</span></div>
                <input class="fcal_input" type="text" placeholder="Full Name" bind:value={form.name} />
            </label>
        </div>
        <div class="fcal_form_item">
            <label class="fcal_input_content">
                <div class="fcal_input_label">Your Email Address <span>*</span></div>
                <input disabled="{form.user_id}" class="fcal_input" type="email" placeholder="Email Address" bind:value={form.email} />
            </label>
        </div>
        <div class="fcal_form_item">
            <label class="fcal_input_content">
                <div class="fcal_input_label">
                    Please share anything that will help prepare for our meeting.
                </div>
                <textarea class="fcal_input fcal_textarea" bind:value={form.message} />
            </label>
        </div>
        <div class="fcal_form_item fcal_submit">
            <button disabled="{submitting}" on:click={submitForm} class="fcal_btn_submit { submitting ? 'fcal_btn_submitting' : '' }">Schedule Meeting</button>
        </div>
    </div>

</div>

<script>
    import {util} from '../util.js';
    import {createEventDispatcher} from 'svelte';

    export let timezone;
    export let spot;
    export let slot;

    const form = window.fluentCalendarPublicVars.current_person;

    let submitting = false;

    let dispatch = createEventDispatcher();

    function submitForm() {

        const postdata = {
            ...form,
            timezone,
            start_date: spot.start,
            slot_id: slot.id
        }

        if(submitting) return;

        submitting = true;

        util.$post(`slots/${slot.id}/schedule`, postdata)
            .then(res => {
                dispatch('bookingConfirmed', res);
            })
            .catch(err => {
                console.log(err);
            })
            .finally(() => {
                submitting = false;
            });
    }
</script>
