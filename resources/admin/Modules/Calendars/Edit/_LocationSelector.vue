<template>
    <div class="fcal_location_selector_wrap">
        <el-select
            popper-class="fcal_selector_with_submenu fcal_location_select fcal_select"
            v-model="slot.location_settings[0].type"
            clearable
            placeholder="Select Location">
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

        <el-form
            v-if="isLocationInfoRequired"
            label-position="top"
            class="fcal_location_form"
        >
            <el-form-item label="Location Title *">
                <el-input v-model="slot.location_settings[0].title" type="text" placeholder="Location Title" />
            </el-form-item>
            <el-form-item label="Location Description">
                <el-input v-model="slot.location_settings[0].description" type="textarea" placeholder="Location Description" />
            </el-form-item>
        </el-form>

        <el-form
            v-else-if="isPhoneRequired"
            label-position="top"
            class="fcal_location_form"
        >
            <div>
                <el-form-item label="Your Phone Number * (with country code)">
                    <el-input v-model="slot.location_settings[0].host_phone_number" type="text" placeholder="Your Phone Number"/>
                </el-form-item>
            </div>
        </el-form>
        <div style="color: red;" v-if="isDisabledSelected">Looks like your remote connection for this location is disabled. Please revise your location selection</div>
    </div>
</template>

<script type="text/babel">
import isEmpty from 'lodash/isEmpty';
export default {
    name: 'LocationSelector',
    props: ['slot'],
    computed: {
        isPhoneRequired() {
            return this.slot.location_settings[0]?.type == 'phone_organizer';
        },
        isLocationInfoRequired() {
            return this.slot.location_settings[0]?.type == 'in_person_organizer' || this.slot.location_settings[0]?.type == 'custom';
        },
        isDisabledSelected() {
            const firstSelectedType = this.slot.location_settings[0]?.type;
            if(!firstSelectedType) {
                return false;
            }

            if(this.slot.settings.location_fields && !isEmpty(this.slot.settings.location_fields.conferencing.options)) {
                return this.slot.settings.location_fields.conferencing.options[firstSelectedType]?.disabled;
            }

            return false;
        }
    }
}
</script>
