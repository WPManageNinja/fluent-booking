<template>
    <div class="fcal_location_selector_wrap">

        <div class="fcal_locations_lists">
            <div class="fcal_location_list"
                 v-for="(location, i) in slot.location_settings"
                 :key="i"
            >
                <el-select
                    v-model="slot.location_settings[i].type"
                    popper-class="fcal_select"
                    @change="isLocationInfoRequired(slot.location_settings[i], i)"
                >
                    <el-option-group
                        v-for="(location, locationKey) in slot.settings.location_fields"
                        :key="locationKey"
                        :label="location.label"
                        :value="locationKey"
                    >
                        <el-option
                            v-for="(option, optionKey) in location.options"
                            :key="optionKey"
                            :label="option.title"
                            :value="optionKey"
                            :disabled="option.disabled"
                        >
                            {{ option.title }}
                        </el-option>
                    </el-option-group>
                </el-select>

                <div class="fcal_location_actions">

                    <el-button
                        v-if="isEditable(slot.location_settings[i].type)"
                        class="fcal_plain_btn edit_location_btn"
                        @click="isLocationInfoRequired(slot.location_settings[i], i)"
                    >
                        <el-icon><EditPen /></el-icon>
                    </el-button>
                    <el-button
                        v-if="slot.location_settings.length != 1"
                        class="fcal_plain_btn edit_location_btn"
                        @click="deleteLocation(i)"
                    >
                        <el-icon><Delete /></el-icon>
                    </el-button>

                </div>

                <div style="color: red;" v-if="isDisabledSelected(slot.location_settings[i].type)">Looks like your remote connection for this location is disabled. Please revise your location selection</div>

            </div>
        </div>

        <el-link v-if="slot.event_type == 'single'" :underline="false" @click="addNewLocation">Add a Location</el-link>

        <el-dialog
            v-if="dialogVisible"
            v-model="dialogVisible"
            :close-on-press-escape="false"
            :close-on-click-modal="false"
            width="30%"
            :title="modalSettings.type ? 'Edit Location' : 'Add Location'"
            class="fcal_modal fcal_location_modal"
        >
            <el-form
                label-position="top"
                class="fcal_location_form"
            >
                <el-form-item v-if="modalSettings.type == 'custom'" label="Location Title *">
                    <el-input v-model="modalSettings.custom_title" type="text" placeholder="Location Title" />
                </el-form-item>
                <el-form-item v-if="modalSettings.type == 'in_person_organizer' || modalSettings.type == 'custom'" label="Location Description">
                    <el-input v-model="modalSettings.description" type="textarea" placeholder="Location Description *" />
                </el-form-item>
                <el-form-item v-if="modalSettings.type == 'phone_organizer'" label="Your Phone Number * (with country code)">
                    <el-input v-model="modalSettings.host_phone_number" type="text" placeholder="Your Phone Number"/>
                </el-form-item>
                <el-form-item v-if="modalSettings.type == 'online_meeting'" label="Online Meeting Link *">
                    <el-input v-model="modalSettings.meeting_link" type="text" placeholder="Your Meeting Link"/>
                </el-form-item>
                <el-form-item>
                    <el-checkbox v-model="modalSettings.display_on_booking" true-label="yes" false-label="no" label="Display on booking page"/>
                </el-form-item>
            </el-form>
            <template #footer>
                  <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="cancelDetails(modalSettings.index)">
                        Cancel
                    </el-button>
                    <el-button class="fcal_primary_btn" @click="updateDetails">
                        Update
                    </el-button>
                  </span>
            </template>

        </el-dialog>
    </div>
</template>

<script type="text/babel">
import isEmpty from 'lodash/isEmpty';
import { EditPen, Delete } from '@element-plus/icons-vue';


export default {
    name: 'LocationSelector',
    props: ['slot'],
    components: {
        EditPen,
        Delete
    },
    data() {
        return {
            locationSettings: {},
            dialogVisible: false,
            modalSettings: {
                index: '',
                type: '',
                title: '',
                host_phone_number: '',
                description: '',
                display_on_booking: 'no'
            }
        }
    },
    computed: {
        isDisabledSelected() {
            return (firstSelectedType) => {
                if(!firstSelectedType) {
                    return false;
                }

                if(this.slot.settings.location_fields && !isEmpty(this.slot.settings.location_fields.conferencing.options)) {
                    return this.slot.settings.location_fields.conferencing.options[firstSelectedType]?.disabled;
                }

                return false;
            }
        },
        isEditable() {
            return (locationType) => {
                if (locationType == 'in_person_guest' || locationType == 'phone_guest' || locationType == 'google_meet') {
                    return false;
                }
                return true;
            }
        }
    },
    methods: {
        addNewLocation() {
            this.locationSettings = {
                type: '',
                title: '',
                custom_title: '',
                host_phone_number: '',
                description: '',
                display_on_booking: 'no',
            };
            this.slot.location_settings.push(this.locationSettings);
        },
        updateDetails() {
            const location = this.modalSettings;
            if ((location.type == 'custom') && !location.custom_title)  {
                this.$handleError('Location Title is required');
                return false;
            } else if ((location.type == 'in_person_organizer' || location.type == 'custom') && !location.description)  {
                this.$handleError('Location Description is required');
                return false;
            } else if (location.type == 'phone_organizer' && !location.host_phone_number) {
                this.$handleError('Phone Number is required');
                return false;
            } else if (location.type == 'online_meeting' && !location.meeting_link) {
                this.$handleError('Meeting Link is required');
                return false;
            }
            this.dialogVisible = false;
            this.modalSettings = {};
        },
        cancelDetails(index) {
            const location = this.modalSettings;
            if ((location.type == 'custom') && (!location.title || !location.description)) {
                this.deleteLocation(index);
            } else if ((location.type == 'in_person_organizer' || location.type == 'custom') && !location.description)  {
                this.deleteLocation(index);
            } else if (location.type == 'phone_organizer' && !location.host_phone_number) {
                this.deleteLocation(index);
            } else if (location.type == 'online_meeting' && !location.meeting_link) {
                this.deleteLocation(index);
            }
            this.dialogVisible = false;
        },
        isLocationInfoRequired(location, index) {
            if (location.type == 'in_person_guest') {
                this.slot.location_settings[index].title = 'In Person (Attendee Address)';
                return;
            } else if (location.type == 'in_person_organizer') {
                this.slot.location_settings[index].title = 'In Person (Organizer Address)';
            } else if (location.type == 'phone_guest') {
                this.slot.location_settings[index].title = 'Attendee Phone Number';
                return;
            } else if (location.type == 'phone_organizer') {
                this.slot.location_settings[index].title = 'Organizer Phone Number';
            } else if (location.type == 'online_meeting') {
                this.slot.location_settings[index].title = 'Online Meeting';
            }
            
            this.dialogVisible = true;
            if (this.dialogVisible) {
                this.modalSettings = location;
                this.modalSettings.index = index;
            } else {
                this.modalSettings = {}
            }
        },
        deleteLocation(deleteIndex) {
            this.slot.location_settings.splice(deleteIndex, 1);
        }
    }
}
</script>
