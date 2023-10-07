<template>
    <div class="fcal_remote_calendar_block">
        <div class="fcal_remote_header">
            <div class="fcal_driver_brand">
                <img :src="driver.icon" />
                <div class="fcal_driver_heading">
                    <h3>{{driver.title}}</h3>
                    <p>{{feed.identifier}}</p>
                </div>
            </div>
            <div class="fcal_driver_action">
                <el-button>Delete</el-button>
            </div>
        </div>
        <pre>{{feed}}</pre>
    </div>
</template>
<script type="text/babel">
export default {
    name: 'RemoteCalendarBlock',
    props: ['calendar', 'driver', 'feed']
}
</script>
