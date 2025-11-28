<template>
    <div v-if="settings.schedule_conditions" class="fcal_minimum_notice">
        <el-input :placeholder="$t('eg: 4')" type="number" v-model="settings.schedule_conditions.value" />
        <el-select
            v-model="settings.schedule_conditions.unit"
            :placeholder="$t('Select Unit')"
            popper-class="fcal_select"
        >
            <el-option value="minutes" :label="$t('Minutes')"></el-option>
            <el-option value="hours" :label="$t('Hours')"></el-option>
            <el-option value="days" :label="$t('Days')"></el-option>
        </el-select>
    </div>
</template>

<script>
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
