<template>
        <el-dialog
            v-model="dialogVisible"
            width="30%"
            :title="locationDetails.type ? $t('Edit Location') : $t('Add Location')"
            class="fcal_modal fcal_location_modal"
        >
            <el-form
                label-position="top"
                class="fcal_location_form"
            >
                <el-form-item v-if="locationDetails.type == 'custom'" :label="$t('Location Title *')">
                    <el-input v-model="locationDetails.title" type="text" :placeholder="$t('Location Title')" />
                </el-form-item>
                <el-form-item v-if="locationDetails.type == 'in_person_organizer' || locationDetails.type == 'custom'" :label="$t('Location Description')">
                    <el-input v-model="locationDetails.description" type="textarea" :placeholder="$t('Location Description')" />
                </el-form-item>
                <el-form-item v-if="locationDetails.type == 'phone_organizer'" :label="$t('Your Phone Number * (with country code)')">
                    <el-input v-model="locationDetails.host_phone_number" type="text" :placeholder="$t('Your Phone Number')"/>
                </el-form-item>
                <el-form-item v-if="locationDetails.type == 'online_meeting'" :label="$t('Online Meeting Link *')">
                    <el-input v-model="locationDetails.meeting_link" type="text" :placeholder="$t('Your Meeting Link')"/>
                </el-form-item>
            </el-form>
            <template #footer>
                  <span class="dialog-footer">
                    <el-button class="fcal_plain_btn" @click="dialogVisible = false">
                        {{ $t('Cancel') }}
                    </el-button>
                    <el-button class="fcal_primary_btn" @click="updateDetails">
                        {{ $t('Update') }}
                    </el-button>
                  </span>
            </template>
        </el-dialog>
</template>

<script>
export default {
    name: 'LocationDetailsModal',
    props: ['locationDetails', 'showDialog'],
    data() {
        return {
            dialogVisible: this.showDialog,
        }
    },
    methods: {
        updateDetails() {
            this.$emit('updateDetails', this.dialogVisible, this.locationDetails);
        }
    }
}
</script>