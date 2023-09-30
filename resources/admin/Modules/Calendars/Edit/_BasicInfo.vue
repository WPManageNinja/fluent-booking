<template>
    <div class="fcal_create_calendar_form">
        <div class="fcal_create_calendar_form_header">
            <h2> <EventIcon/> Event Details </h2>
            <el-switch v-model="isEnable" />
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
                                <span class="fcal_color" :style="'background:'+ calendarColor "></span>
                                <el-select
                                    v-model="calendarColor"
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
                    <div v-if="slot.duration == 'custom'" class="custom-duration">
                        <el-input
                            v-model="slot.customDuration"
                        />
                        <el-select v-model="slot.durationType" popper-class="fcal_select" placeholder="Select">
                            <el-option
                                v-for="item in durationTypes"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                            />
                        </el-select>
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
            isEnable: true,
            calendarColor: '#4587EC',
            colors: [
                {
                    value: '#4587EC',
                    label: ''
                },
                {
                    value: '#2653C7',
                    label: ''
                },
                {
                    value: '#e4b606',
                    label: ''
                },
                {
                    value: 'green',
                    label: ''
                },
                {
                    value: '#d3d305',
                    label: ''
                }
            ],
            meetingDuration: [
                {
                    value: 15,
                    label: '15 Minutes'
                },
                {
                    value: 30,
                    label: '30 Minutes'
                },
                {
                    value: 45,
                    label: '45 Minutes'
                },
                {
                    value: 60,
                    label: '60 Minutes'
                },
                {
                    value: 'custom',
                    label: 'Custom'
                }
            ],
            durationTypes: [
                {
                    value: 'minutes',
                    label: 'Minutes'
                },
                {
                    value: 'hours',
                    label: 'Hours'
                }
            ],
            formData: {
                title: '',
                duration: 30,
                customDuration: '',
                durationType: 'minutes',
                description: '',
                location_type: 'phone',
                location_heading: '',
                location_settings: {},
                event_type: this.event_type,
            }
        }
    },
    methods: {
        toggleDisplaySpots() {

        }
    },
}
</script>
