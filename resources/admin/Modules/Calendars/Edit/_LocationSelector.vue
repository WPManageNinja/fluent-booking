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
                    @change="isLocationInfoRequired(slot.location_settings[i])"
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
                        v-if="slot.location_settings[i].type != 'in_person_guest' || slot.location_settings[i].type != 'phone_guest'"
                        class="fcal_plain_btn edit_location_btn"
                        @click="isLocationInfoRequired(slot.location_settings[i])"
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

                <el-form
                    v-if="false"
                    label-position="top"
                    class="fcal_location_form"
                >
                    <el-form-item label="Location Title *">
                        <el-input v-model="modalSettings.title" type="text" placeholder="Location Title" />
                    </el-form-item>
                    <el-form-item label="Location Description">
                        <el-input v-model="modalSettings.description" type="textarea" placeholder="Location Description" />
                    </el-form-item>
                </el-form>

                <div style="color: red;" v-if="isDisabledSelected(slot.location_settings[i].type)">Looks like your remote connection for this location is disabled. Please revise your location selection</div>

            </div>
        </div>

        <el-link @click="addNewLocation">Add a Location</el-link>


        <el-dialog
            v-if="dialogVisible"
            v-model="dialogVisible"
            width="30%"
            :title="modalSettings.type ? 'Edit Location' : 'Add Location'"
            class="fcal_modal fcal_location_modal"
        >
            <el-form
                label-position="top"
                class="fcal_location_form"
            >
                <el-form-item v-if="modalSettings.type == 'in_person_organizer' || modalSettings.type == 'custom'" label="Location Title *">
                    <el-input v-model="modalSettings.title" type="text" placeholder="Location Title" />
                </el-form-item>
                <el-form-item v-if="modalSettings.type == 'in_person_organizer' || modalSettings.type == 'custom'" label="Location Description">
                    <el-input v-model="modalSettings.description" type="textarea" placeholder="Location Description" />
                </el-form-item>

                <el-form-item v-if="modalSettings.type == 'phone_organizer'" label="Your Phone Number * (with country code)">
                    <el-input v-model="modalSettings.host_phone_number" type="text" placeholder="Your Phone Number"/>
                </el-form-item>
            </el-form>
            <template #footer>
                  <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">
                        Cancel
                    </el-button>
                    <el-button class="fcal_primary_btn" @click="handleDone">
                      Done
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
                type: '',
                title: '',
                host_phone_number: '',
                description: ''
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
        }
    },
    methods: {
        addNewLocation() {
            this.locationSettings = {
                type: '',
                title: '',
                host_phone_number: '',
                description: ''
            };
            this.slot.location_settings.push(this.locationSettings);
        },
        handleDone() {
            this.dialogVisible = false;
            this.modalSettings = {};
        },
        isLocationInfoRequired(location) {
            if (location.type == 'in_person_guest' || location.type == 'phone_guest') {
                return;
            }
            this.dialogVisible = true;
            if (this.dialogVisible) {
                this.modalSettings = location;
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
