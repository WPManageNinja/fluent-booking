<template>
    <div class="fcal_time_zone_selector">
        <el-select id="fcal_timezone_selector" filterable @change="timeZoneChanged" v-model="selected" placeholder="Select Timezone">
            <el-option-group
                v-for="(group, groupLabel) in appVars.timezones"
                :key="groupLabel"
                :label="groupLabel"
            >
                <el-option
                    v-for="item in group"
                    :key="item"
                    :label="item"
                    :value="item"
                />
            </el-option-group>
        </el-select>
        <span v-if="currentDateTime">Current DateTime: <code>{{currentDateTime}}</code></span>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'TimeZoneSelector',
    props: ['modelValue', 'disabled'],
    emits: ['update:modelValue'],
    data() {
        return {
            selected: this.modelValue,
            currentDateTime: ''
        }
    },
    methods: {
        timeZoneChanged() {
            this.$emit('update:modelValue', this.selected);
            this.setCurrentTime();
        },
        setCurrentTime() {
            if(this.selected) {
                this.currentDateTime = this.dayjs().tz(this.selected).format('YYYY-MM-DD HH:mm');
            } else {
                this.currentDateTime = '';
            }
        }
    },
    mounted() {
        if(!this.value) {
            this.selected = this.dayjs.tz.guess();
            this.timeZoneChanged();
        } else {
            this.setCurrentTime();
        }
    }
}
</script>
