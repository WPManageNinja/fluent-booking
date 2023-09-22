<template>
    <div v-if="settings.schedule_conditions" class="fcal_inline_items">
        <el-input placeholder="eg: 4" type="number" v-model="settings.schedule_conditions.value" />
        <el-select v-model="settings.schedule_conditions.unit" placeholder="Select Unit">
            <el-option value="minutes" label="Minutes"></el-option>
            <el-option value="hours" label="Hours"></el-option>
            <el-option value="days" label="Days"></el-option>
        </el-select>
        <span class="help-tx">of an event start time</span>
    </div>
</template>

<script type="text/babel">
export default {
    name: '__SchedulingConditions',
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
