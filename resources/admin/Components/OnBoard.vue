<template>
    <div>
        <create-new 
            :host_id="appVars.me.id"
            :event_type="'single'"
            :is_board="true"
        />
    </div>
</template>

<script>
import CreateNew from "../Modules/Calendars/CreateNew.vue";
export default {
    name: 'CreateNewCalendar',
    components: {
        CreateNew
    }
}
</script>
