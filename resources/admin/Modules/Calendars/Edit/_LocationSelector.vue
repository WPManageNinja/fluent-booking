<template>
    <div class="fcal_location_selector_wrap">
        <el-select
            @change="locationChanged()"
            popper-class="fcal_selector_with_submenu fcal_location_select fcal_select"
            v-model="location_details.location_type"
            clearable
            placeholder="Select Location">
            <el-option-group
                v-for="(location, locationKey) in locations"
                :key="locationKey"
                :label="location.label"
                :value="locationKey"
            >
                <el-option
                    v-for="(option, optionKey) in location.options"
                    :key="optionKey"
                    :label="option.title"
                    :value="optionKey"
                >
                    {{ option.title }}
                </el-option>
            </el-option-group>
        </el-select>

        <el-form
            v-if="showInput && location_details.location_type !== 'google_meet'"
            :model="location_details"
            label-position="top"
            class="fcal_location_form"
        >
            <el-form-item v-if="hasHeading" label="Location Title">
                <el-input v-model="location_details.location_heading" type="text" placeholder="Location Title" />
            </el-form-item>
            <el-form-item v-if="hasDescription" label="Location Description">
                <el-input v-model="location_details.location_settings.description" type="textarea" placeholder="Location Description" />
            </el-form-item>

            <div v-if="location_details.location_type == 'phone' || location_details.location_type == 'phone_organizer'">
                <el-form-item>
                    <el-radio-group class="radio_desc_group" v-model="location_details.location_settings.call_type">
                        <el-radio label="outbound">
                            <b>I will call my invitee</b>
                            <p>Invitee will be asked for his/her phone number</p>
                        </el-radio>
                        <el-radio label="inbound">
                            <b>My invitee should call me</b>
                            <p>Invitee can see your phone number after booking</p>
                        </el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item v-if="location_details.location_settings.call_type == 'inbound'" label="Your Phone Number (with country code)">
                    <el-input type="text" placeholder="Your Phone Number" v-model="location_details.location_settings.host_phone_number" />
                </el-form-item>
            </div>

        </el-form>

        <el-button
            class="fcal_location_edit_btn fcal_plain_btn"
           v-if="location_details.location_type"
            @click="showInput = true;">
            <el-icon><EditPen /></el-icon>
        </el-button>

<!--        <el-dialog-->
<!--            v-model="showModal"-->
<!--            title="Edit Location"-->
<!--            :append-to-body="true"-->
<!--            class="fcal_dialog"-->
<!--        >-->
<!--            <el-form v-if="showModal" :model="location_details" label-position="top" >-->
<!--                <el-form-item label="Location">-->
<!--                    <el-select-->
<!--                        @change="locationChanged()"-->
<!--                        popper-class="fcal_selector_with_submenu fcal_select"-->
<!--                        v-model="location_details.location_type"-->
<!--                        placeholder="Select Location">-->
<!--                        <el-option-->
<!--                            v-for="(location, locationKey) in locations"-->
<!--                            :key="locationKey"-->
<!--                            :label="location.title"-->
<!--                            :value="locationKey"-->
<!--                        >-->
<!--                            <b>{{ location.title }}</b>-->
<!--                            <span style="display: block;color: var(&#45;&#45;el-text-color-secondary);padding:0; margin:0; font-size: 12px;">-->
<!--                                {{ location.subtitle }}-->
<!--                            </span>-->
<!--                        </el-option>-->
<!--                    </el-select>-->
<!--                </el-form-item>-->
<!--                <el-form-item v-if="hasHeading" label="Location Title">-->
<!--                    <el-input v-model="location_details.location_heading" type="text" placeholder="Location Title" />-->
<!--                </el-form-item>-->
<!--                <el-form-item v-if="hasDescription" label="Location Description">-->
<!--                    <el-input v-model="location_details.location_settings.description" type="textarea" placeholder="Location Description" />-->
<!--                </el-form-item>-->

<!--                <div v-if="location_details.location_type == 'phone'">-->
<!--                    <el-form-item>-->
<!--                        <el-radio-group class="radio_desc_group" v-model="location_details.location_settings.call_type">-->
<!--                            <el-radio label="outbound">-->
<!--                                <b>I will call my invitee</b>-->
<!--                                <p>Invitee will be asked for his/her phone number</p>-->
<!--                            </el-radio>-->
<!--                            <el-radio label="inbound">-->
<!--                                <b>My invitee should call me</b>-->
<!--                                <p>Invitee can see your phone number after booking</p>-->
<!--                            </el-radio>-->
<!--                        </el-radio-group>-->
<!--                    </el-form-item>-->
<!--                    <el-form-item v-if="location_details.location_settings.call_type == 'inbound'" label="Your Phone Number (with country code)">-->
<!--                        <el-input type="text" placeholder="Your Phone Number" v-model="location_details.location_settings.host_phone_number" />-->
<!--                    </el-form-item>-->
<!--                </div>-->

<!--            </el-form>-->
<!--            <template #footer>-->
<!--              <div class="dialog-footer">-->
<!--                    <el-button class="fcal_plain_btn" @click="showModal = false">-->
<!--                        Cancel-->
<!--                    </el-button>-->
<!--                    <el-button class="fcal_primary_btn" @click="confirmLocation()">-->
<!--                      Save-->
<!--                    </el-button>-->
<!--              </div>-->
<!--            </template>-->
<!--        </el-dialog>-->
    </div>
</template>

<script type="text/babel">
import { EditPen } from '@element-plus/icons-vue';

export default {
    name: 'LocationSelector',
    props: ['slot'],
    components: {
        EditPen
    },
    data() {
        return {
            locations: {
                conferencing: {
                    value: 'conferencing',
                    label: 'Conferencing',
                    options: {
                        google_meet: {
                            title: 'Google Meet',
                        },
                    }
                },
                in_person: {
                    value: 'in_person',
                    label: 'In Person',
                    options: {
                        in_person: {
                            title: 'In Person (Attendee Address)',
                        },
                        in_person_organizer: {
                            title: 'In Person (Organizer Address)',
                        },
                    }
                },
                phone: {
                    value: 'phone',
                    label: 'Phone',
                    options: {
                        phone: {
                            title: 'Attendee Phone Number',
                        },
                        phone_organizer: {
                            title: 'Organizer Phone Number',
                        }
                    }
                },
                other: {
                    value: 'other',
                    label: 'Other',
                    options: {
                        custom: {
                            title: 'Custom',
                        }
                    }
                }
            },
            showModal: false,
            showInput: false,
            location_details: {
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings || {},
            }
        }
    },
    computed: {
        hasHeading() {
            return this.location_details.location_type == 'in_person_organizer' || this.location_details.location_type == 'in_person' || this.location_details.location_type == 'custom';
        },
        hasDescription() {
            return this.location_details.location_type == 'in_person_organizer' || this.location_details.location_type == 'in_person' || this.location_details.location_type == 'custom';
        }
    },
    methods: {
        locationChanged() {
            this.location_details.location_settings = {
                description: '',
                call_type: 'outbound'
            }

            this.showModal = true;
            this.showInput = true;
        },
        confirmLocation() {

            if (this.location_details.location_type != 'phone_organizer' && this.location_details.location_type != 'phone' && this.location_details.location_type != 'google_meet') {
                if(!this.location_details.location_heading) {
                    this.$handleError('Please provide Location heading');
                    return;
                }
            }

            this.slot.location_type = this.location_details.location_type;
            this.slot.location_heading = this.location_details.location_heading;
            this.slot.location_settings = this.location_details.location_settings;
            this.showModal = false;
        }
    }
}
</script>
