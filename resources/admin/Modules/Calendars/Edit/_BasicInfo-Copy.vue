<template>
    <div class="fcal_form_section">
        <div style="padding: 20px 0;" class="fcal_section_body">
            <el-form :model="slot" label-position="top">
                <el-form-item label="Event Type">
                    <el-select :disabled="!!slot.id" popper-class="fcal_selector_with_submenu" v-model="slot.event_type" placeholder="Select Event Type">
                        <el-option
                            v-for="(type, typeKey) in eventTypes"
                            :key="typeKey"
                            :label="type.title"
                            :value="typeKey"
                        >
                            <b>{{ type.title }}</b>
                            <span>{{ type.subtitle }}</span>
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="Event name">
                    <el-input type="text" placeholder="Title of the event slot" v-model="slot.title"/>
                </el-form-item>
                <el-form-item label="Event Description">
                    <el-input :rows="4" type="textarea" placeholder="Event Description" v-model="slot.description"/>
                </el-form-item>
                <el-row :gutter="30">
                    <el-col :sm="24" :md="12">
                        <el-form-item label="Slot Duration">
                            <el-input type="number" :min="10" placeholder="duration in minutes"
                                      v-model="slot.duration">
                                <template #append>minutes</template>
                            </el-input>
                        </el-form-item>
                    </el-col>
                    <el-col :sm="24" :md="12">
                        <el-form-item label="Location">
                            <location-selector :slot="slot"/>
                        </el-form-item>
                    </el-col>
                </el-row>
                <template v-if="isOneToMany">
                    <el-form-item label="Max Guests in a Spot">
                        <el-col :md="4">
                            <el-input type="number" v-model="slot.max_book_per_slot"></el-input>
                        </el-col>
                    </el-form-item>
                    <el-checkbox 
                        v-model="isDisplaySpots"
                        @change="toggleDisplaySpots()"
                        type="checkbox"
                        label="Display Remaining Spots on Booking Page">
                    </el-checkbox>
                </template>
            </el-form>
        </div>
    </div>
</template>

<script type="text/babel">
import LocationSelector from "./_LocationSelector.vue";

export default {
    name: 'EventBasicInfo',
    props: ['slot'],
    components: {
        LocationSelector
    },
    data() {
        return {
            allHosts: [],
            eventTypes: this.appVars.event_types,
            isDisplaySpots: this.slot.is_display_spots == 1 ? true : false,
            isOneToMany: this.slot.id && this.slot.event_type == 'group'
        }
    },
    methods: {
        toggleDisplaySpots() {
            this.slot.is_display_spots = this.isDisplaySpots ? 1 : 0;
        }
    }
}
</script>
