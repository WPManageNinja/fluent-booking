<template>
    <div class="fcal_form_section">
        <div class="fcal_section_body">
            <el-form :model="slot" label-position="top">
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
    }
}
</script>
