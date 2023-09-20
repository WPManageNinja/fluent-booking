<template>
    <div class="fcal_settings_body_inner fcal_settings_general">
        <div class="fcal_settings_header">
            <h3>General</h3>
        </div>

        <div class="fcal_settings_content_wrap">
            <el-form v-model="formData">
                <el-form-item>
                    <div class="fcal_settings_card">
                        <div class="content">
                            <h3>Date & Time Format</h3>
                            <p>Change the date and time format to your likings</p>
                            <span class="format-date">
                                Default format:
                                <span>{{ formData.dateFormat }}</span>
                            </span>
                        </div>
                        <el-button class="fcal_primary_btn2">
                            Update Date & Time Format
                        </el-button>
                    </div>
                </el-form-item>


                <el-form-item>
                    <div class="fcal_settings_card">
                        <div class="content">
                            <h3>Calendar translator</h3>
                            <p>Select the language for your user</p>
                        </div>

                        <el-select
                            v-model="formData.translateLanguage"
                            placeholder="Select"
                            popper-class="fcal_select"
                        >
                            <el-option
                                label="English"
                                value="english"
                            />
                            <el-option
                                label="Bangla"
                                value="bangla"
                            />
                        </el-select>
                    </div>
                </el-form-item>

                <el-form-item>
                    <div class="fcal_settings_card">
                        <div class="content">
                            <h3>Set Timezone</h3>
                            <p>Select the timezone of timetics</p>
                            <p>Current Time: 4:39pm</p>
                        </div>

                        <el-select
                            v-model="formData.timezone"
                            placeholder="Select"
                            popper-class="fcal_select"
                        >
                            <el-option
                                label="Asia/Dhaka"
                                value="asia/dhaka"
                            />
                            <el-option
                                label="United State"
                                value="us"
                            />
                        </el-select>
                    </div>
                </el-form-item>

            </el-form>

            <div class="fcal_settings_footer">
                <el-button class="fcal_primary_btn">
                    Save Changes
                </el-button>
            </div>
        </div>

    </div>
</template>

<script>
export default {
    name: "GeneralSettings",
    data() {
        return {
            formData: {
                dateFormat: 'F j, Yg:i a',
                translateLanguage: 'english',
                timezone: 'asia/dhaka'
            }
        }
    }
}
</script>

<style scoped>

</style>