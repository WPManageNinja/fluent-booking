<template>
    <div class="fcal_location_selector_wrap">
        <el-select
            popper-class="fcal_selector_with_submenu fcal_location_select fcal_select"
            v-model="slot.location_type"
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
            v-if="isLocationInfoRequired"
            label-position="top"
            class="fcal_location_form"
        >
            <el-form-item label="Location Title *">
                <el-input v-model="slot.location_heading" type="text" placeholder="Location Title" />
            </el-form-item>
            <el-form-item label="Location Description">
                <el-input v-model="slot.location_settings.description" type="textarea" placeholder="Location Description" />
            </el-form-item>
        </el-form>

        <el-form
            v-if="isPhoneRequired"
            label-position="top"
            class="fcal_location_form"
        >
            <div>
                <el-form-item label="Your Phone Number (with country code)">
                    <el-input v-model="slot.location_settings.host_phone_number" type="text" placeholder="Your Phone Number"/>
                </el-form-item>
            </div>
        </el-form>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'LocationSelector',
    props: ['slot'],
    data() {
        return {
            locations: this.appVars.location_schema,
        }
    },
    computed: {
        isPhoneRequired() {
            return this.slot.location_type == 'phone_organizer';
        },
        isLocationInfoRequired() {
            return this.slot.location_type == 'in_person_organizer' || this.slot.location_type == 'custom';
        }
    }
}
</script>
