<template>
    <div class="fcal_week_day_config">
        <div v-if="day_label" class="fcal_day_status">
            <span style="text-transform: uppercase;">{{day_label}}</span>
        </div>
        <div class="fcal_day_slots">
            <div class="fcal_slot" v-for="(slot, index) in slots" :key="index">
                <el-time-select v-model="slot.start"
                                :start="overrideSelectTime.start"
                                :step="overrideSelectTime.step"
                                :end="overrideSelectTime.end"
                                :max-time="slot.end"
                                :placeholder="$t('Start')"
                                popper-class="fcal_select"
                                :disabled="isUnavailable"
                />
                <span class="fcal_sep"></span>
                <el-time-select v-model="slot.end"
                                :start="overrideSelectTime.start"
                                :step="overrideSelectTime.step"
                                :min-time="slot.start"
                                :end="overrideSelectTime.end"
                                :placeholder="$t('End')"
                                popper-class="fcal_select"
                                :disabled="isUnavailable"
                />

                <el-button
                    v-if="slots.length > 1"
                    text
                    :icon="DeleteIcon"
                    class="fcal_slot_delete"
                    @click="removeSlot(index)" />
            </div>
        </div>
        <div class="fcal_add_slot">
            <el-button :disabled="isUnavailable" text :icon="PlusIcon" @click="addSlot" />
        </div>
    </div>
</template>

<script>
import { Delete, Plus } from '@element-plus/icons-vue'
import { markRaw } from "vue";
export default {
    name: 'DayOverRideConfig',
    props: ['day_label', 'slots', 'isUnavailable'],
    data() {
        return {
            overrideSelectTime: this.appVars.override_select_times,
            DeleteIcon: markRaw(Delete),
            PlusIcon: markRaw(Plus)
        }
    },
    watch: {
        isUnavailable() {
            this.updateAvailability();
        }
    },
    methods: {
        removeSlot(index) {
            this.slots.splice(index, 1);
        },
        addSlot() {
            this.slots.push({
                start: '',
                end: ''
            });
        },
        updateAvailability() {
            this.slots.splice(0, this.slots.length);
            if (this.isUnavailable) {
                this.slots.push({
                    start: "00:00",
                    end: "00:00"
                });
            } else {
                this.slots.push({
                    start: '',
                    end: ''
                });
            }
        }
    },
    mounted() {
        if(!this.slots.length) {
            this.addSlot();
        }
    }
}
</script>
