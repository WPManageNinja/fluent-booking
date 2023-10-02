<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <EventIcon/> Event Details </h2>
            <el-switch class="fcal_switch" v-model="isEnable" @change="toggleSlotStatus"/>
        </div>
        <div class="fcal_create_calendar_form_body">
            <el-form label-position="top">
                <el-form-item label="Event Name *" class="fcal_color_select_wrap">
                    <el-input
                        v-model="slot.title"
                        placeholder="Enter Event Title"
                    >
                        <template #prepend>
                            <div class="fcal_color_select">
                                <span class="fcal_color" :style="'background:'+ slot.color_schema "></span>
                                <el-select
                                    v-model="slot.color_schema"
                                    placeholder="Select"
                                    style="width: 77px"
                                    popper-class="fcal_color_select_popover"
                                >
                                    <el-option
                                        v-for="(color, i) in colors" :key="i"
                                        :value="color.value">
                                        <span :style="'background:'+color.value"></span>
                                    </el-option>
                                </el-select>
                            </div>
                        </template>
                    </el-input>
                </el-form-item>

                <el-form-item label="Meeting Duration *">
                    <el-select v-model="slot.duration" placeholder="Select" popper-class="fcal_select">
                        <el-option
                            v-for="item in meetingDuration"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        />
                    </el-select>
                    <div v-if="slot.duration === 'custom'" class="custom-duration">
                        <el-input
                            v-model="slot.custom_duration"
                            @change="validateDuration(slot)"
                            type="number"
                            :min="10">
                            <template #append>Minutes</template>
                        </el-input>
                    </div>
                </el-form-item>

                <el-form-item label="Description">
                    <el-input
                        v-model="slot.description"
                        type="textarea"
                        placeholder="Enter Description here"
                    />
                </el-form-item>

                <el-form-item label="Location *">
                    <location-selector :slot="slot"/>
                </el-form-item>

                <template v-if="isGroupMeeting">
                    <el-form-item label="Max invitees in a spot">
                        <el-input type="number" :min="1" v-model="slot.max_book_per_slot"></el-input>
                    </el-form-item>
                    <el-checkbox
                        v-model="isDisplaySpots"
                        @change="toggleDisplaySpots"
                        class="fcal_checkbox"
                        type="checkbox"
                        label="Display remaining spots on booking page">
                    </el-checkbox>
                </template>
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import LocationSelector from "./_LocationSelector.vue";
import EventIcon from "../../../Components/Icons/EventIcon";

export default {
    name: 'EventBasicInfo',
    props: ['slot', 'event_type'],
    components: {
        LocationSelector,
        EventIcon
    },
    data() {
        return {
            isEnable: this.slot.status === 'active' ? true : false,
            isDisplaySpots: this.slot.is_display_spots == 1 ? true : false,
            isGroupMeeting: this.slot.event_type == 'group',
            colors: this.appVars.event_colors,
            meetingDuration: this.appVars.meeting_durations
        }
    },
    methods: {
        toggleDisplaySpots() {
            this.slot.is_display_spots = this.isDisplaySpots ? 1 : 0;
        },
        toggleSlotStatus() {
            this.slot.status = this.isEnable ? 'active' : 'draft';
        },
        validateDuration(slot) {
            this.slot.custom_duration = Math.max(10, Math.min(300, slot.custom_duration));
        },
        checkDurationType() {
            const fromDurationValue = this.appVars.meeting_durations.some(duration => duration.value === this.slot.duration);
            if (!fromDurationValue) {
                this.slot.custom_duration = this.slot.duration;
                this.slot.duration = 'custom';
            }
        }
    },
    mounted() {
        this.checkDurationType();
        this.slot.event_type = this.event_type ? this.event_type : this.slot.event_type;
    }
}
</script>
