<template>
    <div v-if="settings.schedule_conditions" style="display: flex;" class="fcal_inline_items">
        <div>
            <el-input placeholder="eg: 4" type="number" v-model="settings.schedule_conditions.value" />
        </div>
        <div>
            <el-select v-model="settings.schedule_conditions.unit" placeholder="Select Unit">
                <el-option value="minutes" label="Minutes"></el-option>
                <el-option value="hours" label="Hours"></el-option>
                <el-option value="days" label="Days"></el-option>
            </el-select> of an event start time
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'SchedulingConditions',
    props: ['settings'],
    mounted() {
        if(!this.settings.schedule_conditions) {
            this.settings.schedule_conditions = {
                value: 4,
                unit: 'hours'
            };
        }
    }
}
</script>
