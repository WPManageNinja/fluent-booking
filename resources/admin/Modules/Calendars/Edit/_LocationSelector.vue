<template>
    <div>
        <el-select @change="locationChanged()" :disabled="!!location_details.location_type" popper-class="fcal_selector_with_submenu" v-model="location_details.location_type" placeholder="Select Location">
            <el-option
                v-for="(location, locationKey) in locations"
                :key="locationKey"
                :label="location.title"
                :value="locationKey"
            >
                <b>{{ location.title }}</b>
                <span>{{ location.subtitle }}</span>
            </el-option>
            <template #prefix>
                <el-button class="location_edit_btn" v-if="location_details.location_type" @click="showModal = true;">Edit</el-button>
            </template>
        </el-select>
        <el-dialog
            v-model="showModal"
            title="Edit Location"
            :append-to-body="true"
            max-width="300px">
            <el-form v-if="showModal" :model="location_details" label-position="top" >
                <el-form-item label="Location">
                    <el-select @change="locationChanged()" popper-class="fcal_selector_with_submenu" v-model="location_details.location_type" placeholder="Select Location">
                        <el-option
                            v-for="(location, locationKey) in locations"
                            :key="locationKey"
                            :label="location.title"
                            :value="locationKey"
                        >
                            <b>{{ location.title }}</b>
                            <span style="display: block;color: var(--el-text-color-secondary);padding:0; margin:0; font-size: 12px;">
                                {{ location.subtitle }}
                            </span>
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item v-if="hasHeading" label="Location Title">
                    <el-input v-model="location_details.location_heading" type="text" placeholder="Location Title" />
                </el-form-item>
                <el-form-item v-if="hasDescription" label="Location Description">
                    <el-input v-model="location_details.location_settings.description" type="textarea" placeholder="Location Description" />
                </el-form-item>

                <div v-if="location_details.location_type == 'phone'">
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
            <template #footer>
              <span class="dialog-footer">
                <el-button type="primary" @click="confirmLocation()">
                  Confirm
                </el-button>
              </span>
            </template>
        </el-dialog>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'LocationSelector',
    props: ['slot'],
    data() {
        return {
            locations: {
                in_person: {
                    title: 'In Person Meeting',
                    subtitle: 'Set an address or place'
                },
                google_meet: {
                    title: 'Google Meet',
                    subtitle: 'Google Meet link will be shared'
                },
                phone: {
                    title: 'Phone Call',
                    subtitle: 'In bound or outbound calls'
                },
                custom: {
                    title: 'Custom',
                    subtitle: 'Leave a customized location details'
                }
            },
            showModal: false,
            location_details: {
                location_type: this.slot.location_type,
                location_heading: this.slot.location_heading,
                location_settings: this.slot.location_settings || {},
            }
        }
    },
    computed: {
        hasHeading() {
            return this.location_details.location_type == 'in_person' || this.location_details.location_type == 'custom';
        },
        hasDescription() {
            return this.location_details.location_type == 'in_person' || this.location_details.location_type == 'custom';
        }
    },
    methods: {
        locationChanged() {
            this.location_details.location_settings = {
                description: '',
                call_type: 'outbound'
            }

            this.showModal = true;
        },
        confirmLocation() {

            if (this.location_details.location_type != 'phone') {
                if(!this.location_details.location_heading) {
                    this.$notify.error('Please provide Location heading');
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
