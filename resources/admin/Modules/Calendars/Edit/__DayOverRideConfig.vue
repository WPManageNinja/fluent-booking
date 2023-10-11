<template>
    <div class="fcal_week_day_config">
        <div v-if="day_label" class="fcal_day_status">
            <span style="text-transform: uppercase;">{{day_label}}</span>
        </div>
        <div class="fcal_day_slots">
            <div class="fcal_slot" v-for="(slot, index) in slots" :key="index">
                <el-time-select v-model="slot.start"
                                start="00:00"
                                step="00:10"
                                end="23:50"
                                :max-time="slot.end"
                                placeholder="Start"
                                popper-class="fcal_select"
                                :disabled="unavailable_date"
                />
                <span class="fcal_sep"></span>
                <el-time-select v-model="slot.end"
                                start="00:00"
                                step="00:10"
                                :min-time="slot.start"
                                end="23:50"
                                placeholder="End"
                                popper-class="fcal_select"
                                :disabled="unavailable_date"
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
            <el-button :disabled="unavailable_date" text :icon="PlusIcon" @click="addSlot" />
        </div>
    </div>
</template>

<script type="text/babel">
import {
    Delete,
    Plus
} from '@element-plus/icons-vue'
import {markRaw} from "vue";
export default {
    name: 'DayOverRideConfig',
    props: ['day_label', 'slots', 'unavailable_date'],
    data() {
        return {
            DeleteIcon: markRaw(Delete),
            PlusIcon: markRaw(Plus)
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
        }
    },
    mounted() {
        if(!this.slots.length) {
            this.addSlot();
        }
    }
}
</script>
