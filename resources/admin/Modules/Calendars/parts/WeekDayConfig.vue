<template>
    <div class="fcal_week_day_config">
        <div class="fcal_day_status">
            <el-checkbox
                @change="statusUpdated"
                v-model="config.enabled"
            />
            <span class="fcal_day">{{ $t(week_day) }}</span>
        </div>
        <div class="fcal_day_slots">
            <div v-if="config.slots.length && config.enabled" class="fcal_slot" v-for="(slot, index) in config.slots" :key="index">
                <el-time-select v-model="slot.start"
                                :start="weekSelectTime.start"
                                :step="weekSelectTime.step"
                                :end="weekSelectTime.end"
                                :max-time="slot.end"
                                :placeholder="$t('Start')"
                                popper-class="fcal_select"
                />
                <span class="fcal_sep"></span>
                <el-time-select v-model="slot.end"
                                :start="weekSelectTime.start"
                                :step="weekSelectTime.step"
                                :min-time="slot.start"
                                :end="weekSelectTime.end"
                                :placeholder="$t('End')"
                                popper-class="fcal_select"
                />

                <el-button
                    class="fcal_slot_delete"
                    v-if="config.slots.length > 1"
                    text
                    :icon="DeleteIcon"
                    @click="removeSlot(index)"
                />
            </div>
            <span v-else class="fcal_unavailable_day">{{ $t('Unavailable') }}</span>
        </div>
        <div v-if="config.enabled" class="fcal_add_slot">
            <el-button text :icon="PlusIcon" @click="addSlot" />
        </div>
    </div>
</template>

<script>
import { Delete, Plus } from '@element-plus/icons-vue'
import { markRaw } from "vue";
export default {
    name: 'WeekDayConfig',
    props: ['week_day', 'config'],
    data() {
        return {
            weekSelectTime: this.appVars.week_select_times,
            DeleteIcon: markRaw(Delete),
            PlusIcon: markRaw(Plus)
        }
    },
    methods: {
        removeSlot(index) {
            this.config.slots.splice(index, 1);
        },
        addSlot() {
            this.config.slots.push({
                start: '',
                end: ''
            });
        },
        statusUpdated() {
            if (this.config.enabled && !this.config.slots.length) {
                this.addSlot();
            }
        }
    }
}
</script>
